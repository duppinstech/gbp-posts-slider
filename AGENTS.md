# Agent notes

WordPress plugin. No Composer, npm, Makefile, or test runner. Do not add a dependency manifest unless the task asks for one.

## Plugin facts

- **Product:** Google Business Posts Slider. Short name in the plugin header and on Settings: GBP Posts Slider.
- **Main file:** `gbp-posts-slider.php`
- **Text domain:** `gbp-posts-slider`
- **Prefix:** `gbp_` on AJAX actions and the `gbp_post` post type. The updater is configured with `gbp_posts_slider`.
- **Floor:** WordPress 6.0+ and PHP 7.4+. `readme.txt` is tested up to 6.7.
- **Author header:** Duppins Technology. See `OWNERSHIP.md`.
- **License:** GPL-2.0-or-later from version 1.0.3 (`OWNERSHIP.md`). The header version is 1.0.0. There is no git tag.
- **Price:** Free. Duppins Technology does not pay for Google API usage.
- **Credentials:** The site owner enters the client id and secret on Settings → GBP Posts Slider. No API keys are stored in this repository. Optional `GBP_CLIENT_ID` and `GBP_CLIENT_SECRET` constants in `wp-config.php` override the settings fields.
- **Updater:** `includes/class-gbp-posts-slider-github-updater.php`, repo `duppinstech/gbp-posts-slider`. The `Update URI` header points at that repository.
- **Repo:** https://github.com/duppinstech/gbp-posts-slider (public)

## What this is

GBP Posts Slider publishes a business's Google Business Profile posts on a WordPress site.

An administrator connects Google once and chooses the location. The plugin syncs posts on a schedule and stores them locally, so a page view does not call Google. The site renders them with the `[gbp_posts_slider]` shortcode.

This repository is the plugin. It is not a hosted service, and Google credentials are not stored in the repo. They belong on the settings screen, or in `wp-config.php` as the constants above.

## Architecture

- `gbp-posts-slider.php` — `GBP_Posts_Slider`: OAuth, settings, hourly sync into the private `gbp_post` type, and `[gbp_posts_slider]`.
- `includes/class-gbp-posts-slider-github-updater.php` — checks GitHub releases and offers them on Dashboard → Updates.
- `includes/class-gbp-posts-slider-plugin-info.php` — Plugins screen details, row links, and the "View details" modal.
- `assets/` — admin and front-end CSS and JS. The slider script calls Swiper 11, loaded from jsDelivr (MIT, not bundled).
- `assets/wporg/` — listing icon, banners, and screenshots.
- `uninstall.php` — deletes the `gbp_posts_slider_github_release` option when the plugin is deleted.
- `bin/` — `build-zip.sh` and `release-notes.sh`. Not shipped in the zip.

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

## Hard rules

- Do not read or commit `.env`, `.env.*`, `CLAUDE.local.md`, or `.claude/settings.local.json`.
- Do not commit `gbp-posts-slider.zip`, `vendor/`, or `node_modules/`.
- Do not force-push, `git reset --hard`, or `git clean`.
- Do not edit `LICENSE`.
- Do not rewrite `CHANGELOG.md` except when the task is a release.
- Do not store Google client secrets in git. See `PLAN.md`.

## Agents

| Agent | When |
|---|---|
| `security-reviewer` | AJAX, the updater, or code that reads request data |
| `legal-reviewer` | Dependencies, bundled assets, privacy, or license text |
| `finance-reviewer` | Paid APIs, tiers, or anything a customer might pay for |
| `product-reviewer` | Scope against `INTENT.md` and `PLAN.md` |
| `marketing-reviewer` | Public copy, naming, screenshots, release highlights |
| `support-docs-reviewer` | README, `readme.txt`, changelog, admin messages, i18n |
| `release-ops-reviewer` | Compatibility, zip contents, uninstall, rollback |
| `ux-reviewer` | Front-end output or admin screens |
| `test-writer` | After a feature or fix, or to bootstrap the harness |
| `research` | WordPress, library, or third-party API behavior |
