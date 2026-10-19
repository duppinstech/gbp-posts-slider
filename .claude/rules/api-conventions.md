---
paths:
  - "gbp-posts-slider.php"
  - "includes/**/*.php"
---

# API conventions (REST, AJAX, admin-post, hooks)

## REST
- Register in `rest_api_init` under a `gbp-posts-slider/v1`-style namespace. Every route has a real `permission_callback`; never `__return_true` on routes that write.
- Validate and sanitize every argument with `args` → `sanitize_callback` / `validate_callback`. Return `WP_REST_Response` or `WP_Error`; never `echo`.

## admin-ajax
- Actions are prefixed: `wp_ajax_gbp_*`.
- First lines of the handler: `check_ajax_referer()`, then the capability check. Respond with `wp_send_json_success()` / `wp_send_json_error()`.

## admin-post
- `admin_post_gbp_*` handlers call `check_admin_referer()` **and** `current_user_can()` before reading `$_POST` or `$_FILES`, and finish with `wp_safe_redirect()` then `exit`.

## Outbound HTTP
- `wp_remote_get()` / `wp_remote_post()` with a timeout; cache responses in transients where sensible. Keys come from settings; never log them.

## Hooks we expose
- New hooks are prefixed `gbp_`, documented with a docblock, and listed here. Renaming or removing a public hook is a breaking change; deprecate first.
- `gbp_posts_slider_github_updater_config` — filters the GitHub updater configuration before it is stored.
- `gbp_posts_slider_github_release_http_args` — filters the `wp_remote_get()` args for the GitHub latest-release request.
- The plugin does not call `do_action`.
