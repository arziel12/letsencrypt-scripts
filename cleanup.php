<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

use Nette\Utils\Json;

\Tracy\Debugger::enable(false);

var_dump($argv);

if ($argc !== 3) {
	die('invalid arguments');
}

$config = Json::decode(file_get_contents(__DIR__ . '/config.json'), Json::FORCE_ARRAY);

$client = new \Arziel\Letsencrypt\SubregClient();

$client->deleteEntry($config, $argv[1], $argv[2]);