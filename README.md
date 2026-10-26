# omnidrive/ftp

A directory of an FTP or FTPS server as an [omnidrive](https://github.com/glitchr-omnisense/omnidrive)
drive, through league/flysystem-ftp (PHP's ftp extension).

```sh
composer require omnisense/omnidrive omnidrive/ftp
```

```php
use Omnidrive\Ftp\FtpDriveFactory;
use Omnidrive\Registry;

$drives = new Registry([new FtpDriveFactory()], [
    'legacy' => ['factory' => 'ftp', 'options' => ['host' => 'ftp.example.org', 'username' => 'site', 'password' => getenv('FTP_PASSWORD'), 'root' => '/www/files', 'ssl' => true]],
]);
```

| Option | Default | |
|---|---|---|
| `host`, `username`, `password` | required | |
| `port` | 21 | |
| `root` | `/` | the directory behind the drive's paths |
| `ssl` | false | FTPS (explicit) |
| `passive` | true | |
| `ignore_passive_address` | the extension's | a server behind NAT: the address it gives for passive mode ignored, its own used |
| `timeout` | 90 s | |
| `utf8` | false | |
| `binary` | true | transfers in binary mode, else ASCII |
| `system_type` | asked | `unix` or `windows` |
| `recurse_manually` | true | a deep listing walked directory by directory |
| `timestamps_on_unix_listings` | false | |
| `permissions`, `public_url`, `visibility`, `directory_visibility`, `retain_visibility` | | as every drive |

The connection is opened on the first operation. `capabilities()`: not local, public addresses with
`public_url`, no temporary address.

## Verified, and not

- Verified (2026-10-23, omnidrive's harness, PHP 8.4): the contract's tests against Pure-FTPd
  (passive mode, a chrooted user); in plain PHP (`bare --live`); a server that does not answer is a
  `DriveException` of the operation asked.
- Not verified: FTPS; a server behind NAT; Windows servers. vsftpd (3.0.5, seen 2026-10-23): its
  STAT answers nothing for a file below a subdirectory, so a file's visibility cannot be read there
  and a copy, which keeps its source's visibility by default, fails: give such a drive
  `retain_visibility: false`.

License: MIT. Unofficial; not affiliated with The PHP League.
