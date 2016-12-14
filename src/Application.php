<?php

namespace Bekh6ex\GitMiner;


use DateTimeImmutable;
use ezcGraphArrayDataSet;
use ezcGraphLineChart;

class Application
{
    private $reportDir;

    /**
     * @param string $reportDir
     */
    public function __construct($reportDir)
    {
        $this->reportDir = $reportDir;
    }


    public function main($file)
    {
        $parser = new \Bekh6ex\GitMiner\Parser();

        $commits = $parser->parse($file);

        usort($commits, function (Commit $a, Commit $b) {
            return $a->date()->getTimestamp() >= $b->date()->getTimestamp();
        });

        $files = $this->fileProjectionFromCommits($commits);
        $files = $this->filterNoninterestingFiles($files);


        /** @var File[] $top20 */
        $top20 = array_slice($files, 0, 20);
        $htmlBody = '';
        if (!file_exists($this->reportDir)) {
            mkdir($this->reportDir, 0777, true);
        }
        foreach ($top20 as $item) {
            $graphFileName = $this->makeGraphForItem($item);

            $itemChangesCount = count($item->changes());

            $modificationScore = (int)$item->modificationScore();
            $htmlBody .= <<<HTML
<div>
<h2>{$item->name()} ($itemChangesCount changes total; rating: {$modificationScore})</h2>
<img src="{$graphFileName}.svg">
</div>
HTML;
        }

        $pageHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report</title>
</head>
<body>
{$htmlBody}
</body>
</html>
HTML;


        file_put_contents($this->reportDir . '/report.html', $pageHtml);
    }

    /**
     * @param DateTimeImmutable[] $dates
     * @return int[]
     */
    private function countByMonth(array $dates)
    {
        $result = [];
        /** @var DateTimeImmutable $startDate */
        $startDate = call_user_func_array('min', $dates);
        $startDate = $startDate->setDate($startDate->format('Y'), $startDate->format('m'), 1)->setTime(0, 0, 0);
        $endDate = call_user_func_array('max', $dates);
        $endDate = $endDate->setDate($endDate->format('Y'), $endDate->format('m'), 1)->setTime(0, 0, 0);

        for ($date = $startDate; $date <= $endDate; $date = $date->modify('+1 month')) {
            $dateKey = $date->format('m Y');
            $result[$dateKey] = 0;
        }

        foreach ($dates as $date) {
            $dateKey = $date->format('m Y');

            $result[$dateKey]++;
        }

        return $result;
    }


    private function percentile($data, $percentile)
    {

        $p = $percentile * .01;

        $count = count($data);
        $allindex = ($count - 1) * $p;
        $intvalindex = intval($allindex);
        $floatval = $allindex - $intvalindex;
        sort($data);
        if (!is_float($floatval)) {
            $result = $data[$intvalindex];
        } else {
            if ($count > $intvalindex + 1)
                $result = $floatval * ($data[$intvalindex + 1] - $data[$intvalindex]) + $data[$intvalindex];
            else
                $result = $data[$intvalindex];
        }
        return $result;
    }

    /**
     * @param $item
     * @return mixed
     */
    private function makeGraphForItem($item)
    {
        $graph = new ezcGraphLineChart();
        $graph->title = $item->name();

        $graph->data['changes count'] = new ezcGraphArrayDataSet($this->countByMonth($item->changes()));

        $safeFileName = preg_replace('/[^\w\d]/ui', '_', $item->name());

        $graph->render(1000, 300, $this->reportDir . '/' . $safeFileName . '.svg');
        return $safeFileName;
    }

    /**
     * @param Commit[] $commits
     * @return File[]
     * @throws \Exception
     */
    private function fileProjectionFromCommits($commits)
    {
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
                        if (!isset($files[$fileChange->name()])) {
                            //TODO: Can happen that file is deleted and then modified. Need to figure out why.
                            break;
                        }
                        $files[$fileChange->name()]->addChange($commit->date());
                        break;
                    default:
                        throw new \Exception("Unknown action: `{$fileChange->action()}`");
                }

            }
        }

        return $files;
    }

    /**
     * @param $files
     * @return array
     */
    private function filterNoninterestingFiles($files)
    {
        $borderCreationDate = (new DateTimeImmutable())->modify('-1 month');
        $files = array_filter($files, function (File $file) use ($borderCreationDate) {
            return $file->wasCreatedBefore($borderCreationDate);
        });
        $files = array_filter($files, function (File $file) {
            return substr($file->name(), -4) === '.php';
        });
        $files = array_filter($files, function (File $file) {
            return
                strpos($file->name(), 'tests') === false &&
                strpos($file->name(), 'config') === false &&
                strpos($file->name(), 'i18n') === false;
        });

        $changesCount = array_map(function (File $file) {
            return count($file->changes());
        }, $files);


        $percentile90 = $this->percentile($changesCount, 90);

        $files = array_filter($files, function (File $file) use ($percentile90) {
            return count($file->changes()) >= $percentile90;
        });

        usort($files, function (File $a, File $b) {
            return $a->modificationScore() < $b->modificationScore();
        });
        return $files;
    }
}
