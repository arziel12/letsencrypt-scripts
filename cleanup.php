<?php declare(strict_types = 1);

require_once __DIR__ . '/vendor/autoload.php';

use Arziel\Letsencrypt\CertificateAuthenticator;

$domain = getenv('CERTBOT_DOMAIN');
$validation = getenv('CERTBOT_VALIDATION');

$output = new \Arziel\Letsencrypt\MultipleConsoleOutput(
	sprintf(
		'%s/log/result/%s/cleanup.%s.%s.log',
		__DIR__,
		date('Y-m-d'),
		$domain,
		microtime(true)
	)
);

$client = new CertificateAuthenticator($output);
$client->authenticate($domain, $validation);
