---
paths:
  - "gbp-posts-slider.php"
  - "uninstall.php"
  - "includes/**/*.php"
  - "assets/**/*.js"
---

# Testing standard

## Minimum (every change)
1. `php -l` passes on every changed PHP file (the PostToolUse hook does this).
2. Manual smoke test on a WordPress site with `WP_DEBUG` on:
   - The plugin activates without notices or errors.
   - Settings → GBP Posts Slider: save a client id and secret, connect Google, choose a location, and run Sync Now.
   - A page that contains `[gbp_posts_slider]` renders the slider from synced posts, including the empty state.
3. If you touched the updater, test the Check Again flow against a draft release.

## Automated tests
No suite yet; see PLAN.md.
- Location, once a suite exists: `tests/unit/` (Brain Monkey) and `tests/integration/` (wp-phpunit). Naming: `Test_<Class_Name>.php`, methods `test_<behavior>_<condition>`.
- Every bug fix ships with a regression test that fails before the fix. Unit tests never hit the network.
- CI lints PHP on 7.4 (`.github/workflows/ci.yml`). It does not run a test suite.

## Priority test targets
- `GBP_Posts_Slider` OAuth callback, token exchange, and `sync_posts`.
- `GBP_Posts_Slider` AJAX handlers: `ajax_disconnect`, `ajax_fetch_accounts`, `ajax_fetch_locations`.
- `GBP_Posts_Slider_GitHub_Updater`: release fetch, package URL check, and the update transient.
- `GBP_Posts_Slider_Plugin_Info`: Plugins screen links and the details modal.
- `uninstall.php`: deletes `gbp_posts_slider_github_release` and nothing else.
