<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

\Nette\Utils\FileSystem::createDir(__DIR__ . '/log', 775);

\Tracy\Debugger::enable(true, __DIR__ . '/log');

$console = new \Symfony\Component\Console\Application('Letsencrypt authenticator');

$console->add(new \Arziel\Letsencrypt\Command());

$console->setDefaultCommand('run');

$argv[] = $_SERVER['CERTBOT_DOMAIN'];
$argv[] = $_SERVER['CERTBOT_VALIDATION'];

$argc++;
$argc++;

$output = new \Symfony\Component\Console\Output\BufferedOutput();
$input = new \Symfony\Component\Console\Input\ArgvInput();


$console->run(
	$input,
	$output
);

echo $output->fetch();

\Nette\Utils\FileSystem::write(
	sprintf("/var/log/letsencript-scripts/%s-%s.log", __DIR__, implode('-', $input->getArguments()), date('ymd-His')),
	$output->fetch()
);
