# Agent notes

WordPress plugin. No Composer, npm, Makefile, or test runner. Do not add a dependency manifest unless the task asks for one.

## Commands

Install: copy this directory to `wp-content/plugins/gbp-posts-slider` and activate it in WordPress. There is no package-install command.

Lint:

```
php -l gbp-posts-slider.php
```

Test: no test command. Do not add a test runner as a drive-by change.

Build, from the repository root in bash (this is what the release workflow runs):

```
mkdir -p dist/gbp-posts-slider
cp gbp-posts-slider.php readme.txt LICENSE README.md dist/gbp-posts-slider/
cp -r assets dist/gbp-posts-slider/
(cd dist && zip -r gbp-posts-slider.zip gbp-posts-slider)
```

Do not commit the zip. `dist/` is gitignored.

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
