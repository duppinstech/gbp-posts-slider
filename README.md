# GBP Posts Slider

A WordPress plugin that pulls your **Google Business Profile posts** and displays them in a responsive, accessible slider on your website.

Posts are cached locally as a custom post type so the front end never makes a live API call — fast and reliable.

---

## Features

- **One-click Google connect** — define credentials in `wp-config.php` and end-users just click a button
- Posts cached as a private CPT for front-end performance
- Automatic hourly sync via WP-Cron + manual sync button
- Responsive Swiper.js carousel (1 → 2 → 3 columns)
- Post images, dates, and call-to-action buttons
- CSRF-protected OAuth flow (state token)
- Accessible markup with ARIA labels and `loading="lazy"` images
- Shortcode with configurable attributes

---

## Requirements

| Requirement | Version |
|---|---|
| WordPress | 6.0+ |
| PHP | 7.4+ |

---

## Installation

### 1. Google Cloud Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/) and create a project.
2. Enable **My Business Posting API** under *APIs & Services → Library*.
3. Configure **OAuth consent screen** (External; add your account as a test user for internal use).
4. Create an **OAuth 2.0 Client ID** (Web application type).
5. Add the plugin's redirect URI as an **Authorized redirect URI** — you'll find the exact URI in the plugin settings page.

### 2. Plugin Setup

**Option A — credentials in `wp-config.php`** (recommended for single-site / managed installs)

End-users see only a "Connect" button — no credential fields exposed:

```php
// wp-config.php
define( 'GBP_CLIENT_ID',     'YOUR_CLIENT_ID.apps.googleusercontent.com' );
define( 'GBP_CLIENT_SECRET', 'YOUR_CLIENT_SECRET' );
```

**Option B — enter credentials in the admin**

Leave the constants undefined; the credential fields will appear in **Settings → GBP Posts Slider**.

### 3. Connect & Configure

1. Go to **Settings → GBP Posts Slider**.
2. Click **Connect Google Business Profile** and complete the consent screen.
3. Enter your **Account ID** and **Location ID** (numeric only, no prefix).
4. Click **Sync Now** to pull your posts immediately.
5. Add the shortcode to any page.

---

## Shortcode

```
[gbp_posts_slider]
```

| Attribute | Default | Description |
|---|---|---|
| `limit` | `6` | Number of posts to display |
| `show_images` | `true` | Show post images |
| `show_date` | `true` | Show post date |
| `autoplay` | `true` | Enable carousel autoplay |
| `slides_per_view` | `3` | Max slides on desktop (1–6) |

**Example:**

```
[gbp_posts_slider limit="4" autoplay="false" slides_per_view="2"]
```

---

## Troubleshooting

### Error 400: invalid_request

The redirect URI in your Google Cloud OAuth client does not exactly match the one the plugin sends. Copy the URI shown in **Settings → GBP Posts Slider** and add it as an Authorized redirect URI in Google Cloud Console.

### "This app doesn't comply with Google's OAuth 2.0 policy"

Your app is in **Testing** mode. In Google Cloud Console go to **APIs & Services → OAuth consent screen → Test users** and add the Google account you're using to sign in.

For a production/public deployment you must complete [Google's OAuth verification process](https://support.google.com/cloud/answer/9110914).

### Posts not showing

1. Verify you're connected (Settings → GBP Posts Slider shows "Connected").
2. Confirm Account ID and Location ID are correct (numeric only).
3. Click **Sync Now** and check the count.
4. Check that WP-Cron is running on your server (`define('DISABLE_WP_CRON', true)` would break automatic sync).

---

## Hooks & Filters

| Hook | Type | Description |
|---|---|---|
| `gbp_posts_slider_refresh` | Action (cron) | Fired hourly to sync posts |

More filters will be added in future releases.

---

## License

[GPL-2.0-or-later](LICENSE)
