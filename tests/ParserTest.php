<?php


namespace Bekh6ex\GitMiner\Test;


use Bekh6ex\GitMiner\Commit;
use Bekh6ex\GitMiner\CommitFileChange;
use Bekh6ex\GitMiner\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canParseSimpleCase() {
        $testInput = <<<GIT_LOG
commit a4eb90c30633d63c85a3db7fb1bbc66a434930e1
Author: Aleksey Bekh-Ivanov <6ex@mail.ru>
Date:   Fri Nov 4 08:57:34 2016 +0100

    Removed NoNumber Framework

:100644 000000 316fdd6... 0000000... D  web/media/nnframework/css/NNIcoMoon.dev.svg
GIT_LOG;

        $commits = (new Parser())->parse($this->asResource($testInput));

        assertThat($commits, is(arrayWithSize(1)));
        /** @var Commit $commit */
        $commit = $commits[0];

        assertThat($commit->hash(), is(equalTo('a4eb90c30633d63c85a3db7fb1bbc66a434930e1')));
        assertThat($commit->date(),
            is(equalTo(new \DateTimeImmutable('2016-11-04 08:57:34', new \DateTimeZone('GMT+01')))));

        assertThat($commit->fileChanges(),
            is(equalTo([new CommitFileChange('web/media/nnframework/css/NNIcoMoon.dev.svg', CommitFileChange::ACTION_DELETE)])));
    }

    /**
     * @param $testInput
     * @return resource
     */
    private function asResource($testInput)
    {
        $res = fopen('php://temp', 'rw+');
        fwrite($res, $testInput);
        rewind($res);

        return $res;
    }

}
