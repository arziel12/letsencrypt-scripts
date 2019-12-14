<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

\Tracy\Debugger::enable(false);

$console = new \Symfony\Component\Console\Application('Letsencrypt authenticator');

$console->add(new \Arziel\Letsencrypt\Command());

$console->setDefaultCommand('run');

$console->run(new \Symfony\Component\Console\Input\ArgvInput(), new \Symfony\Component\Console\Output\ConsoleOutput());

