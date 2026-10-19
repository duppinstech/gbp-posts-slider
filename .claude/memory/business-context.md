# Business context

_Committed. If the repo is public, keep only public-safe facts here: published tiers and list prices, positioning, cost drivers. Private numbers go in the "Business (private)" section of the gitignored `CLAUDE.local.md`._

Read by `finance-reviewer`, `marketing-reviewer`, `legal-reviewer` and `product-reviewer`. Items marked _TBD_ are open questions for Odell. Agents must not fill them in by guessing.

## Product and positioning
- **Product:** Google Business Posts Slider, by Duppins Technology (https://duppinstech.com). Short name: GBP Posts Slider.
- **Audience:** A WordPress administrator publishing one business's Google Business Profile posts on that site (`INTENT.md`).
- **Positioning:** Connect Google once, choose the location, and show the synced posts with `[gbp_posts_slider]`. A page view reads the local copy and does not call Google (`INTENT.md`, `readme.txt`).
- **Built for (client):** Product work. Not work-for-hire for a single client (`OWNERSHIP.md`).

## Tiers
| Tier | What's included | Price | Distribution |
|---|---|---|---|
| Core | Connect one Google Business Profile, hourly sync, and `[gbp_posts_slider]`. | Free | GitHub releases through the plugin updater |

## Cost drivers
- Google API calls use the client id and secret the site owner saves on Settings → GBP Posts Slider. Duppins Technology does not pay for that usage. No API keys are hardcoded in the plugin.
- This repository is the plugin. It is not a hosted service (`INTENT.md`).
- Support time: _TBD (Odell)_
