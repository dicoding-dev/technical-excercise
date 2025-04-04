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

            $deadline = Carbon::parse(Enrollment::latest('id')->value('deadline_at'));

            $this->stopwatch->start(__CLASS__);

            $this->dropOutEnrollmentsBefore($deadline);

            $this->stopwatch->stop(__CLASS__);
            $this->info($this->stopwatch->getEvent(__CLASS__));

            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * The dropout process should fulfil the following requirements:
     * 1. The enrollment deadline has passed.
     * 2. The student has no active exam.
     * 3. The student has no submission waiting for review.
     * 4. Update the enrollment status to `DROPOUT`.
     * 5. Create an activity log for the student.
     */
    private function dropOutEnrollmentsBefore(Carbon $deadline)
    {
        $initialEnrollmentsToBeDroppedOut = Enrollment::where('enrollments.deadline_at', '<=', $deadline);
        $initialEnrollmentsToBeDroppedOutCount = $initialEnrollmentsToBeDroppedOut->count();

        $this->info('Enrollments to be dropped out: ' . $initialEnrollmentsToBeDroppedOutCount);

        $batchSize = 500;
        $enrollmentsToBeDroppedOutCount = 0;
        $now = now();
        $initialEnrollmentsToBeDroppedOut
            ->leftJoin('exams', function ($join) {
                $join->on('enrollments.course_id', '=', 'exams.course_id')
                    ->on('enrollments.student_id', '=', 'exams.student_id')
                    ->where('exams.status', 'IN_PROGRESS');
            })
            ->leftJoin('submissions', function ($join) {
                $join->on('enrollments.course_id', '=', 'submissions.course_id')
                    ->on('enrollments.student_id', '=', 'submissions.student_id')
                    ->where('submissions.status', 'WAITING_REVIEW');
            })
            ->whereNull('exams.id')
            ->whereNull('submissions.id')
            ->selectRaw('DISTINCT enrollments.id, enrollments.student_id')
            ->orderBy('enrollments.id')
            ->chunkById($batchSize, function ($enrollments) use (&$enrollmentsToBeDroppedOutCount, $now) {
                $ids = [];
                $activities = [];

                foreach ($enrollments as $enrollment) {
                    $ids[] = $enrollment->id;
                    $activities[] = [
                        'resource_id' => $enrollment->id,
                        'user_id' => $enrollment->student_id,
                        'description' => 'COURSE_DROPOUT',
                    ];
                }

                Enrollment::whereIn('id', $ids)->update([
                    'status' => 'DROPOUT',
                    'updated_at' => $now,
                ]);

                Activity::insert($activities);
                $enrollmentsToBeDroppedOutCount += count($activities);

                unset($ids, $activities);
                gc_collect_cycles();
            }, 'enrollments.id', 'id');

        $this->info('Excluded from drop out: ' . $initialEnrollmentsToBeDroppedOutCount - $enrollmentsToBeDroppedOutCount);
        $this->info('Final dropped out enrollments: ' . $enrollmentsToBeDroppedOutCount);
    }

}
