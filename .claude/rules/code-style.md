---
paths:
  - "gbp-posts-slider.php"
  - "uninstall.php"
  - "includes/**/*.php"
  - "assets/**/*.js"
  - "assets/**/*.css"
---

# Code style

## PHP
- Target **PHP 7.4**. For PHP 7.4 that means no `match`, enums, `readonly`, constructor promotion, `?->`, union types, or `str_contains` without a polyfill.
- WordPress Coding Standards: spaces inside parentheses, Yoda conditions for literals; match the file's array syntax and indentation. Don't reformat lines you didn't change.
- Prefix every global function, option, meta key, transient, hook and AJAX action with `gbp_`. Text domain `gbp-posts-slider` on every translatable string.
- Docblock on every public method and every hook the plugin fires.
- No namespace and no autoloader. `GBP_Posts_Slider` lives in `gbp-posts-slider.php`. `GBP_Posts_Slider_GitHub_Updater` and `GBP_Posts_Slider_Plugin_Info` live in `includes/`, loaded with `require_once` and started with `::boot()`.
- No field library and no global functions. Settings are the `gbp_posts_slider_settings` option.

## JavaScript
- Data from PHP arrives through `wp_localize_script` or `wp_add_inline_script`; never inline secrets.
- `assets/gbp-slider.js` is vanilla and calls the Swiper global. `assets/gbp-admin.js` is a jQuery IIFE and reads `gbpAdmin` from `wp_localize_script`. There is no build step.

## CSS
- Scope front-end CSS under a plugin wrapper or body class so it doesn't leak into themes or wp-admin.
- Don't edit vendored libraries (`*.min.*`, third-party bundles).

## Comments
- Explain *why*, not *what*.
