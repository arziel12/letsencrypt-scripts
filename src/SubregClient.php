<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt;

final class SubregClient
{
	private const URL = 'https://soap.subreg.cz/cmd.php';
	private const RECORD_TYPE = 'TXT';
	private const RECORD_NAME = '_acme-challenge';
	private $client;
	
	public function __construct()
	{
		$this->client = new \SoapClient(
			null,
			[
				'location' => self::URL,
				'uri'      => 'http://PRODUCTION/soap',
			]
		);
	}
	
	public function addEntry(array $config, string $domain, string $token): void
	{
		echo "[$domain] Add Entry $token" . \PHP_EOL;
		
		$ssid = $this->authorize($config['login'], $config['password']);
		
		$this->addDnsTxtRecord($ssid, $domain, $token);
		
		$this->checkRecord($domain, $token);
	}
	
	public function deleteEntry(array $config, string $domain, string $token): void
	{
		$ssid = $this->authorize($config['login'], $config['password']);
		
		$records = $this->getRecords($ssid, $domain);
		
		foreach ($records as ['id' => $id, 'name' => $name, 'type' => $type, 'content' => $content]) {
			if ($name === self::RECORD_NAME) {
				if ($type === self::RECORD_TYPE) {
					if ($content === $token) {
						echo "Delete $token" . \PHP_EOL;
						
						
						$this->deleteTxt($ssid, $domain, $id);
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
		
		throw new \LogicException();
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