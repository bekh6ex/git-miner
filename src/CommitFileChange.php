<?php

namespace Bekh6ex\GitMiner;

class CommitFileChange
{
    const ACTION_ADD = 'A';
    const ACTION_MODIFY = 'M';
    const ACTION_DELETE = 'D';

    /** @var string */
    private $name;

    /** @var string */
    private $action;

    /**
     * @param string $name
     * @param string $action
     */
    public function __construct($name, $action)
    {
        $this->name = $name;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
