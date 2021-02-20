<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\Clients;

use Arziel\Conditions\Any;
use Arziel\Letsencrypt\DTO\DnsRecord;
use Nette\Utils\Json;

final class SubregDNSClient implements IDNSProvider
{
	private const URL = 'https://soap.subreg.cz/cmd.php';
	private const RECORD_TYPE = 'TXT';
	private const RECORD_NAME = '_acme-challenge';
	private $client;
	/**
	 * @var string
	 */
	private $authToken;
	
	/**
	 * @var int
	 */
	private $wait;
	
	public function __construct(array $auth, int $wait)
	{
		$this->client = new \SoapClient(
			null,
			[
				'location' => self::URL,
				'uri'      => 'http://PRODUCTION/soap',
			]
		);
		
		if (Any::isNull($auth['password'], $auth['login'])) {
			throw new \LogicException('Please define auth in config');
		}
		
		
		$this->authToken = $this->authorize($auth['login'], $auth['password']);
		
		$this->wait = $wait;
	}
	
	public function getWait(): int
	{
		return $this->wait;
	}
	
	public function add(DnsRecord $row): void
	{
		echo "[Subreg][{$row->getDomain()}] add TXT record {$row->getToken()}" . \PHP_EOL;
		
		$this->addDnsTxtRecord($this->authToken, $row->getDomain(), $row->getToken());
	}
	
	public function delete(DnsRecord $row): void
	{
		$token = $row->getToken();
		$domain = $row->getDomain();
		
		$records = $this->getRecords($this->authToken, $domain);
		
		foreach ($records as ['id' => $id, 'name' => $name, 'type' => $type, 'content' => $content]) {
			if ($name === self::RECORD_NAME) {
				if ($type === self::RECORD_TYPE) {
					if ($content === $token) {
						echo "[Subreg][{$domain}] delete $token" . \PHP_EOL;
				
						$this->deleteTxt($this->authToken, $domain, $id);
					}
				}
			}
		}
	}
	
	
	public function checkRecord(
		$domain,
		$token,
		int $retries = 4 * 20
	): bool
	{
		echo "Check TXT Records" . \PHP_EOL;
		$records = \dns_get_record(self::RECORD_NAME . '.' . $domain, DNS_TXT);
		
		foreach ($records as $record) {
			
			if (isset($record['txt']) && $record['txt'] === $token) {
				echo "[$domain] TXT found at DNS" . \PHP_EOL;
				
				return true;
			}
		}
		
		if ($retries > 0) {
			$this->sleep(15);
			
			return $this->checkRecord($domain, $token, $retries - 1);
		}
		
		return false;
	}
	
	public function cleanup(
		array $config,
		string $domain
	): void
	{
		
	}
	
	private function request(
		string $method,
		array $data
	)
	{
		$response = $this->client->__call(
			$method,
			[
				'data' => $data,
			]
		);
		
		if ($response['status'] === 'ok') {
			return $response['data'] ?? null;
		}
		
		\dump($response);
		
		throw new \LogicException(Json::encode($response));
	}
	
	private function getRecords(
		string $ssid,
		string $domain
	): array
	{
		$response = $this->request(
			'Get_DNS_Zone',
			[
				'ssid'   => $ssid,
				'domain' => $domain,
			]
		);
		
		return $response['records'];
	}
	
	private function deleteTxt(
		string $ssid,
		string $domain,
		string $id
	)
	{
		$this->request(
			'Delete_DNS_Record',
			[
				'ssid'   => $ssid,
				'domain' => $domain,
				'record' => ['id' => $id],
			]
		);
	}
	
	private function addDnsTxtRecord(
		string $ssid,
		string $domain,
		string $value
	)
	{
		$this->request(
			'Add_DNS_Record',
			[
				'ssid'   => $ssid,
				'domain' => $domain,
				'record' =>
					[
						'name'    => self::RECORD_NAME,
						'type'    => 'TXT',
						'ttl'     => 600,
						'content' => $value,
					],
			]
		);
	}
	
	private function authorize(
		string $login,
		string $password
	): string
	{
		$response = $this->request(
			'Login',
			[
				'login'    => $login,
				'password' => $password,
			]
		);
		
		return $response['ssid'];
	}
	
	private function sleep(
		int $seconds
	): void
	{
		echo "nothing, sleep for $seconds sec(s)" . \PHP_EOL;
		
		\sleep($seconds);
	}
}
