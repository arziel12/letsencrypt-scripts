<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\Enum;

use Consistence\Enum\Enum;

class DnsRecordType extends Enum
{
	public const A = 'A';
	public const AAAA = 'AAAA';
	public const CNAME = 'CNAME';
	public const TXT = 'TXT';
	public const MX = 'MX';
}