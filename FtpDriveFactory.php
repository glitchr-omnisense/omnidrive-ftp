<?php

namespace Omnidrive\Ftp;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use Omnidrive\Config;
use Omnidrive\Drive;
use Omnidrive\Flysystem\FlysystemDriveFactory;

/**
 * A directory of an FTP or FTPS server, through league/flysystem-ftp (PHP's ftp
 * extension):
 *
 *   (new FtpDriveFactory())->create(['host' => 'ftp.example.org', 'username' => 'site', 'password' => '...', 'root' => '/www/uploads', 'ssl' => true]);
 *
 * Options: host, username and password (required), port (21), root (the
 * directory behind the drive's paths, "/"), ssl (FTPS, explicit), passive (true),
 * ignore_passive_address (the address a server behind NAT gives for passive
 * mode ignored, its own used), timeout (90 s), utf8, binary (true: transfers in
 * binary mode, else ASCII), system_type ("unix", "windows": else asked),
 * recurse_manually (a deep listing walked directory by directory, true),
 * timestamps_on_unix_listings, public_url, visibility, directory_visibility,
 * permissions (as every drive).
 *
 * The connection is opened on the first operation, not here.
 */
final class FtpDriveFactory extends FlysystemDriveFactory
{
    protected function populate(Config $c): void
    {
        $c->defaults([
            'omnidrive.factory_name' => 'ftp',
            'omnidrive.factory_title' => 'An FTP or FTPS server',
            'omnidrive.required_options' => ['host', 'username', 'password'],
            'port' => 21,
            'root' => '/',
            'ssl' => false,
            'passive' => true,
            'timeout' => 90,
            'utf8' => false,
            'binary' => true,
            'recurse_manually' => true,
            'timestamps_on_unix_listings' => false,
        ]);
    }

    protected function build(Config $c, string $name): Drive
    {
        $ignorePassiveAddress = $c->get('ignore_passive_address');
        $options = FtpConnectionOptions::fromArray([
            'host' => (string) $c->string('host'),
            'root' => $c->string('root') ?? '/',
            'username' => (string) $c->string('username'),
            'password' => (string) $c->string('password'),
            'port' => $c->int('port', 21),
            'ssl' => $c->bool('ssl'),
            'timeout' => $c->int('timeout', 90),
            'utf8' => $c->bool('utf8'),
            'passive' => $c->bool('passive', true),
            'transferMode' => $c->bool('binary', true) ? \FTP_BINARY : \FTP_ASCII,
            'systemType' => $c->string('system_type'),
            'ignorePassiveAddress' => null === $ignorePassiveAddress || '' === $ignorePassiveAddress ? null : $c->bool('ignore_passive_address'),
            'timestampsOnUnixListingsEnabled' => $c->bool('timestamps_on_unix_listings'),
            'recurseManually' => $c->bool('recurse_manually', true),
        ]);

        return $this->drive($name, new FtpAdapter($options, null, null, $this->unixVisibility($c)), $c);
    }
}
