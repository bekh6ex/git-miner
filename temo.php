<?php
use Bekh6ex\GitMiner\Commit;
use Bekh6ex\GitMiner\File;

require_once __DIR__ . '/vendor/autoload.php';

$parser = new \Bekh6ex\GitMiner\Parser();


$file = fopen(__DIR__ . '/4seo-git-log.txt', 'r');

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

$borderCreationDate = (new DateTimeImmutable())->modify('-1 month');
$files = array_filter($files, function (File $file) use ($borderCreationDate) {
    return $file->wasCreatedBefore($borderCreationDate);
});
$files = array_filter($files, function (File $file) use ($borderCreationDate) {
    return substr($file->name(), -4) === '.php';
});
$files = array_filter($files, function (File $file) use ($borderCreationDate) {
    return strpos($file->name(), 'tests') === false;
});

usort($files, function (File $a, File $b) {
    return $a->modificationScore() < $b->modificationScore();
});


/** @var File[] $top20 */
$top20 = array_slice($files, 0, 20);

foreach ($top20 as $item) {
    echo $item->modificationScore() . ' : ' . $item->name() . "\n";
}

