<?php

namespace Bekh6ex\GitMiner;

use DateTimeImmutable;

class File
{
    private $name;
    /** @var \DateTimeImmutable */
    private $created;
    /** @var \DateTimeImmutable[] */
    private $changes = [];

    public function __construct($name, DateTimeImmutable $created)
    {
        $this->name = $name;
        $this->created = $created;

        $this->addChange($created);
    }

    public function name()
    {
        return $this->name;
    }
    /**
     * @return DateTimeImmutable
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated(DateTimeImmutable $created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTimeImmutable[]
     */
    public function changes()
    {
        return $this->changes;
    }

    /**
     * @param \DateTimeImmutable[] $changes
     */
    public function addChange(DateTimeImmutable $changeDate)
    {
        $this->changes[] = $changeDate;
    }

    /**
     * @param DateTimeImmutable $date
     * @return bool
     */
    public function wasCreatedBefore(DateTimeImmutable $date)
    {
        return $this->created < $date;
    }

    /**
     * @return float
     */
    public function modificationScore()
    {
        $borderDate = $this->created->modify('+6 months');

        $totalChangeCount = count($this->changes);
        $initialPeriodChangeCount = $this->countChangesBeforeDate($borderDate);

        return ($totalChangeCount - $initialPeriodChangeCount) / $initialPeriodChangeCount;
    }

    private function countChangesBeforeDate(DateTimeImmutable $date)
    {
        $result = 0;
        foreach ($this->changes as $change) {
            if ($change < $date) {
                $result++;
            }
        }

        return $result;
    }


}
