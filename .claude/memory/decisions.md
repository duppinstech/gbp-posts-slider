# Architectural decisions

Append-only log. Format: `## YYYY-MM-DD — Title`, then **Context** / **Decision** / **Consequences**. Owner waivers of High findings are recorded here too.

## 2026-05-30 — Cache Google posts on the site

**Context:** A page view should not call Google. Initial release, commit `68a690d`. `CHANGELOG.md` dates 1.0.0 to this day.

**Decision:** Sync posts on an hourly cron into a private `gbp_post` type, and render `[gbp_posts_slider]` from that copy.

**Consequences:** The front end reads WordPress. Disconnecting leaves the copied posts in place.

## 2026-05-30 — Google credentials stay out of the repository

**Context:** `PLAN.md` excludes storing Google client secrets in git.

**Decision:** The site owner enters the client id and secret on Settings → GBP Posts Slider. Those settings default to empty strings. Optional `GBP_CLIENT_ID` and `GBP_CLIENT_SECRET` constants in `wp-config.php` override the fields. The repository only contains placeholders in comments and docs.

**Consequences:** Each site uses its own Google Cloud project. Duppins Technology does not pay for that API usage.

## 2026-10-17 — Choose the location in wp-admin

**Context:** Commit `c1b2814`, "Add an admin picker for Google Business Profile locations."

**Decision:** After connecting, Settings → GBP Posts Slider loads Google accounts and locations, and the administrator picks one.

**Consequences:** The chosen account id and location id are stored in `gbp_posts_slider_settings`.

## 2026-10-18 — Ship updates from GitHub

**Context:** Commit `b7c35cf`. The plugin header sets `Update URI` to this repository.

**Decision:** `GBP_Posts_Slider_GitHub_Updater` reads GitHub releases for `duppinstech/gbp-posts-slider` and offers them on Dashboard → Updates. WordPress.org is not the update source.

**Consequences:** A `v*` tag on main is how a release is packaged. No git tag exists yet.
