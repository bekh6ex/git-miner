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


}
