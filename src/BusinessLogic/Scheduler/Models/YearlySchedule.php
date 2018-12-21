<?php

namespace Packlink\BusinessLogic\Scheduler\Models;

/**
 * Class YearlySchedule
 * @package Logeecom\Infrastructure\Scheduler\Models
 */
class YearlySchedule extends Schedule
{

    /**
     * Calculates next schedule time
     *
     * @return \DateTime Next schedule date
     * @throws \Exception Emits Exception in case of an error while creating DateTime instance
     */
    public function calculateNextSchedule()
    {
        $now = $this->now();
        $shouldExecuteOn = $this->now();

        $year = (int)date('Y', $now->getTimestamp());

        $shouldExecuteOn->setDate($year, $this->getMonth(), $this->getDay());
        $shouldExecuteOn->setTime($this->getHour(), $this->getMinute(), 0);

        if ($now->getTimestamp() > $shouldExecuteOn->getTimestamp()) {
            // add one year
            $shouldExecuteOn->add(new \DateInterval('P1Y'));
        }

        return $shouldExecuteOn;
    }
}
