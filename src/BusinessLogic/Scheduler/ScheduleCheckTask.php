<?php

namespace Packlink\BusinessLogic\Scheduler;

use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\ServiceRegister;
use Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Logeecom\Infrastructure\TaskExecution\QueueService;
use Logeecom\Infrastructure\TaskExecution\Task;
use Logeecom\Infrastructure\Utility\TimeProvider;
use Packlink\BusinessLogic\Scheduler\Models\Schedule;

/**
 * Class ScheduleCheckTask.
 *
 * @package Logeecom\Infrastructure\Scheduler
 */
class ScheduleCheckTask extends Task
{
    /**
     * @var \Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface
     */
    private $repository;

    /**
     * Runs task logic.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function execute()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        /** @var Schedule $scheduledTask */
        foreach ($this->getScheduledTasks() as $scheduledTask) {
            $task = $scheduledTask->getTask();
            try {
                $queueService->enqueue($scheduledTask->getQueueName(), $task);

                $scheduledTask->setNextSchedule($scheduledTask->calculateNextSchedule());
                $this->getRepository()->update($scheduledTask);
            } catch (QueueStorageUnavailableException $ex) {
                Logger::logDebug(
                    'Failed to enqueue task ' . $task->getType(),
                    'Core',
                    array(
                        'ExceptionMessage' => $ex->getMessage(),
                        'ExceptionTrace' => $ex->getTraceAsString(),
                        'TaskData' => serialize($task),
                    )
                );
            }
        }

        $this->reportProgress(100);
    }

    /**
     * Returns current date and time
     *
     * @return \DateTime
     */
    protected function now()
    {
        /** @var TimeProvider $timeProvider */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);

        return $timeProvider->getCurrentLocalTime();
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Returns an array of Scheduled tasks that are due for execution
     *
     * @return \Logeecom\Infrastructure\ORM\Entity[]
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getScheduledTasks()
    {
        $queryFilter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $queryFilter->where('nextSchedule', '<=', $this->now());

        return $this->getRepository()->select($queryFilter);
    }

    /**
     * Returns repository instance
     *
     * @return \Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            /** @var \Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface $repository */
            $this->repository = RepositoryRegistry::getRepository(Schedule::CLASS_NAME);
        }

        return $this->repository;
    }
}
