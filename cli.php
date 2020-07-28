<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

\Tracy\Debugger::enable(false);

$console = new \Symfony\Component\Console\Application('Letsencrypt authenticator');

$console->add(new \Arziel\Letsencrypt\Command());

$console->setDefaultCommand('run');

$output = new \Symfony\Component\Console\Output\BufferedOutput();

$input = new \Symfony\Component\Console\Input\ArgvInput();

$input->setArgument('domain', $_ENV['CERTBOT_DOMAIN']);
$input->setArgument('token', $_ENV['CERTBOT_VALIDATION']);

$console->run(
	$input,
	$output
);

echo $output->fetch();

\Nette\Utils\FileSystem::write(
	sprintf("/var/log/letsencript-scripts/%s-%s.log", __DIR__, implode('-', $input->getArguments()), date('ymd-His')),
	$output->fetch()
);
