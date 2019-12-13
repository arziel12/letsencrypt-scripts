<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

\Tracy\Debugger::enable(false);

if ($argc !== 4) {
	die('invalid arguments');
}

$action = $argv[1];

$client = new \Arziel\Letsencrypt\CertificateAuthenticator();

if ($action === 'authenticate') {
	$client->authenticate($argv[2], $argv[3]);
} elseif ($action === 'cleanup') {
	$client->cleanup($argv[2], $argv[3]);
}
