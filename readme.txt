=== GBP Posts Slider ===
Contributors: duppinstech
Tags: google business profile, google my business, posts, slider, carousel
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show your Google Business Profile posts in a slider on any WordPress page.

== Description ==

GBP Posts Slider keeps a copy of your Google Business Profile posts on your site and shows them in a slider. A page view reads that copy. It does not call Google.

Connect the Google account that manages the business, choose the location, and place the shortcode on a page. Posts sync every hour, and you can sync immediately from the settings screen. When a post is removed on Google, the next sync removes it here.

= Features =

* Connect Google from Settings → GBP Posts Slider
* Choose the business location after you connect
* Hourly sync, plus Sync Now
* Post photos, dates, and call-to-action buttons
* One post on a phone, up to two on a tablet, and the number you choose on a desktop
* Autoplay that pauses while the pointer is over the slider

= Shortcode =

`[gbp_posts_slider]`

* `limit` — number of posts (default `6`)
* `show_images` — `true` or `false` (default `true`)
* `show_date` — `true` or `false` (default `true`)
* `autoplay` — `true` or `false` (default `true`)
* `slides_per_view` — posts visible on a desktop, from 1 to 6 (default `3`)

Example: `[gbp_posts_slider limit="4" autoplay="false" slides_per_view="2"]`

= Support =

Support: [support@duppinstech.com](mailto:support@duppinstech.com). Made by [Duppins Technology](https://duppinstech.com).

== Installation ==

1. Download `gbp-posts-slider.zip` from the latest release.
2. In wp-admin, go to Plugins → Add New → Upload Plugin, choose the zip, and click Install Now.
3. Activate GBP Posts Slider.
4. In [Google Cloud Console](https://console.cloud.google.com/), create a project, enable the My Business Posting API, and create an OAuth client ID of type Web application.
5. Open Settings → GBP Posts Slider, copy the redirect address, and add it as an Authorized redirect URI on that OAuth client.
6. Enter the Client ID and Client Secret on the settings screen, then save. If `GBP_CLIENT_ID` and `GBP_CLIENT_SECRET` are already set in `wp-config.php`, the settings screen hides those fields.
7. Click Connect Google Business Profile and finish the Google consent screen.
8. Choose the account and the business location, then save.
9. Click Sync Now.
10. Add `[gbp_posts_slider]` to a page.

= Google Cloud notes =

For a site only you use, set the OAuth consent screen to External and add your Google account as a test user. A public site needs Google's app verification before other people can connect.

= Updates =

New releases appear under Dashboard → Updates.

== Frequently Asked Questions ==

= I get "Error 400: invalid_request" from Google =

The redirect address in Google Cloud Console does not match the one this plugin uses. Copy the address shown under Settings → GBP Posts Slider → Google API Credentials, and paste it as an Authorized redirect URI on your OAuth client.

= The app says it doesn't comply with Google's OAuth 2.0 policy =

The OAuth app is in Testing mode, and your Google account is not a test user. In Google Cloud Console go to APIs & Services → OAuth consent screen → Test users, and add the Google account you use to sign in. A public site needs Google's app verification.

= Where do I choose the business? =

After you connect, Settings → GBP Posts Slider asks you to pick the Google account and then the business location. Save that choice before you sync.

= Will posts be removed from my site if I delete them on Google? =

Yes. The next sync removes posts that are no longer on Google.

= Does this slow down my site? =

No. Posts are stored on your site. A page view does not call Google.

== Screenshots ==

1. Settings → GBP Posts Slider before Google is connected.
2. The slider on a page, with post photos, dates, and buttons.

== Changelog ==

= 1.0.0 =

* Initial release.
* Connect Google Business Profile from the settings screen. Each sign-in request can be used only once.
* Google credentials can be set in wp-config.php so the settings screen does not show them.
* Posts are stored on the site. A page view does not call Google.
* Posts sync automatically every hour.
* Sync Now on the settings screen pulls posts immediately.
* Posts deleted on Google are removed from the site on the next sync.
* Shortcode `[gbp_posts_slider]` with `limit`, `show_images`, `show_date`, `autoplay`, and `slides_per_view`.
* The slider shows one post on a phone, up to two on a tablet, and the chosen number on a desktop.
* Autoplay pauses while the pointer is over the slider.
* Post photos load as they come near the screen.
* A call-to-action button is shown when the Google post has one.
* Disconnect asks for confirmation before it runs.
* The settings screen can copy the Google redirect address.
* Previous and next controls have screen-reader labels.
* The Google connection is reused for a short time so the plugin does not request a new one on every sync.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
