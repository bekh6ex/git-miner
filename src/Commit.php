<?php

namespace Bekh6ex\GitMiner;

class Commit
{
    /** @var string */
    private $hash;

    /** @var \DateTimeImmutable */
    private $date;

    /** @var CommitFileChange[] */
    private $fileChanges;

    /**
     * @param string $hash
     * @param \DateTimeImmutable $date
     * @param array $fileChanges
     */
    public function __construct($hash, \DateTimeImmutable $date, array $fileChanges)
    {
        $this->hash = $hash;
        $this->date = $date;
        $this->fileChanges = $fileChanges;
    }

    public function hash()
    {
        return $this->hash;
    }

    public function date()
    {
        return $this->date;
    }

    public function fileChanges()
    {
        return $this->fileChanges;
    }
}
