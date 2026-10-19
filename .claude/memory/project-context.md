# Project context

_Shared, committed memory. Keep it factual and current. If the repo is public, so is this file._

- **Product:** Google Business Posts Slider (short name GBP Posts Slider), a WordPress plugin. Owner: Odell Duppins Jr, doing business as Duppins Technology. Author header: Duppins Technology.
- **Repo:** https://github.com/duppinstech/gbp-posts-slider (public)
- **Current release:** No git tag. Header version 1.0.0. `CHANGELOG.md` dates 1.0.0 to 2026-05-30. `OWNERSHIP.md` states GPL-2.0-or-later from version 1.0.3.
- **Distribution:** GitHub updater in `includes/class-gbp-posts-slider-github-updater.php`. The `Update URI` header is this repository. `readme.txt` says new releases appear under Dashboard → Updates.
- **Key dependencies:** No Composer or npm packages. Swiper 11 is loaded from jsDelivr (`https://cdn.jsdelivr.net/npm/swiper@11/`, package 11.2.10 when checked). License: MIT. Copyright (c) 2019 Vladimir Kharlampidi. It is not bundled in this repo.
- **Integrations:** Google OAuth (`accounts.google.com`, `oauth2.googleapis.com`), My Business Account Management, Business Information, and My Business Posting v1 with a v4 fallback. GitHub releases API for updates.
- **CI:** `php -l` on PHP 7.4 in `.github/workflows/ci.yml`. No test suite. Releases are built by `release.yml` on `v*` tags.

## Review watchlist
Known items each department re-checks on every review until fixed. Remove lines when they're resolved.
- **Security:** none yet
- **Legal:** none yet
- **Finance:** none yet
- **Product:** none yet
- **Marketing:** none yet
- **Support:** `README.md` still says to type an Account ID and Location ID. The settings screen and `readme.txt` use the location picker added in `c1b2814`.
- **Ops:** none yet
- **UX:** none yet
