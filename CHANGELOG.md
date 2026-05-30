# Changelog

All notable changes to GBP Posts Slider are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] - 2026-05-30

### Added
- Initial release.
- OAuth 2.0 connection to Google Business Profile with CSRF state token protection.
- Support for GBP_CLIENT_ID and GBP_CLIENT_SECRET constants in wp-config.php to hide credentials from end users.
- Private custom post type (gbp_post) as a local cache for synced posts — no live API calls on the front end.
- Automatic hourly sync via WP-Cron.
- Manual sync button in the admin settings page.
- My Business Posting API v1 with v4 fallback.
- Configurable [gbp_posts_slider] shortcode with limit, show_images, show_date, utoplay, and slides_per_view attributes.
- Responsive Swiper.js carousel (1 / 2 / 3 columns at mobile / tablet / desktop).
- Autoplay pause on hover and pauseOnMouseEnter.
- Post images with loading=lazy and decoding=async.
- CTA (call-to-action) button support.
- Disconnect button with confirmation dialog.
- Copy-to-clipboard redirect URI helper in the admin.
- Accessible markup: ARIA labels, region role, navigation button labels.
- Pruning of CPT entries when posts are deleted from Google Business Profile.
- Access token short-term caching (transient) to minimise token refresh requests.
