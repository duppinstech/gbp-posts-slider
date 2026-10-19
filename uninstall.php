<?php
/**
 * Removes the GitHub updater cache when the plugin is deleted.
 *
 * @package GBP_Posts_Slider
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_site_option( 'gbp_posts_slider_github_release' );
