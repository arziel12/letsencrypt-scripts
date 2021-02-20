<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\Enum;

/**
 * @method static self A()
 * @method static self AAAA()
 * @method static self CNAME()
 * @method static self TXT()
 * @method static self MX()
 */
final class DnsRecordType extends AbstractEnum
{
	public const A = 'A';
	public const AAAA = 'AAAA';
	public const CNAME = 'CNAME';
	public const TXT = 'TXT';
	public const MX = 'MX';
}
