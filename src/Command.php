<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
	public function __construct()
	{
		parent::__construct('run');
		
		$this->addArgument('action', InputArgument::REQUIRED);
		$this->addArgument('domain', InputArgument::REQUIRED);
		$this->addArgument('token', InputArgument::REQUIRED);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$action = $input->getArgument('action');
		$domain = $input->getArgument('domain');
		$token = $input->getArgument('token');
		
		$client = new CertificateAuthenticator($output);
		
		try {
			if ($action === 'authenticate') {
				$client->authenticate($domain, $token);
			} elseif ($action === 'cleanup') {
				$client->cleanup($domain, $token);
			}
		} catch (\Throwable $e) {
			$exception = \get_class($e);
			
			$output->writeln("<error>{$exception}</error>");
			$output->writeln("<error>{$e->getMessage()}</error>");
			
			return 1;
		}
		
		return 0;
	}
}
