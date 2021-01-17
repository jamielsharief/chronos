# Chronos Database Backup & Restore

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://github.com/jamielsharief/chronos/workflows/CI/badge.svg)](https://github.com/jamielsharief/backup-restore/actions)
[![coverage](https://coveralls.io/repos/github/jamielsharief/chronos/badge.svg?branch=master)](https://coveralls.io/github/jamielsharief/backup-restore?branch=master)

Chronos Database Backup and Restore provides easy and painless backups of your MySQL, Postgres and Sqlite databases.

Each process, wether dumping, compression or encryption is handled by the specific binary application and then the relevant extension is appened to the output filename.  For example, `chronos` does not compress using `gpg` nor encrypt with `7zip`. This means the trail on how backups were created can easily be followed, and thus can be unpacked without `chronos`, if needed.

## Requirements

- `mysqldump` if you are going to backup MySQL databases
- `pgdump` if you are going to backup Postgres databases
- `gpg`,`openssl` if you want to encrypt/decrpyt data
- `bzip2`,`gzip`,`7zip` or `zip` executable binaries if you want to compress backups

## Installation

To install this package

```linux
$ composer require jamielsharief/chronos
```

Create a folder where you will store your backups, and set the permissions so you can write
to it, assuming you are a member of the `www-data` group, for example

```bash
$ mkdir /backups
$ chown www-data:www-data /backups
$ chmod 0775 /backups
```

## Usage

First you need to initialize your installation which creates `chronos.json` in your project folder with the database settings.

```bash
$ vendor/bin/chronos init
          __                               
    _____/ /_  _________  ____  ____  _____
   / ___/ __ \/ ___/ __ \/ __ \/ __ \/ ___/
  / /__/ / / / /  / /_/ / / / / /_/ (__  ) 
  \___/_/ /_/_/   \____/_/ /_/\____/____/

version 0.1.0

Starting chronos initialization
Engine  (mysql/postgres/sqlite) [mysql]
> mysql

Host [localhost]
> mysql

Port [3306]
> 3306

Username [root]
> root

Password
> 

Backup directory
> /backups/crm

[ OK ] chronos.json saved
```

If you are installing on a server with multiple applications, then it could be be a good idea to setup a folder within your backup folder for each application , e.g. `/backups/crm`, `/backups/helpdesk`

To create backups

```bash
$ vendor/bin/chronos backup bookmarks
          __                               
    _____/ /_  _________  ____  ____  _____
   / ___/ __ \/ ___/ __ \/ __ \/ __ \/ ___/
  / /__/ / / / /  / /_/ / / / / /_/ (__  ) 
  \___/_/ /_/_/   \____/_/ /_/\____/____/

version 0.1.0

[ OK ] Backup 'bookmarks'

Took 0.06 seconds
```

You can also backup multiple databases at the same time

```bash
$ vendor/bin/chronos backup crm helpdesk accounting 
```

To use compression, simply supply the compression type `7zip`, `bzip2`, `gzip`, `unzip` or `zip`. 

```bash
$ vendor/bin/chronos backup crm --compress gzip
```

To encrypt your backups using `AES` you can use `gpg` or `ssl` which uses `openssl`.

```bash
$ vendor/bin/chronos backup crm --encrypt gpg # this will ask for password
$ CHRONOS_PASSWORD=secret vendor/bin/chronos backup crm --encrypt gpg # uses password from env var
```

To list backups

```bash
$ vendor/bin/chronos backup list
          __                               
    _____/ /_  _________  ____  ____  _____
   / ___/ __ \/ ___/ __ \/ __ \/ __ \/ ___/
  / /__/ / / / /  / /_/ / / / / /_/ (__  ) 
  \___/_/ /_/_/   \____/_/ /_/\____/____/

version 0.1.0

Backups path: /backups/bookmarks
+----------------------+--------------------------------------+----------+
| Date                 | Backup                               | Size     |
+----------------------+--------------------------------------+----------+
| 2021-01-16 14:11:15  | bookmarks-20210116141115.sql.7z.gpg  | 1.48 KB  |
| 2021-01-16 14:10:56  | bookmarks-20210116141056.sql.gpg     | 4.23 KB  |
| 2021-01-16 14:10:46  | bookmarks-20210116141046.sql.zip     | 1.53 KB  |
| 2021-01-16 14:10:42  | bookmarks-20210116141042.sql         | 4.21 KB  |
+----------------------+--------------------------------------+----------+
```

To restore a backup

```bash
$ vendor/bin/chronos restore bookmarks-20210116132550.sql.bz2
          __                               
    _____/ /_  _________  ____  ____  _____
   / ___/ __ \/ ___/ __ \/ __ \/ __ \/ ___/
  / /__/ / / / /  / /_/ / / / / /_/ (__  ) 
  \___/_/ /_/_/   \____/_/ /_/\____/____/

version 0.1.0

 WARNING   Database 'bookmarks' already exists
Existing data will be overwritten, continue?  (yes/no) [no]
> yes

[ SKIPPED ] Create database 'bookmarks'
[ OK ] Restore 'bookmarks' from 'bookmarks-20210116132550.sql.bz2'

Took 1.22 seconds
```

If you want to restore the backup to a different database than from what it was created

```bash
$ vendor/bin/chronos restore bookmarks-20210116132550.sql.bz2 <different-name>
```

When restoring backups, if the file is encrypted it will prompt you for a password.

## Compression

Backups can be compressed using using `bzip2`,`gzip`,`7zip` and `zip`.

Depending upon your linux distribution you might need to install, this is how you
can install on `ubunutu`.

For brevity I have put all compression packages in one line, however you can install just the one or ones
that you want.

Ubuntu/Debian

```
$ apt install bzip2 zip gzip pz7ip-full
```

For Redhat/CentOS/Fedora

> 7zip packages are in the EPEL repository and needs to be enabled install

```bash
$ yum install bzip2 zip gzip p7zip p7zip-plugins
```

If a dump is compressed, the filename will be appened with `bz2`,`gz`,`7z` or `zip`.

### Compression Comparison

I carried out a simple test using a single table with `1,000` and `10,000` records with random data.

This is how I generated the data

```php
$post = $this->Post->new([
    'title' => uniqid(), // random 13 char string
    'body' => Security::hex(16), // random 16 char string
    'published' => rand(0, 1)
]);
```

These are the results that I got

```
                1,000       10,000
sql             76.42 KB    770.76 KB
bzip2           18.93 KB    147.25 KB
gzip            18.93 KB    185.42 KB    
zip             19.12 KB    185.52 KB
7zip            13.97 KB    133.31 KB
```

## Encryption

Encryption is carried out after compression to be effective, however it some cases it could become less secure due to [side channel attacks](https://www.iacr.org/cryptodb/archive/2002/FSE/3091/3091.pdf). 

### GPG (recommended)

[NASA recommend](https://www.nas.nasa.gov/hecc/support/kb/using-gpg-to-encrypt-your-data_242.html) using GPG for file encryption, and this is easily installed on linux operating systems (if not already)

For Ubuntu/Debian

```bash
$ sudo apt install gnupg
```

For Redhat/CentOS/Fedora

```bash
$ sudo yum install gnupg
```

### OpenSSL

To use this encryption engine, you need `openssl` to be installed, this is already on linux systems, but on the Mac `libressl` is included but that does not support does not support `pbkdf2` or `iter`. Both these features [improve security](https://courses.csail.mit.edu/6.857/2018/project/Ainane-Barrett-Johnson-Vivar-OpenSSL.pdf) and should be used.

Encrypting data is touchy with `openssl` because the settings used to encrypt needs to same when decrypting, therefore you can't be encrypting data on different installations in different ways. Major changes to openssl could affect backup/restore operations.

On the MacOS you can install `openssl` simply like this

```bash
$ brew update
$ brew install openssl
```

If a dump is encrypted with `openssl` it will have the `enc` extension added.