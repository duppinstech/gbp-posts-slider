<?php
/**
 * Plugin Name:       GBP Posts Slider
 * Plugin URI:        https://github.com/duppinstech/gbp-posts-slider
 * Description:       Pulls Google Business Profile posts and displays them in a responsive slider on your WordPress website.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Duppins Technology
 * Author URI:        https://duppinstech.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gbp-posts-slider
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GBP_SLIDER_VERSION', '1.0.0' );
define( 'GBP_SLIDER_FILE',    __FILE__ );
define( 'GBP_SLIDER_DIR',     plugin_dir_path( __FILE__ ) );
define( 'GBP_SLIDER_URL',     plugin_dir_url( __FILE__ ) );

// ---------------------------------------------------------------------------
// Developer convenience: define these in wp-config.php to hide credential
// fields from the admin UI entirely (end-users just click "Connect").
//
//   define( 'GBP_CLIENT_ID',     'your-client-id.apps.googleusercontent.com' );
//   define( 'GBP_CLIENT_SECRET', 'your-client-secret' );
// ---------------------------------------------------------------------------

class GBP_Posts_Slider {

    const OPTION_KEY      = 'gbp_posts_slider_settings';
    const CRON_HOOK       = 'gbp_posts_slider_refresh';
    const CPT_SLUG        = 'gbp_post';
    const OAUTH_STATE_KEY = 'gbp_oauth_state';

    /* ------------------------------------------------------------------
     *  Boot
     * ------------------------------------------------------------------ */

    public function __construct() {
        add_action( 'init',               [ $this, 'register_cpt' ] );
        add_action( 'admin_menu',         [ $this, 'admin_menu' ] );
        add_action( 'admin_init',         [ $this, 'handle_oauth_callback' ] );
        add_action( 'admin_notices',      [ $this, 'admin_notices' ] );
        add_action( self::CRON_HOOK,      [ $this, 'sync_posts' ] );
        add_action( 'wp_ajax_gbp_disconnect', [ $this, 'ajax_disconnect' ] );

        add_shortcode( 'gbp_posts_slider', [ $this, 'shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        register_activation_hook(   GBP_SLIDER_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( GBP_SLIDER_FILE, [ $this, 'deactivate' ] );
    }

    public function activate() {
        $this->register_cpt();
        flush_rewrite_rules();
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'hourly', self::CRON_HOOK );
        }
    }

    public function deactivate() {
        wp_clear_scheduled_hook( self::CRON_HOOK );
        flush_rewrite_rules();
    }

    /* ------------------------------------------------------------------
     *  Settings helpers
     * ------------------------------------------------------------------ */

    private function settings() {
        return wp_parse_args( get_option( self::OPTION_KEY, [] ), [
            'refresh_token' => '',
            'account_id'    => '',
            'location_id'   => '',
            'client_id'     => '',
            'client_secret' => '',
        ] );
    }

    private function save_settings( array $settings ) {
        update_option( self::OPTION_KEY, $settings, false );
    }

    /** Client ID: wp-config.php constant takes priority over DB value. */
    private function client_id() {
        return defined( 'GBP_CLIENT_ID' ) ? GBP_CLIENT_ID : $this->settings()['client_id'];
    }

    /** Client secret: wp-config.php constant takes priority over DB value. */
    private function client_secret() {
        return defined( 'GBP_CLIENT_SECRET' ) ? GBP_CLIENT_SECRET : $this->settings()['client_secret'];
    }

    /**
     * Returns true when credentials are defined as constants (e.g. in wp-config.php).
     * When true, credential input fields are hidden from the admin — end-users
     * just click "Connect Google Business Profile".
     */
    private function credentials_are_built_in() {
        return defined( 'GBP_CLIENT_ID' ) && defined( 'GBP_CLIENT_SECRET' );
    }

    /* ------------------------------------------------------------------
     *  Custom Post Type — used as a local cache for GBP posts
     * ------------------------------------------------------------------ */

    public function register_cpt() {
        register_post_type( self::CPT_SLUG, [
            'label'            => __( 'GBP Posts', 'gbp-posts-slider' ),
            'public'           => false,
            'show_ui'          => false,
            'show_in_menu'     => false,
            'rewrite'          => false,
            'supports'         => [ 'title', 'editor', 'custom-fields' ],
            'delete_with_user' => false,
        ] );
    }

    /* ------------------------------------------------------------------
     *  Admin menu
     * ------------------------------------------------------------------ */

    public function admin_menu() {
        add_options_page(
            __( 'GBP Posts Slider', 'gbp-posts-slider' ),
            __( 'GBP Posts Slider', 'gbp-posts-slider' ),
            'manage_options',
            'gbp-posts-slider',
            [ $this, 'settings_page' ]
        );
    }

    public function admin_notices() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) return;

        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'settings_page_gbp-posts-slider' ) return;

        if ( isset( $_GET['connected'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>'
                . esc_html__( 'Successfully connected to Google Business Profile!', 'gbp-posts-slider' )
                . '</p></div>';
        }

        if ( isset( $_GET['disconnected'] ) ) {
            echo '<div class="notice notice-info is-dismissible"><p>'
                . esc_html__( 'Disconnected from Google Business Profile.', 'gbp-posts-slider' )
                . '</p></div>';
        }

        if ( isset( $_GET['oauth_error'] ) ) {
            $msg = sanitize_text_field( urldecode( $_GET['oauth_error'] ) );
            echo '<div class="notice notice-error is-dismissible"><p>'
                . sprintf( esc_html__( 'Google OAuth error: %s', 'gbp-posts-slider' ), '<strong>' . esc_html( $msg ) . '</strong>' )
                . '</p></div>';
        }
    }

    /* ------------------------------------------------------------------
     *  Settings page
     * ------------------------------------------------------------------ */

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $settings     = $this->settings();
        $is_connected = ! empty( $settings['refresh_token'] );

        if ( isset( $_POST['gbp_save_settings'] ) ) {
            check_admin_referer( 'gbp_save_settings' );

            if ( ! $this->credentials_are_built_in() ) {
                $settings['client_id']     = sanitize_text_field( $_POST['client_id'] ?? '' );
                $settings['client_secret'] = sanitize_text_field( $_POST['client_secret'] ?? '' );
            }
            $settings['account_id']  = sanitize_text_field( $_POST['account_id'] ?? '' );
            $settings['location_id'] = sanitize_text_field( $_POST['location_id'] ?? '' );

            $this->save_settings( $settings );
            wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&saved=1' ) );
            exit;
        }

        if ( isset( $_POST['gbp_sync_now'] ) ) {
            check_admin_referer( 'gbp_sync_now' );
            $count = $this->sync_posts();
            wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&synced=' . (int) $count ) );
            exit;
        }

        $auth_url     = $this->get_auth_url();
        $synced_count = $this->get_synced_count();
        $redirect_uri = $this->redirect_uri();

        ?>
        <div class="wrap gbps-wrap">
            <h1 class="gbps-page-title">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="#4285F4"/>
                    <path d="M17.6 12.2c0-.6-.1-1.2-.2-1.8H12v3.4h3.1c-.1.8-.6 1.5-1.3 2v1.6h2.1c1.2-1.1 1.9-2.8 1.9-4.8-.1-.1-.2-.3-.2-.4z" fill="#fff"/>
                    <path d="M12 18c1.6 0 2.9-.5 3.9-1.4l-2.1-1.6c-.5.4-1.1.6-1.8.6-1.4 0-2.6-.9-3-2.2H6.8v1.7C7.8 16.9 9.8 18 12 18z" fill="#fff"/>
                    <path d="M9 13.4c-.1-.4-.2-.8-.2-1.4s.1-1 .2-1.4V9H6.8C6.3 10 6 11 6 12s.3 2 .8 2.9L9 13.4z" fill="#fff"/>
                    <path d="M12 8.6c.8 0 1.5.3 2.1.8l1.6-1.6C14.8 6.8 13.5 6.2 12 6.2c-2.2 0-4.2 1.1-5.2 2.9L9 10.8c.4-1.3 1.6-2.2 3-2.2z" fill="#fff"/>
                </svg>
                <?php esc_html_e( 'GBP Posts Slider', 'gbp-posts-slider' ); ?>
            </h1>

            <?php if ( isset( $_GET['saved'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'gbp-posts-slider' ); ?></p></div>
            <?php endif; ?>

            <?php if ( isset( $_GET['synced'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>
                    <?php printf(
                        esc_html__( '%d posts synced from Google Business Profile.', 'gbp-posts-slider' ),
                        (int) $_GET['synced']
                    ); ?>
                </p></div>
            <?php endif; ?>

            <div class="gbps-grid">

                <!-- ── Connection Card ── -->
                <div class="gbps-card gbps-card--full">
                    <h2><?php esc_html_e( 'Google Connection', 'gbp-posts-slider' ); ?></h2>

                    <?php if ( $is_connected ) : ?>
                        <p class="gbps-status gbps-status--ok">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e( 'Connected to Google Business Profile', 'gbp-posts-slider' ); ?>
                        </p>
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=gbp_disconnect' ), 'gbp_disconnect' ) ); ?>"
                           class="button gbps-disconnect-btn"
                           onclick="return confirm('<?php esc_attr_e( 'Disconnect from Google? Your synced posts will remain but will stop being updated.', 'gbp-posts-slider' ); ?>')">
                            <?php esc_html_e( 'Disconnect', 'gbp-posts-slider' ); ?>
                        </a>
                    <?php else : ?>
                        <p class="gbps-status gbps-status--warn">
                            <span class="dashicons dashicons-warning"></span>
                            <?php esc_html_e( 'Not connected', 'gbp-posts-slider' ); ?>
                        </p>

                        <?php if ( $auth_url !== '#' ) : ?>
                            <a class="button button-primary gbps-connect-btn" href="<?php echo esc_url( $auth_url ); ?>">
                                <?php esc_html_e( 'Connect Google Business Profile', 'gbp-posts-slider' ); ?>
                            </a>
                        <?php else : ?>
                            <p class="description">
                                <?php esc_html_e( 'Enter your Google API credentials below, save, then return here to connect.', 'gbp-posts-slider' ); ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- ── Credentials Card (hidden when constants are defined) ── -->
                <?php if ( ! $this->credentials_are_built_in() ) : ?>
                <div class="gbps-card gbps-card--full">
                    <h2><?php esc_html_e( 'Google API Credentials', 'gbp-posts-slider' ); ?></h2>
                    <p class="description">
                        <?php echo wp_kses(
                            __( 'Create a project in <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a>, enable the <strong>My Business Posting API</strong>, and create an <strong>OAuth 2.0 Client ID</strong> (Web application type). Add the following as an <strong>Authorized redirect URI</strong>:', 'gbp-posts-slider' ),
                            [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ], 'strong' => [] ]
                        ); ?>
                    </p>
                    <p><code class="gbps-uri"><?php echo esc_html( $redirect_uri ); ?></code>
                        <button type="button" class="button button-small gbps-copy-btn" data-copy="<?php echo esc_attr( $redirect_uri ); ?>">
                            <?php esc_html_e( 'Copy', 'gbp-posts-slider' ); ?>
                        </button>
                    </p>

                    <form method="post" action="">
                        <?php wp_nonce_field( 'gbp_save_settings' ); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Client ID', 'gbp-posts-slider' ); ?></th>
                                <td>
                                    <input type="text" name="client_id" class="regular-text" value="<?php echo esc_attr( $settings['client_id'] ); ?>" autocomplete="off">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Client Secret', 'gbp-posts-slider' ); ?></th>
                                <td>
                                    <input type="password" name="client_secret" class="regular-text" value="<?php echo esc_attr( $settings['client_secret'] ); ?>" autocomplete="new-password">
                                </td>
                            </tr>
                        </table>
                        <!-- Preserve location fields when saving credentials -->
                        <input type="hidden" name="account_id"  value="<?php echo esc_attr( $settings['account_id'] ); ?>">
                        <input type="hidden" name="location_id" value="<?php echo esc_attr( $settings['location_id'] ); ?>">
                        <p><button class="button button-primary" name="gbp_save_settings" value="1"><?php esc_html_e( 'Save Credentials', 'gbp-posts-slider' ); ?></button></p>
                    </form>
                </div>
                <?php endif; ?>

                <!-- ── Business Location Card ── -->
                <div class="gbps-card gbps-card--full">
                    <h2><?php esc_html_e( 'Business Location', 'gbp-posts-slider' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Enter the numeric IDs from your Google Business Profile — not prefixed with "accounts/" or "locations/".', 'gbp-posts-slider' ); ?>
                    </p>

                    <form method="post" action="">
                        <?php wp_nonce_field( 'gbp_save_settings' ); ?>
                        <?php if ( $this->credentials_are_built_in() ) : ?>
                            <!-- Credentials handled via wp-config.php constants -->
                        <?php else : ?>
                            <input type="hidden" name="client_id"     value="<?php echo esc_attr( $settings['client_id'] ); ?>">
                            <input type="hidden" name="client_secret" value="<?php echo esc_attr( $settings['client_secret'] ); ?>">
                        <?php endif; ?>

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Account ID', 'gbp-posts-slider' ); ?></th>
                                <td>
                                    <input type="text" name="account_id" class="regular-text" value="<?php echo esc_attr( $settings['account_id'] ); ?>">
                                    <p class="description"><?php esc_html_e( 'Example: 123456789', 'gbp-posts-slider' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Location ID', 'gbp-posts-slider' ); ?></th>
                                <td>
                                    <input type="text" name="location_id" class="regular-text" value="<?php echo esc_attr( $settings['location_id'] ); ?>">
                                    <p class="description"><?php esc_html_e( 'Example: 987654321', 'gbp-posts-slider' ); ?></p>
                                </td>
                            </tr>
                        </table>
                        <p><button class="button button-primary" name="gbp_save_settings" value="1"><?php esc_html_e( 'Save Settings', 'gbp-posts-slider' ); ?></button></p>
                    </form>
                </div>

                <!-- ── Sync Card (only when connected) ── -->
                <?php if ( $is_connected ) : ?>
                <div class="gbps-card">
                    <h2><?php esc_html_e( 'Sync Posts', 'gbp-posts-slider' ); ?></h2>
                    <p>
                        <?php printf(
                            esc_html__( 'Posts sync automatically every hour. %s currently synced.', 'gbp-posts-slider' ),
                            '<strong>' . (int) $synced_count . ' ' . esc_html__( 'posts', 'gbp-posts-slider' ) . '</strong>'
                        ); ?>
                    </p>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'gbp_sync_now' ); ?>
                        <button class="button" name="gbp_sync_now" value="1"><?php esc_html_e( 'Sync Now', 'gbp-posts-slider' ); ?></button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- ── Shortcode Reference Card ── -->
                <div class="gbps-card">
                    <h2><?php esc_html_e( 'Shortcode', 'gbp-posts-slider' ); ?></h2>
                    <p><?php esc_html_e( 'Paste into any page, post, or widget:', 'gbp-posts-slider' ); ?></p>
                    <p><code>[gbp_posts_slider]</code></p>
                    <p><strong><?php esc_html_e( 'Optional attributes:', 'gbp-posts-slider' ); ?></strong></p>
                    <table class="widefat striped gbps-attr-table">
                        <thead><tr>
                            <th><?php esc_html_e( 'Attribute', 'gbp-posts-slider' ); ?></th>
                            <th><?php esc_html_e( 'Default', 'gbp-posts-slider' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'gbp-posts-slider' ); ?></th>
                        </tr></thead>
                        <tbody>
                            <tr><td><code>limit</code></td><td>6</td><td><?php esc_html_e( 'Number of posts to display', 'gbp-posts-slider' ); ?></td></tr>
                            <tr><td><code>show_images</code></td><td>true</td><td><?php esc_html_e( 'Show post images', 'gbp-posts-slider' ); ?></td></tr>
                            <tr><td><code>show_date</code></td><td>true</td><td><?php esc_html_e( 'Show post date', 'gbp-posts-slider' ); ?></td></tr>
                            <tr><td><code>autoplay</code></td><td>true</td><td><?php esc_html_e( 'Enable carousel autoplay', 'gbp-posts-slider' ); ?></td></tr>
                            <tr><td><code>slides_per_view</code></td><td>3</td><td><?php esc_html_e( 'Slides visible on desktop (1–6)', 'gbp-posts-slider' ); ?></td></tr>
                        </tbody>
                    </table>
                    <p style="margin-top:12px;">
                        <code>[gbp_posts_slider limit="4" autoplay="false" slides_per_view="2"]</code>
                    </p>
                </div>

            </div><!-- .gbps-grid -->
        </div><!-- .gbps-wrap -->

        <style>
        .gbps-wrap { max-width: 900px; }
        .gbps-page-title { display:flex; align-items:center; }
        .gbps-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .gbps-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:20px 24px; }
        .gbps-card--full { grid-column:1/-1; }
        .gbps-card h2 { font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#50575e; border-bottom:1px solid #f0f0f1; padding-bottom:12px; margin:0 0 16px; }
        .gbps-status { display:flex; align-items:center; gap:8px; font-size:15px; font-weight:500; margin-bottom:14px; }
        .gbps-status--ok   { color:#00a32a; }
        .gbps-status--warn { color:#d63638; }
        .gbps-connect-btn { font-size:14px !important; }
        .gbps-uri { display:inline-block; padding:4px 8px; background:#f6f7f7; border-radius:3px; word-break:break-all; }
        .gbps-copy-btn { margin-left:8px; vertical-align:middle; }
        .gbps-attr-table { font-size:13px; margin-top:8px; }
        .gbps-attr-table td { vertical-align:middle; padding:6px 10px; }
        @media (max-width:782px) { .gbps-grid { grid-template-columns:1fr; } }
        </style>

        <script>
        document.querySelectorAll('.gbps-copy-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                navigator.clipboard.writeText(btn.dataset.copy).then(function() {
                    btn.textContent = '<?php echo esc_js( __( 'Copied!', 'gbp-posts-slider' ) ); ?>';
                    setTimeout(function() {
                        btn.textContent = '<?php echo esc_js( __( 'Copy', 'gbp-posts-slider' ) ); ?>';
                    }, 2000);
                });
            });
        });
        </script>
        <?php
    }

    /* ------------------------------------------------------------------
     *  OAuth helpers
     * ------------------------------------------------------------------ */

    private function redirect_uri() {
        return admin_url( 'options-general.php?page=gbp-posts-slider' );
    }

    private function get_auth_url() {
        $client_id = $this->client_id();
        if ( empty( $client_id ) ) return '#';

        // Generate a one-time state token for CSRF protection.
        $state = bin2hex( random_bytes( 16 ) );
        set_transient( self::OAUTH_STATE_KEY, $state, 10 * MINUTE_IN_SECONDS );

        return add_query_arg( [
            'client_id'     => $client_id,
            'redirect_uri'  => $this->redirect_uri(),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/business.manage',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ], 'https://accounts.google.com/o/oauth2/v2/auth' );
    }

    public function handle_oauth_callback() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) return;
        if ( empty( $_GET['page'] ) || 'gbp-posts-slider' !== $_GET['page'] ) return;

        // Handle explicit OAuth error from Google.
        if ( ! empty( $_GET['error'] ) ) {
            $error = urlencode( sanitize_text_field( $_GET['error'] ) );
            wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&oauth_error=' . $error ) );
            exit;
        }

        if ( empty( $_GET['code'] ) ) return;

        // Verify the state parameter to prevent CSRF.
        $stored_state   = (string) get_transient( self::OAUTH_STATE_KEY );
        $received_state = sanitize_text_field( $_GET['state'] ?? '' );

        if ( empty( $stored_state ) || ! hash_equals( $stored_state, $received_state ) ) {
            wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&oauth_error='
                . urlencode( 'Invalid state token. Please try connecting again.' ) ) );
            exit;
        }
        delete_transient( self::OAUTH_STATE_KEY );

        // Exchange the authorization code for tokens.
        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
            'body' => [
                'code'          => sanitize_text_field( $_GET['code'] ),
                'client_id'     => $this->client_id(),
                'client_secret' => $this->client_secret(),
                'redirect_uri'  => $this->redirect_uri(),
                'grant_type'    => 'authorization_code',
            ],
            'timeout' => 20,
        ] );

        if ( is_wp_error( $response ) ) {
            wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&oauth_error='
                . urlencode( $response->get_error_message() ) ) );
            exit;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $body['refresh_token'] ) ) {
            $settings                  = $this->settings();
            $settings['refresh_token'] = sanitize_text_field( $body['refresh_token'] );
            $this->save_settings( $settings );

            wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&connected=1' ) );
            exit;
        }

        $error_msg = $body['error_description'] ?? $body['error'] ?? 'Token exchange failed. Check your credentials and redirect URI.';
        wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&oauth_error='
            . urlencode( $error_msg ) ) );
        exit;
    }

    public function ajax_disconnect() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );
        check_admin_referer( 'gbp_disconnect' );

        $settings                  = $this->settings();
        $settings['refresh_token'] = '';
        $this->save_settings( $settings );
        delete_transient( 'gbp_access_token_cache' );

        wp_safe_redirect( admin_url( 'options-general.php?page=gbp-posts-slider&disconnected=1' ) );
        exit;
    }

    /* ------------------------------------------------------------------
     *  Google API — fetch access token
     * ------------------------------------------------------------------ */

    private function get_access_token() {
        $settings = $this->settings();
        if ( empty( $settings['refresh_token'] ) ) return false;

        $cached = get_transient( 'gbp_access_token_cache' );
        if ( $cached ) return $cached;

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
            'body' => [
                'client_id'     => $this->client_id(),
                'client_secret' => $this->client_secret(),
                'refresh_token' => $settings['refresh_token'],
                'grant_type'    => 'refresh_token',
            ],
            'timeout' => 20,
        ] );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['access_token'] ) ) return false;

        $expires_in = max( 300, absint( $body['expires_in'] ?? 3600 ) );
        set_transient( 'gbp_access_token_cache', $body['access_token'], $expires_in - 300 );

        return $body['access_token'];
    }

    /* ------------------------------------------------------------------
     *  Sync GBP posts → CPT
     * ------------------------------------------------------------------ */

    public function sync_posts() {
        $settings = $this->settings();

        if ( empty( $settings['account_id'] ) || empty( $settings['location_id'] ) ) return 0;

        $access_token = $this->get_access_token();
        if ( ! $access_token ) return 0;

        // Try the current My Business Posting API (v1), then fall back to deprecated v4.
        $posts = $this->fetch_posts_v1( $access_token, $settings )
              ?? $this->fetch_posts_v4( $access_token, $settings );

        if ( empty( $posts ) || ! is_array( $posts ) ) return 0;

        $count = 0;
        foreach ( $posts as $gbp_post ) {
            if ( $this->upsert_cpt_post( $gbp_post ) ) {
                $count++;
            }
        }

        $this->prune_deleted_posts( $posts );

        return $count;
    }

    /** My Business Posting API v1 (current). */
    private function fetch_posts_v1( $access_token, $settings ) {
        $url = sprintf(
            'https://mybusinesspostingapi.googleapis.com/v1/accounts/%s/locations/%s/localPosts?pageSize=20',
            rawurlencode( $settings['account_id'] ),
            rawurlencode( $settings['location_id'] )
        );

        return $this->do_get( $url, $access_token, 'localPosts' );
    }

    /** My Business API v4 (deprecated — kept as fallback). */
    private function fetch_posts_v4( $access_token, $settings ) {
        $url = sprintf(
            'https://mybusiness.googleapis.com/v4/accounts/%s/locations/%s/localPosts?pageSize=20',
            rawurlencode( $settings['account_id'] ),
            rawurlencode( $settings['location_id'] )
        );

        return $this->do_get( $url, $access_token, 'localPosts' );
    }

    private function do_get( $url, $access_token, $key ) {
        $response = wp_remote_get( $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ],
            'timeout' => 20,
        ] );

        if ( is_wp_error( $response ) ) return null;
        if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) return null;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return $body[ $key ] ?? null;
    }

    /** Insert or update a single GBP post in the CPT cache. */
    private function upsert_cpt_post( array $gbp_post ) {
        $name      = $gbp_post['name']                  ?? '';
        $summary   = $gbp_post['summary']               ?? '';
        $created   = $gbp_post['createTime']            ?? '';
        $image_url = $gbp_post['media'][0]['googleUrl'] ?? '';
        $cta       = $gbp_post['callToAction']          ?? [];
        $cta_url   = $cta['url']                        ?? '';
        $cta_type  = $cta['actionType']                 ?? '';
        $topic     = $gbp_post['topicType']             ?? '';

        if ( empty( $name ) ) return false;

        $existing = get_posts( [
            'post_type'      => self::CPT_SLUG,
            'post_status'    => 'any',
            'meta_key'       => '_gbp_name',
            'meta_value'     => $name,
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );

        $post_title = $summary
            ? wp_trim_words( $summary, 12, '…' )
            : __( 'GBP Post', 'gbp-posts-slider' );

        $post_data = [
            'post_type'    => self::CPT_SLUG,
            'post_status'  => 'publish',
            'post_title'   => $post_title,
            'post_content' => $summary,
            'post_date'    => $created
                ? get_date_from_gmt( gmdate( 'Y-m-d H:i:s', strtotime( $created ) ) )
                : current_time( 'mysql' ),
        ];

        if ( ! empty( $existing ) ) {
            $post_data['ID'] = $existing[0];
            $post_id = wp_update_post( $post_data, true );
        } else {
            $post_id = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $post_id ) ) return false;

        update_post_meta( $post_id, '_gbp_name',      $name );
        update_post_meta( $post_id, '_gbp_image_url', esc_url_raw( $image_url ) );
        update_post_meta( $post_id, '_gbp_cta_url',   esc_url_raw( $cta_url ) );
        update_post_meta( $post_id, '_gbp_cta_type',  sanitize_text_field( $cta_type ) );
        update_post_meta( $post_id, '_gbp_topic',     sanitize_text_field( $topic ) );

        return true;
    }

    /** Delete CPT posts whose GBP counterpart no longer exists. */
    private function prune_deleted_posts( array $gbp_posts ) {
        $live_names = array_filter( array_column( $gbp_posts, 'name' ) );
        if ( empty( $live_names ) ) return;

        $cached = get_posts( [
            'post_type'      => self::CPT_SLUG,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            'meta_query'     => [ [ 'key' => '_gbp_name', 'compare' => 'EXISTS' ] ],
        ] );

        foreach ( $cached as $post ) {
            $post_name = get_post_meta( $post->ID, '_gbp_name', true );
            if ( ! in_array( $post_name, $live_names, true ) ) {
                wp_delete_post( $post->ID, true );
            }
        }
    }

    private function get_synced_count() {
        $counts = wp_count_posts( self::CPT_SLUG );
        return isset( $counts->publish ) ? (int) $counts->publish : 0;
    }

    /* ------------------------------------------------------------------
     *  Front-end assets
     * ------------------------------------------------------------------ */

    public function enqueue_assets() {
        wp_enqueue_style(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [],
            '11'
        );
        wp_enqueue_script(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [],
            '11',
            true
        );
        wp_enqueue_style(
            'gbp-posts-slider',
            GBP_SLIDER_URL . 'assets/gbp-slider.css',
            [ 'swiper' ],
            GBP_SLIDER_VERSION
        );
        wp_enqueue_script(
            'gbp-posts-slider',
            GBP_SLIDER_URL . 'assets/gbp-slider.js',
            [ 'swiper' ],
            GBP_SLIDER_VERSION,
            true
        );
    }

    /* ------------------------------------------------------------------
     *  Shortcode
     * ------------------------------------------------------------------ */

    public function shortcode( $atts ) {
        $atts = shortcode_atts( [
            'limit'           => 6,
            'show_images'     => 'true',
            'show_date'       => 'true',
            'autoplay'        => 'true',
            'slides_per_view' => 3,
        ], $atts, 'gbp_posts_slider' );

        $limit          = max( 1, absint( $atts['limit'] ) );
        $show_images    = filter_var( $atts['show_images'],     FILTER_VALIDATE_BOOLEAN );
        $show_date      = filter_var( $atts['show_date'],       FILTER_VALIDATE_BOOLEAN );
        $autoplay       = filter_var( $atts['autoplay'],        FILTER_VALIDATE_BOOLEAN );
        $slides_desktop = max( 1, min( 6, absint( $atts['slides_per_view'] ) ) );

        $posts = get_posts( [
            'post_type'      => self::CPT_SLUG,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ] );

        if ( empty( $posts ) ) {
            return '<p class="gbp-no-posts">' . esc_html__( 'No Google Business Profile posts found.', 'gbp-posts-slider' ) . '</p>';
        }

        $slider_id = 'gbp-slider-' . wp_unique_id();

        ob_start();
        ?>
        <div class="swiper gbp-posts-slider"
             id="<?php echo esc_attr( $slider_id ); ?>"
             data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
             data-slides-desktop="<?php echo esc_attr( $slides_desktop ); ?>"
             role="region"
             aria-label="<?php esc_attr_e( 'Google Business Profile Posts', 'gbp-posts-slider' ); ?>">

            <div class="swiper-wrapper">
                <?php foreach ( $posts as $post ) :
                    $image_url = get_post_meta( $post->ID, '_gbp_image_url', true );
                    $cta_url   = get_post_meta( $post->ID, '_gbp_cta_url',   true );
                    $cta_type  = get_post_meta( $post->ID, '_gbp_cta_type',  true );
                    $summary   = get_the_content( null, false, $post );
                ?>
                <div class="swiper-slide gbp-post-card">

                    <?php if ( $show_images && $image_url ) : ?>
                    <div class="gbp-post-image">
                        <img src="<?php echo esc_url( $image_url ); ?>"
                             alt="<?php echo esc_attr( wp_trim_words( $summary, 10 ) ); ?>"
                             loading="lazy"
                             decoding="async">
                    </div>
                    <?php endif; ?>

                    <div class="gbp-post-content">

                        <?php if ( $show_date ) : ?>
                        <div class="gbp-post-date">
                            <?php echo esc_html( get_the_date( get_option( 'date_format' ), $post ) ); ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( $summary ) : ?>
                        <p class="gbp-post-summary"><?php echo esc_html( wp_trim_words( $summary, 28 ) ); ?></p>
                        <?php endif; ?>

                        <?php if ( $cta_url ) :
                            $label = $cta_type
                                ? ucwords( strtolower( str_replace( '_', ' ', $cta_type ) ) )
                                : __( 'Learn More', 'gbp-posts-slider' );
                        ?>
                        <a class="gbp-post-button"
                           href="<?php echo esc_url( $cta_url ); ?>"
                           target="_blank"
                           rel="noopener noreferrer">
                            <?php echo esc_html( $label ); ?>
                        </a>
                        <?php endif; ?>

                    </div><!-- .gbp-post-content -->
                </div><!-- .swiper-slide -->
                <?php endforeach; ?>
            </div><!-- .swiper-wrapper -->

            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous post', 'gbp-posts-slider' ); ?>"></div>
            <div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Next post', 'gbp-posts-slider' ); ?>"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new GBP_Posts_Slider();
