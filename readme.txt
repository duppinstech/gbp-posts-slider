=== GBP Posts Slider ===
Contributors: yourname
Tags: google business profile, google my business, posts, slider, carousel
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your Google Business Profile posts in a beautiful responsive slider on any WordPress page or post.

== Description ==

**GBP Posts Slider** connects to your Google Business Profile via OAuth and syncs your latest posts into a private WordPress cache. Visitors see those posts rendered as a smooth, accessible carousel — without any slow external API calls on the front end.

**Key Features**

* One-click Google OAuth connection — no API keys to paste into the admin (when credentials are configured via `wp-config.php`)
* Posts cached as a custom post type for fast, reliable front-end display
* Automatic hourly sync via WP-Cron
* Responsive Swiper.js carousel (1 → 2 → 3 columns)
* Configurable via shortcode attributes
* Post images, dates, and call-to-action buttons
* CSRF-protected OAuth flow with state token validation
* Accessible markup with ARIA labels

**Shortcode**

`[gbp_posts_slider]`

Optional attributes:

* `limit` — Number of posts to show (default: `6`)
* `show_images` — Show post images: `true` / `false` (default: `true`)
* `show_date` — Show post date: `true` / `false` (default: `true`)
* `autoplay` — Enable carousel autoplay: `true` / `false` (default: `true`)
* `slides_per_view` — Slides visible on desktop, 1–6 (default: `3`)

Example:
`[gbp_posts_slider limit="4" autoplay="false" slides_per_view="2"]`

== Installation ==

= Quick Start (single-site / developer workflow) =

1. Upload the `gbp-posts-slider` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Follow the **Google Cloud Setup** steps below to create OAuth credentials.
4. Add your Client ID and Client Secret to `wp-config.php`:

   ```php
   define( 'GBP_CLIENT_ID',     'YOUR_CLIENT_ID.apps.googleusercontent.com' );
   define( 'GBP_CLIENT_SECRET', 'YOUR_CLIENT_SECRET' );
   ```

5. Go to **Settings → GBP Posts Slider** and click **Connect Google Business Profile**.
6. Complete the Google OAuth consent screen.
7. Enter your **Account ID** and **Location ID**, then save.
8. Click **Sync Now** to pull your posts.
9. Add `[gbp_posts_slider]` to any page.

= Without wp-config.php constants =

If you prefer to enter credentials in the admin (useful when you manage your own Google Cloud project), skip step 4 — the **Google API Credentials** section will appear in the settings page instead.

= Google Cloud Setup =

1. Go to [Google Cloud Console](https://console.cloud.google.com/) and create a new project.
2. Enable the **My Business Posting API** (search under *APIs & Services → Library*).
3. Go to **APIs & Services → OAuth consent screen** and configure your app.
   - For internal/single-site use: choose **External**, add your email as a test user.
   - For production/public use: complete Google's app verification process.
4. Go to **APIs & Services → Credentials → Create Credentials → OAuth client ID**.
   - Application type: **Web application**
   - Add the **Authorized redirect URI** shown in the plugin settings page.
5. Copy the **Client ID** and **Client Secret**.

== Frequently Asked Questions ==

= I get "Error 400: invalid_request" from Google =

This almost always means the **redirect URI** registered in Google Cloud Console doesn't exactly match the one the plugin uses. Copy the URI shown in **Settings → GBP Posts Slider → Google API Credentials** and paste it as an Authorized redirect URI in your Google Cloud OAuth client.

= The app says it doesn't comply with Google's OAuth 2.0 policy =

Your OAuth app is likely in **Testing** mode and your Google account hasn't been added as a test user. In Google Cloud Console go to **APIs & Services → OAuth consent screen → Test users** and add the Google account you're signing in with. For a production deployment you'll need to complete Google's app verification.

= Where do I find my Account ID and Location ID? =

Log in to [Google Business Profile](https://business.google.com/). The numeric IDs appear in the URL when viewing your profile: `business.google.com/dashboard/l/LOCATION_ID`.

Your Account ID is visible via the [Account Management API](https://developers.google.com/my-business/reference/accountmanagement/rest/v1/accounts/list) or in the URL path as well.

= Will posts be removed from my site if I delete them on Google? =

Yes. The next sync will detect removed posts and delete them from the local cache.

= Does this slow down my site? =

No. Posts are cached in a private custom post type and served from the local database — there are no Google API calls on the front end.

== Screenshots ==

1. Settings page — connection status and one-click Google connect.
2. Example slider on the front end.

== Changelog ==

= 1.0.0 =
* Initial release.
* OAuth 2.0 connection with CSRF state token protection.
* Custom post type cache for GBP posts.
* Configurable Swiper.js slider shortcode.
* Support for `GBP_CLIENT_ID` / `GBP_CLIENT_SECRET` wp-config.php constants.
* Automatic hourly sync via WP-Cron.
* v1 API with v4 fallback.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
