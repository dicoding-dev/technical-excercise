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
            // Start the transaction
            DB::beginTransaction();  // Optimization 3: Transactional Operations

            $deadline = Carbon::parse(Enrollment::latest('id')->value('deadline_at'));

            $this->stopwatch->start(__CLASS__);

            $this->dropOutEnrollmentsBefore($deadline);

            $this->stopwatch->stop(__CLASS__);
            $this->info($this->stopwatch->getEvent(__CLASS__));

            // Commit the transaction
            DB::commit();  // Optimization 3: Transactional Operations
        } catch (\Exception $e) {
            DB::rollBack();  // Rollback the transaction in case of an error
            throw $e;
        }
    }

    /**
     * The dropout process should fulfill the following requirements:
     * 1. The enrollment deadline has passed.
     * 2. The student has no active exam.
     * 3. The student has no submission waiting for review.
     * 4. Update the enrollment status to `DROPOUT`.
     * 5. Create an activity log for the student.
     */
    private function dropOutEnrollmentsBefore(Carbon $deadline)
    {
        Enrollment::where('deadline_at', '<=', $deadline)
            ->select('id', 'course_id', 'student_id')  // Optimization 4: Selective Data Loading
            ->chunkById(1000, function ($enrollments) {
                $droppedOutEnrollments = 0;
                $excludedFromDropOut = 0;

                foreach ($enrollments as $enrollment) {
                    // Optimization 2: Efficient Queries
                    $hasActiveExam = Exam::where([
                        ['course_id', $enrollment->course_id],
                        ['student_id', $enrollment->student_id],
                        ['status', 'IN_PROGRESS']
                    ])->exists();

                    $hasWaitingReviewSubmission = Submission::where([
                        ['course_id', $enrollment->course_id],
                        ['student_id', $enrollment->student_id],
                        ['status', 'WAITING_REVIEW']
                    ])->exists();

                    if ($hasActiveExam || $hasWaitingReviewSubmission) {
                        $excludedFromDropOut++;  // Track excluded enrollments
                        continue;
                    }

                    $enrollment->update([
                        'status' => 'DROPOUT',
                        'updated_at' => now(),
                    ]);

                    Activity::create([
                        'resource_id' => $enrollment->id,
                        'user_id' => $enrollment->student_id,
                        'description' => 'COURSE_DROPOUT',
                    ]);

                    $droppedOutEnrollments++;
                }

                $this->info('Excluded from drop out: ' . $excludedFromDropOut);
                $this->info('Final dropped out enrollments: ' . $droppedOutEnrollments);
            });
    }
}
