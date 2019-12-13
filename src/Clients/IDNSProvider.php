<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\Clients;

use Arziel\Letsencrypt\DTO\DnsRecord;

interface IDNSProvider
{
	public function add(DnsRecord $row): void;
	public function delete(DnsRecord $row): void;
	
	public function getWait(): int;
	
}