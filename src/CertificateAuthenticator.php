<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt;

use Arziel\Letsencrypt\Clients\IDNSProvider;
use Arziel\Letsencrypt\DTO\DnsRecord;
use Arziel\Letsencrypt\Enum\DnsRecordType;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

final class CertificateAuthenticator
{
	private const RECORD_NAME = '_acme-challenge';
	private const CONFIG_PATH = __DIR__ . '/../config/config.neon';
	private $client;
	/** @var array */
	private $config;
	private $providers = [];
	private $domains = [];
	
	public function __construct()
	{
		$this->config = Neon::decode(FileSystem::read(self::CONFIG_PATH));
		
		foreach ($this->config['providers'] as $key => $provider) {
			$this->providers[$key] = new $provider['class']($provider['auth'], $provider['wait']);
		}
		
		foreach ($this->config['domains'] as $key => $provider) {
			$this->domains[$key] = $this->providers[$provider];
		}
	}
	
	public function checkRecord(DnsRecord $record): bool
	{
		$domain = $record->getDomain();
		$token = $record->getToken();
		
		echo "Check TXT Records" . \PHP_EOL;
	
		while (true) {
			$records = \dns_get_record(self::RECORD_NAME . '.' . $domain, DNS_TXT);
			
			foreach ($records as $record) {
				if (isset($record['txt']) && $record['txt'] === $token) {
					echo "[$domain] TXT value ($token) found at DNS" . \PHP_EOL;
					
					return true;
				}
			}
			
			echo "[$domain] TXT value ($token) not found at DNS" . \PHP_EOL;
			echo "[$domain] ";
			$this->sleep(15);
		}
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
		$record = new DnsRecord(null, $domain, $token, DnsRecordType::get(DnsRecordType::TXT), 10);
		
		$dnsClient = $this->resolveProvider($record);
		
		$dnsClient->add($record);
		
		$this->checkRecord($record);
	}
	
	public function cleanup(string $domain, string $token): void
	{
		$record = new DnsRecord(null, $domain, $token, DnsRecordType::get(DnsRecordType::TXT), 10);
		
		$dnsClient = $this->resolveProvider($record);
		
		$dnsClient->delete($record);
	}
	
	private function sleep(int $seconds): void
	{
		echo \sprintf(
			'Sleep for %s sec(s) till %s%s',
			$seconds,
			date('d.m.y H:i:s', time() + $seconds),
			PHP_EOL
		);
		
		\sleep($seconds);
	}
}