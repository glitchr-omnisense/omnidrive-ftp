<?php

namespace Omnidrive\Ftp\Tests;

use Omnidrive\Drive;
use Omnidrive\Ftp\FtpDriveFactory;
use Omnidrive\Testing\DriveContractTests;
use PHPUnit\Framework\TestCase;

/**
 * The contract against a real FTP server: the harness's
 * (docker compose run --rm omnidrive test), or the one OMNIDRIVE_FTP_* name.
 * Skipped when none is named.
 */
final class FtpLiveTest extends TestCase
{
    use DriveContractTests;

    protected function drive(): Drive
    {
        $host = getenv('OMNIDRIVE_FTP_HOST');
        if (false === $host || '' === $host) {
            self::markTestSkipped('No FTP server named (OMNIDRIVE_FTP_HOST).');
        }

        return (new FtpDriveFactory())->create([
            'host' => $host,
            'port' => getenv('OMNIDRIVE_FTP_PORT') ?: null,
            'username' => (string) getenv('OMNIDRIVE_FTP_USERNAME'),
            'password' => (string) getenv('OMNIDRIVE_FTP_PASSWORD'),
            'root' => getenv('OMNIDRIVE_FTP_ROOT') ?: '/',
        ], 'ftp');
    }
}
