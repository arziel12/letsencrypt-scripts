<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\Clients;

use Arziel\Conditions\Any;
use Arziel\Letsencrypt\DTO\DnsRecord;
use Arziel\Letsencrypt\Enum\DnsRecordType;
use GuzzleHttp\Client;
use Nette\Utils\Json;

class WedosDNSClient implements IDNSProvider
{
	private const RECORD_NAME = '_acme-challenge';
	private const API_URL = 'https://api.wedos.com/wapi/json';
	public const INVALID_REQUEST_DNS_ROW_EXISTS = 2316;
	/**
	 * @var int
	 */
	private $wait;
	/**
	 * @var string
	 */
	private $authToken;
	/**
	 * @var Client
	 */
	private $client;
	/** @var string */
	private $user;
	
	public function __construct(array $auth, int $wait)
	{
		$this->client = new Client();
		
		$this->wait = $wait;
		
		if (Any::isNull(...\array_values($auth))) {
			throw new \LogicException('Please define all parameters in provider.wedos.auth');
		}
		
		$this->user = $auth['login'];
		$this->authToken = sha1($auth['login'] . sha1($auth['wapiToken']) . date('H', time()));
	}
	
	public function add(DnsRecord $row): void
	{
		$this->request(
			'dns-row-add',
			[
				'domain' => $row->getDomain(),
				'name'   => self::RECORD_NAME,
				'ttl'    => 300,
				'type'   => $row->getDnsRowType()->getValue(),
				'rdata'  => $row->getToken(),
			]
		);
		
		$this->request(
			'dns-domain-commit',
			[
				'name' => $row->getDomain(),
			]
		);
		
		echo '[Wedos] Row added.' . \PHP_EOL;
	}
	
	public function delete(DnsRecord $row): void
	{
		$token = $row->getToken();
		$domain = $row->getDomain();
		
		$response = $this->request(
			'dns-rows-list',
			[
				'domain' => $domain,
			]
		);
		
		foreach ($response['data']['row'] as $data) {
			if ($data['rdtype'] === DnsRecordType::TXT) {
				if ($data['name'] === self::RECORD_NAME) {
					
					if ($data['rdata'] === $token) {
						echo '[Wedos] - delete ' . $token . ' TXT dns' . \PHP_EOL;
						
						$this->request(
							'dns-row-delete',
							[
								'domain' => $domain,
								'row_id' => $data['ID'],
							]
						);
						
						return;
					}
				}
			}
		}
		
		echo '[Wedos] - entry not found' . \PHP_EOL;
	}
	
	public function getWait(): int
	{
		return $this->wait;
	}
	
	protected function request(string $command, array $data = []): array
	{
		// provedení volání
		$array = [
			'request' => [
				'user'    => $this->user,
				'auth'    => $this->authToken,
				'command' => $command,
				'data'    => $data,
			],
		];
		
		// inicializace cURL session
		$ch = curl_init();
		
		// nastavení URL a POST dat
		curl_setopt($ch, CURLOPT_URL, self::API_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'request=' . Json::encode($array));
		
		// odpověď chceme jako návratovou hodnotu curl_exec()
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// doba, po kterou skript čeká na odpověď
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		
		// vypnutí kontrol SSL certifikátů
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$json = curl_exec($ch);
		
		$response = Json::decode($json, Json::FORCE_ARRAY)['response'];
		
		if ($response['code'] === 2051) {
			throw new \LogicException($response['result']);
		}
		
		if ($response['code'] === self::INVALID_REQUEST_DNS_ROW_EXISTS) {
			return $response;
		}
		
		return $response;
	}
}
