<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

\Nette\Utils\FileSystem::createDir(__DIR__ . '/log', 775);

//\Sentry\init(['dsn' => 'https://f8e4a5c829a14dd985823f1c52908f50@o272072.ingest.sentry.io/5369703']);

\Tracy\Debugger::enable(true, __DIR__ . '/log');

$console = new \Symfony\Component\Console\Application('Letsencrypt authenticator');
$console->add(new \Arziel\Letsencrypt\Command());

$input = new \Symfony\Component\Console\Input\ArgvInput();

$output = new \Arziel\Letsencrypt\MultipleConsoleOutput(
	sprintf(
		'%s/log/result/%s/%s.log',
		__DIR__,
		date('Y-m-d'),
		microtime(true)
	)
);
$console->run(
	$input,
	$output
);
