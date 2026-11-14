# ZPanel

A small, fast control panel for one Linux server that I build in my free time: websites with PHP and free SSL, a file manager,
databases, scheduled jobs, backups, the firewall and SSH, server status and two-step sign-in. Plain PHP back end, React web app,
every system command run as an argument list (no shell strings).

Version 2.3.0. Free to use - see LICENSE for the short terms. Ideas and pull requests are welcome.

## Install (fresh Ubuntu 22.04 / 24.04 or Debian 12, as root)

    bash install/install.sh            # optional: --port 9443  --no-firewall

It prints the address, the user `admin` and a one-time password. Change the password and switch on two-step sign-in first.

## Develop

- `src/` back end, `public/index.php` front controller, `web/` React source (`cd web && npm ci && npm run build` writes `public/app`).
- Check: `php tools/community-smoke.php .` (dry run, touches nothing on the machine).

## Updates

`git pull`, then run the installer again with the same options.
