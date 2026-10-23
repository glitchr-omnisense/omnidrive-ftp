<?php

namespace Omnidrive\Ftp\Tests;

use Omnidrive\Exception\DriveException;
use Omnidrive\Exception\InvalidConfigException;
use Omnidrive\Ftp\FtpDriveFactory;
use PHPUnit\Framework\TestCase;

final class FtpDriveTest extends TestCase
{
    public function testNothingConnectsUntilTheFirstOperation(): void
    {
        $drive = (new FtpDriveFactory())->create(['host' => '192.0.2.1', 'username' => 'site', 'password' => 'x', 'timeout' => 1], 'legacy');

        self::assertSame('legacy', $drive->name());
        self::assertSame(['local' => false, 'publicUrls' => false, 'temporaryUrls' => false], $drive->capabilities()->toArray());

        try {
            $drive->exists('a.txt');
            self::fail('a server that does not answer');
        } catch (DriveException $e) {
            self::assertSame(['exists', 'legacy', 'a.txt'], [$e->operation, $e->drive, $e->path]);
        }
    }

    public function testAPublicAddressIsThePrefixGiven(): void
    {
        $drive = (new FtpDriveFactory())->create(['host' => 'ftp.example.org', 'username' => 'site', 'password' => 'x', 'public_url' => 'https://www.example.org/files']);

        self::assertSame('https://www.example.org/files/a.pdf', $drive->publicUrl('a.pdf'));
    }

    public function testTheHostTheUserAndThePasswordAreRequired(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The "ftp" drive needs: host, username, password.');
        (new FtpDriveFactory())->create(['port' => 2121]);
    }
}
