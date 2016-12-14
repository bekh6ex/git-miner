<?php
use Bekh6ex\GitMiner\Commit;
use Bekh6ex\GitMiner\File;

require_once __DIR__ . '/vendor/autoload.php';

$parser = new \Bekh6ex\GitMiner\Parser();


$file = fopen(__DIR__ . '/git-log.txt', 'r');

$commits = $parser->parse($file);

usort($commits, function (Commit $a, Commit $b) {
    return $a->date() > $b->date();
});

$files = [];

foreach ($commits as $commit) {
    foreach ($commit->fileChanges() as $fileChange) {
        switch ($fileChange->action()) {
            case \Bekh6ex\GitMiner\CommitFileChange::ACTION_ADD:
                $files[$fileChange->name()] = new File($fileChange->name(), $commit->date());
                break;
            case \Bekh6ex\GitMiner\CommitFileChange::ACTION_DELETE:
                unset($files[$fileChange->name()]);
                break;
            case \Bekh6ex\GitMiner\CommitFileChange::ACTION_MODIFY:
                $files[$fileChange->name()]->addChange($commit->date());
                break;
            default:
                throw new \Exception("Unknown action: `{$fileChange->action()}`");
        }

    }
}

var_dump($files);

