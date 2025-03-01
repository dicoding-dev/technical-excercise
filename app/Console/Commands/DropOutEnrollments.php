<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Stopwatch\Stopwatch;

class DropOutEnrollments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollments:dropout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dropout enrollments on specified date.';

    public function __construct(
        private readonly Stopwatch $stopwatch,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            // Retrieve the latest deadline (adjust as needed)
            $deadline = Carbon::parse(Enrollment::latest('id')->value('deadline_at'));

            $this->stopwatch->start(__CLASS__);

            // Call the optimized dropout process.
            $this->dropOutEnrollmentsBefore($deadline);

            $event = $this->stopwatch->stop(__CLASS__);

            $this->info($this->stopwatch->getEvent(__CLASS__));

            // Note: Rollback is used here for testing/dry-run purposes.
            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     *Optimized dropout process.
     *
     * Optimization Documentation:
     *
     * - **Step 1: Select Only Required Columns**
     *   - *What:* Instead of retrieving full model data, we select only 'id', 'course_id', and 'student_id'.
     *   - *Why:* Reduces memory usage.
     *   - *How:* Use ->select('id', 'course_id', 'student_id').
     *
     * - **Step 2: Bulk Fetch Related Records with Composite Keys**
     *   - *What:* Use selectRaw to retrieve composite keys (course_id-student_id) for Exams and Submissions.
     *   - *Why:* Minimizes the data loaded from the database.
     *   - *How:* Use ->selectRaw("CONCAT(course_id, '-', student_id) as composite_key")->distinct()->pluck('composite_key').
     *
     * - **Step 3: Cache Timestamp per Chunk**
     *   - *What:* Call now() only once per chunk.
     *   - *Why:* Reduces function call overhead.
     *   - *How:* Store the result in a $now variable.
     *
     * - **Step 4: Use Chunking, and Bulk Update & Insert**
     *   - *What:* Process enrollments in chunks, then update and insert in bulk.
     *   - *Why:* Prevents high memory usage and reduces database roundtrips.
     *   - *How:* Use chunkById(1000) with DB::table()->whereIn()->update() and DB::table()->insert().
     *
     * @param Carbon $deadline
     */
    private function dropOutEnrollmentsBefore(Carbon $deadline)
    {
        $this->info('Starting dropout process...');
        $totalDropped = 0;
        $totalChecked = 0;

        // Process enrollments in chunks (only select needed columns)
        Enrollment::select('id', 'course_id', 'student_id')
            ->where('deadline_at', '<=', $deadline)
            ->chunkById(1000, function ($enrollments) use (&$totalDropped, &$totalChecked) {
                // Extract unique course_ids and student_ids from the current chunk.
                $courseIds = $enrollments->pluck('course_id')->unique()->toArray();
                $studentIds = $enrollments->pluck('student_id')->unique()->toArray();

                // Build lookup for active exams using composite keys.
                $activeExamKeys = Exam::selectRaw("CONCAT(course_id, '-', student_id) as composite_key")
                    ->whereIn('course_id', $courseIds)
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'IN_PROGRESS')
                    ->distinct()
                    ->pluck('composite_key')
                    ->toArray();
                $activeExamLookup = array_flip($activeExamKeys);

                // Build lookup for waiting submissions using composite keys.
                $waitingSubmissionKeys = Submission::selectRaw("CONCAT(course_id, '-', student_id) as composite_key")
                    ->whereIn('course_id', $courseIds)
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'WAITING_REVIEW')
                    ->distinct()
                    ->pluck('composite_key')
                    ->toArray();
                $waitingSubmissionLookup = array_flip($waitingSubmissionKeys);

                // Prepare arrays for bulk update and bulk insert.
                $enrollmentIdsToDrop = [];
                $activityLogs = [];
                $now = now(); // Cache current timestamp for the entire chunk

                foreach ($enrollments as $enrollment) {
                    $totalChecked++;
                    $key = $enrollment->course_id . '-' . $enrollment->student_id;

                    // Skip enrollment if it has an active exam or waiting submission.
                    if (isset($activeExamLookup[$key]) || isset($waitingSubmissionLookup[$key])) {
                        continue;
                    }

                    $enrollmentIdsToDrop[] = $enrollment->id;
                    $activityLogs[] = [
                        'resource_id' => $enrollment->id,
                        'user_id'     => $enrollment->student_id,
                        'description' => 'COURSE_DROPOUT',
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                    $totalDropped++;
                }

                // Bulk update enrollments that qualify for dropout.
                if (!empty($enrollmentIdsToDrop)) {
                    DB::table('enrollments')
                        ->whereIn('id', $enrollmentIdsToDrop)
                        ->update([
                            'status'     => 'DROPOUT',
                            'updated_at' => $now,
                        ]);
                }

                // Bulk insert all the activity log records.
                if (!empty($activityLogs)) {
                    DB::table('activities')->insert($activityLogs);
                }
            });

        // Output process statistics.
        $this->info("Enrollments to be dropped out: $totalChecked");
        $this->info("Excluded from drop out: " . ($totalChecked - $totalDropped));
        $this->info("Final dropped out enrollments: $totalDropped");
    }
}
