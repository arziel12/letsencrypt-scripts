<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

\Tracy\Debugger::enable(false);

$console = new \Symfony\Component\Console\Application('Letsencrypt authenticator');

$console->add(new \Arziel\Letsencrypt\Command());

$console->setDefaultCommand('run');

$output = new \Symfony\Component\Console\Output\BufferedOutput();

$input = new \Symfony\Component\Console\Input\ArgvInput();
$console->run(
	$input,
	$output
);

\Nette\Utils\FileSystem::write(
	sprintf("/var/log/letsencript-scripts/%s-%s.log", __DIR__, implode('-', $input->getArguments()), date('ymd-His')),
	$output->fetch()
);
