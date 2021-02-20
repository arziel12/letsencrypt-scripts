<?php declare(strict_types = 1);

namespace Arziel\Letsencrypt\Enum;

use Consistence\Enum\Enum;

abstract class AbstractEnum extends Enum
{
	/**
	 * @param array<string, string> $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function __callStatic($name, array $args): self
	{
		$constantName = \sprintf('%s::%s', static::class, $name);
		
		if (\defined($constantName)) {
			return self::get(\constant($constantName));
		}
		
		throw new \InvalidArgumentException("Method nor constant {$constantName} not found.");
	}
}
