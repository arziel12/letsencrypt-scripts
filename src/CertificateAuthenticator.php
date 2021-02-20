<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt;

use Arziel\Letsencrypt\Clients\IDNSProvider;
use Arziel\Letsencrypt\DTO\DnsRecord;
use Arziel\Letsencrypt\Enum\DnsRecordType;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Output\OutputInterface;

final class CertificateAuthenticator
{
	private const RECORD_NAME = '_acme-challenge';
	private const CONFIG_PATH = __DIR__ . '/../config/config.neon';
	private const SECONDS = 15;
	private $client;
	/** @var array */
	private $config;
	private $providers = [];
	private $domains = [];
	/**
	 * @var OutputInterface
	 */
	private $output;
	
	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
		$this->config = Neon::decode(FileSystem::read(self::CONFIG_PATH));
		
		foreach ($this->config['providers'] as $key => $provider) {
			$this->providers[$key] = new $provider['class']($provider['auth'], $provider['wait']);
		}
		
		foreach ($this->config['domains'] as $key => $provider) {
			$this->domains[$key] = $this->providers[$provider];
		}
	}
	
	public function checkRecord(IDNSProvider $provider, DnsRecord $dnsRecord): bool
	{
		$domain = $dnsRecord->getDomain();
		$token = $dnsRecord->getToken();
		
		$this->output->writeln('Check TXT Records');
		
		$wait = $provider->getWait();
		
		while ($wait > 0) {
			$records = \dns_get_record(self::RECORD_NAME . '.' . $domain, DNS_TXT);
			
			foreach ($records as $record) {
				if (isset($record['txt']) && $record['txt'] === $token) {
					$this->output->writeln("[$domain] TXT value ($token) found at DNS");
					
					return true;
				}
			}
			
			$this->output->writeln("[$domain] TXT value ($token) not found at DNS");
			$this->output->write("[$domain] ");
			
			$wait -= self::SECONDS;
			$this->sleep(self::SECONDS);
		}
		
		$this->output->write("[$domain] Timeouted");
		
		return false;
	}
	
	public function resolveProvider(DnsRecord $record): IDNSProvider
	{
		if (isset($this->domains[$record->getDomain()]) === false) {
			throw new \LogicException(\sprintf('Can\'t find provider fo %s domain', $record->getDomain()));
		}
		
		return $this->domains[$record->getDomain()];
	}
	
	public function authenticate(string $domain, string $token): void
	{
		$record = new DnsRecord(null, $domain, $token, DnsRecordType::TXT(), 10);
		
		$dnsClient = $this->resolveProvider($record);
		
		$dnsClient->add($record);
		
		$this->checkRecord($dnsClient, $record);
	}
	
	public function cleanup(string $domain, string $token): void
	{
		$record = new DnsRecord(null, $domain, $token, DnsRecordType::TXT(), 10);
		
		$dnsClient = $this->resolveProvider($record);
		
		$dnsClient->delete($record);
	}
	
	private function sleep(int $seconds): void
	{
		$this->output->writeln(
			\sprintf(
				'Sleep for %s sec(s) till %s',
				$seconds,
				date('d.m.y H:i:s', time() + $seconds)
			)
		);
		
		\sleep($seconds);
	}
}
