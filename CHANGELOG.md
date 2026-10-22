# Changelog

## 0.1.1 (2026-10-22)

- Fixed: `bin/php/createoutput.php` no longer signs in as the user `admin`. It
  renders editions as the user named by the new setting
  `[NewsletterSettings] OutputUser` (a login or a user id) and stops with an
  error when that is empty or names no enabled user. Set it when upgrading.
- Fixed: `ConfirmationTimeOut` is parsed instead of evaluated: a whole number
  of seconds or a product of whole numbers such as `6*24*60*60`. Any other
  value is refused with an error and no pending user is removed.
- Fixed: the command that renders an edition quotes each of its arguments, and
  a failed rendering is reported instead of queuing an empty newsletter.

## 0.1.0 (2026-10-22)

First tagged release, at the version `ezinfo.php` has always stated.

- Updated: The admin tab shows an icon: `[Topmenu_ownewsletter]` in
  `settings/menu.ini.append.php` names `Icon=envelope`, which the admin4 design
  draws before the tab's name.
- Added: `composer.json` for the package `se7enxweb/ownewsletter`, installed as
  `extension/ownewsletter` by the Exponential legacy installer; requires PHP 8.0
  or later.
- Added: `extension.xml` with the release metadata and the `ezjscore`
  dependency.
- Updated: `ezinfo.php` states the version, copyright, license and website; the
  stale kernel version entry and the unfilled build placeholders are gone.
- Updated: `README.rst` becomes `README.md`, a complete guide with installation,
  the content structure, usage, fetch functions and operators with examples,
  configuration, permissions, cron jobs, known limits and troubleshooting.

Earlier changes, made by Open Wide from 2013 to 2015, are in the git history.
