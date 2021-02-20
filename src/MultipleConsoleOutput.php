<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt;

use Nette\Utils\FileSystem;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

final class MultipleConsoleOutput extends ConsoleOutput
{
	private ?string $logFile = null;
	
	public function __construct(
		?string $logFile = null,
		int $verbosity = self::VERBOSITY_NORMAL,
		bool $decorated = null,
		OutputFormatterInterface $formatter = null
	)
	{
		parent::__construct(
			$verbosity,
			$decorated,
			$formatter
		);
		
		$this->logFile = $logFile;
	}
	
	public function write($messages, bool $newline = false, int $options = self::OUTPUT_NORMAL)
	{
		if ($this->logFile !== null) {
			FileSystem::createDir(\dirname($this->logFile));
			
			@file_put_contents($this->logFile, $messages . ($newline ? \PHP_EOL : ''), FILE_APPEND | LOCK_EX);
		}
		
		return parent::write($messages, $newline, $options);
	}
}
