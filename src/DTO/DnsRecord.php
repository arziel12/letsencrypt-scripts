<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\DTO;

use Arziel\Letsencrypt\Enum\DnsRecordType;

class DnsRecord
{
	/**
	 * @var int|null
	 */
	private $id;
	/** @var string */
	private $domain;
	/**
	 * @var string
	 */
	private $token;
	/**
	 * @var DnsRecordType
	 */
	private $dnsRowType;
	/**
	 * @var int
	 */
	private $priority;
	
	public function __construct(?int $id, string $domain, string $token, DnsRecordType $dnsRowType, int $priority)
	{
		$this->id = $id;
		$this->domain = $domain;
		$this->token = $token;
		$this->dnsRowType = $dnsRowType;
		$this->priority = $priority;
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getDomain(): string
	{
		return $this->domain;
	}
	
	public function getToken(): string
	{
		return $this->token;
	}
	
	public function getDnsRowType(): DnsRecordType
	{
		return $this->dnsRowType;
	}
	
	public function getPriority(): int
	{
		return $this->priority;
	}
	
	public function setId(?int $id): void
	{
		$this->id = $id;
	}
	
	public function setToken(string $token): void
	{
		$this->token = $token;
	}
}