<?php

namespace Bekh6ex\GitMiner;

class Parser
{

    /**
     * @param resource $input
     * @return Commit[]
     */
    public function parse($input)
    {
        $commits = [];
        $currentCommitHash = null;
        $fileChanges = [];
        $commitDate = null;

        foreach($this->byLineIterator($input) as $line) {
            switch (true) {
                case $this->startsWith($line, 'commit '):
                    if ($currentCommitHash !== null) {
                        $commits[] = new Commit($currentCommitHash, $commitDate, $fileChanges);
                        $currentCommitHash = null;
                        $commitDate = null;
                        $fileChanges = [];
                    }
                    $currentCommitHash = substr($line, strlen('commit '));
                    break;
                //:100644 100644 259255b... 335a267... M	build/datadog/conf.d/docker_daemon.yaml
                case preg_match('/^:\d+ \d+ [\da-f]+... [\da-f]+... (\w)\s+(.*)$/ui', $line, $matches):
                    $fileChanges[] = new CommitFileChange($matches[2], $matches[1]);
                    break;

                case $this->startsWith($line, 'Date:'):
                    $commitDateAsString = trim(substr($line, strlen('Date:')));
                    $commitDate = new \DateTimeImmutable($commitDateAsString);
                    break;
            }
        }

        if ($currentCommitHash !== null) {
            $commits[] = new Commit($currentCommitHash, $commitDate, $fileChanges);
        }

        return $commits;
    }

    /**
     * @param $line
     * @param $string
     * @return bool
     */
    private function startsWith($line, $string)
    {
        return substr($line, 0, strlen($string)) === $string;
    }

    /**
     * @param $input
     * @return \Iterator
     */
    private function byLineIterator($input)
    {
        while(true) {
            $line = fgets($input);
            if ($line === false) {
                return;
            }

            yield trim($line, "\n");
        }
    }
}
