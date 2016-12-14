<?php
use Bekh6ex\GitMiner\Application;

require_once __DIR__ . '/vendor/autoload.php';

(new Application(__DIR__ . '/report'))->main(fopen(__DIR__ . '/git-log.txt', 'r'));