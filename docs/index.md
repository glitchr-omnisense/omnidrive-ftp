# omnidrive/ftp

A directory of an FTP or FTPS server as an
[omniscient/omnidrive](https://github.com/glitchr-studio/omnidrive) drive, through
league/flysystem-ftp (`FtpAdapter`, PHP's `ftp` extension).

## Installation

```sh
composer require omniscient/omnidrive omnidrive/ftp
```

Requires PHP 8.2 with `ext-ftp`, `omniscient/omnidrive` (1.x) and `league/flysystem-ftp` (^3.0).

## Credentials

An FTP account: `username` and `password` (both required). With `ssl`, the session is explicit
FTPS (the extension's `ftp_ssl_connect`).

## Options

`new FtpDriveFactory()`; factory name `ftp`.

| Option | Type | Default | Required | |
|---|---|---|---|---|
| `host`, `username`, `password` | string | - | yes | |
| `port` | int | `21` | no | |
| `root` | string | `/` | no | the directory behind the drive's paths |
| `ssl` | bool | `false` | no | FTPS (explicit) |
| `passive` | bool | `true` | no | passive mode |
| `ignore_passive_address` | bool | the adapter's | no | a server behind NAT: the address it gives for passive mode ignored, its own used |
| `timeout` | int | `90` | no | seconds |
| `utf8` | bool | `false` | no | `OPTS UTF8 ON` |
| `binary` | bool | `true` | no | binary transfers, else ASCII |
| `system_type` | `unix`, `windows` | asked of the server | no | how listings are parsed |
| `recurse_manually` | bool | `true` | no | a deep listing walked directory by directory |
| `timestamps_on_unix_listings` | bool | `false` | no | dates read from Unix listings |
| `permissions` | map | `{file: {public: 0644, private: 0600}, dir: {public: 0755, private: 0700}}` | no | the Unix mode of each visibility; octal strings accepted, a mode left out keeps its default |
| `public_url` | string or list | none | no | the prefix files are served from, or several (sharded) |
| `visibility` | `public`/`private` | `public` (the adapter's) | no | a write's visibility when it does not say |
| `directory_visibility` | `public`/`private` | `private` | no | the same for directories |
| `retain_visibility` | bool | `true` | no | whether a copy keeps its source's visibility |

## Capabilities

| Capability | |
|---|---|
| `local` | no |
| `publicUrls` | with `public_url` |
| `temporaryUrls` | no |
| visibility per file | yes: the file's Unix mode (`SITE CHMOD`), by `permissions` |
| checksum | any algorithm `hash()` knows (the file downloaded and hashed) |

## Behaviour

The connection is opened on the first operation and kept. Listings are parsed from the server's
`LIST` (Unix or Windows format, by `system_type` or as the server says); a file's visibility is
read from its mode in the listing of its directory. A copy downloads and uploads again.

## Example

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Omnidrive\Ftp\FtpDriveFactory;
use Omnidrive\Registry;

$drives = new Registry([new FtpDriveFactory()], [
    'legacy' => ['factory' => 'ftp', 'options' => ['host' => 'ftp', 'username' => 'omnidrive', 'password' => 'omnidrive-harness', 'root' => '/', 'public_url' => 'https://www.example.org/files', 'timeout' => 10]],
]);
$legacy = $drives->get('legacy');

$legacy->write('pdf/brochure.pdf', '%PDF-1.4 ...');
$legacy->copy('pdf/brochure.pdf', 'pdf/archive/brochure-2025.pdf');
echo json_encode($legacy->capabilities()->toArray()), "\n";
echo $legacy->publicUrl('pdf/brochure.pdf'), "\n";
foreach ($legacy->list('pdf', deep: true) as $entry) {
    echo $entry->directory ? 'dir  ' : 'file ', $entry->path, ' ', $entry->size ?? '-', ' ', $entry->visibility?->value ?? '-', "\n";
}
$legacy->deleteDirectory('pdf');
```

```
{"local":false,"publicUrls":true,"temporaryUrls":false}
https://www.example.org/files/pdf/brochure.pdf
dir  pdf/archive - public
file pdf/archive/brochure-2025.pdf 12 public
file pdf/brochure.pdf 12 public
```

(run on 2026-10-25 in omnidrive's harness against its Pure-FTPd, passive mode, a chrooted user)

## Errors

A server that does not answer, a refused login, a refused command are `DriveException`s of the
operation asked; a missing file read is a `FileNotFoundException`. A missing `host`, `username` or
`password` is an `InvalidConfigException`. With vsftpd (3.0.5, seen 2026-10-23) a file's
visibility cannot be read below a subdirectory (its `STAT` answers nothing), and a copy, which
keeps its source's visibility by default, fails there: give such a drive
`retain_visibility: false`.

## Verified, and not

Verified (2026-10-23 and 2026-10-25, omnidrive's harness, PHP 8.4): the contract's tests against
Pure-FTPd (passive mode, a chrooted user); in plain PHP (`bare --live`); a server that does not
answer is a `DriveException` of the operation asked. Not verified: FTPS; a server behind NAT;
Windows servers.

## Responsibility

An independent, unofficial client, not affiliated with The PHP League. Whoever runs it is responsible for the data it stores and the
access it gives (a public or temporary address serves the file to whoever holds it); see the core's
Responsibility section ([omniscient/omnidrive](https://github.com/glitchr-studio/omnidrive#responsibility)).
