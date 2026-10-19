# Agent notes

WordPress plugin. No Composer, npm, Makefile, or test runner. Do not add a dependency manifest unless the task asks for one.

## Commands

Install: copy this directory to `wp-content/plugins/gbp-posts-slider` and activate it in WordPress. There is no package-install command.

Lint:

```
php -l gbp-posts-slider.php
php -l uninstall.php
php -l includes/class-gbp-posts-slider-github-updater.php
php -l includes/class-gbp-posts-slider-plugin-info.php
```

Test: no test command. Do not add a test runner as a drive-by change.

Build, from the repository root in bash (this is what the release workflow runs):

```
bash bin/build-zip.sh
```

The zip is `gbp-posts-slider.zip` at the repository root, built from tracked files. It leaves out `.github/`, `.claude/`, `tests/`, `bin/`, and the agent docs. Do not commit the zip. `build/` and `*.zip` are gitignored.

## Conventions

- PHP 7.4+ and WordPress 6.0+. Indent PHP with 4 spaces.
- Text files use LF. See `.gitattributes`.
- Branches look like `type/short-description`. Commits follow Conventional Commits. See `CONTRIBUTING.md`.
- Google credentials are the PHP constants `GBP_CLIENT_ID` and `GBP_CLIENT_SECRET`, not environment variables.
- User-facing strings go through WordPress translation functions.

## Do not touch

- Do not read or commit `.env`, `.env.*`, `CLAUDE.local.md`, or `.claude/settings.local.json`.
- Do not commit `gbp-posts-slider.zip`, `vendor/`, or `node_modules/`.
- Do not force-push, `git reset --hard`, or `git clean`.
- Do not edit `LICENSE`.
- Do not rewrite `CHANGELOG.md` except when the task is a release.
