<?php
/*
Plugin Name: 360 Global Blocks
Description: Custom Gutenberg blocks for the 360 network. 
 * Version: 1.3.41
Author: Kaz Alvis
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SB_GLOBAL_BLOCKS_VERSION', '1.3.41' );
define( 'SB_GLOBAL_BLOCKS_PLUGIN_FILE', __FILE__ );
define(
    'SB_GLOBAL_BLOCKS_MANIFEST_URL',
    'https://raw.githubusercontent.com/KazimirAlvis/360-global-blocks/main/plugin-manifest.json'
);

require_once plugin_dir_path( __FILE__ ) . 'inc/class-sb-global-blocks-updater.php';

/**
 * Resolve the font family currently assigned to heading typography.
 *
 * @return string Heading font family string if identifiable, otherwise empty string.
 */
function global360blocks_get_heading_font_family() {
    static $resolved = null;

    if ( null !== $resolved ) {
        return $resolved;
    }

    $font_family = '';

    if ( function_exists( 'wp_get_global_styles' ) ) {
        $maybe_heading_font = wp_get_global_styles( array( 'elements', 'heading', 'typography', 'fontFamily' ) );
        if ( is_string( $maybe_heading_font ) && '' !== $maybe_heading_font ) {
            $font_family = $maybe_heading_font;
        }
    }

    if ( ! $font_family && function_exists( 'wp_get_global_settings' ) ) {
        $maybe_user_heading = wp_get_global_settings( array( 'typography', 'fontFamilies', 'user', 'heading', 'fontFamily' ) );
        if ( is_string( $maybe_user_heading ) && '' !== $maybe_user_heading ) {
            $font_family = $maybe_user_heading;
        }
    }

    if ( ! $font_family && function_exists( 'wp_get_global_settings' ) ) {
        $maybe_theme_heading = wp_get_global_settings( array( 'typography', 'fontFamilies', 'theme', 'heading', 'fontFamily' ) );
        if ( is_string( $maybe_theme_heading ) && '' !== $maybe_theme_heading ) {
            $font_family = $maybe_theme_heading;
        }
    }

    if ( ! $font_family && function_exists( 'wp_get_global_settings' ) ) {
        $maybe_root_font = wp_get_global_settings( array( 'typography', 'fontFamily' ) );
        if ( is_string( $maybe_root_font ) && '' !== $maybe_root_font ) {
            $font_family = $maybe_root_font;
        }
    }

    if ( ! $font_family ) {
        $maybe_theme_mod = get_theme_mod( 'typography_heading_font_family', '' );
        if ( is_string( $maybe_theme_mod ) ) {
            $font_family = $maybe_theme_mod;
        }
    }

    if ( is_string( $font_family ) && preg_match( '/var\(([^)]+)\)/', $font_family, $matches ) ) {
        $preset_identifier = trim( $matches[1] );

        if ( '' !== $preset_identifier && function_exists( 'wp_get_global_settings' ) ) {
            $preset_identifier = explode( ',', $preset_identifier )[0];
            $preset_identifier = trim( $preset_identifier );

            if ( 0 === strpos( $preset_identifier, '--wp--preset--font-family--' ) ) {
                $slug = str_replace( '--wp--preset--font-family--', '', $preset_identifier );
                $font_collections = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );

                if ( is_array( $font_collections ) ) {
                    foreach ( $font_collections as $collection ) {
                        if ( ! is_array( $collection ) ) {
                            continue;
                        }

                        foreach ( $collection as $font_entry ) {
                            if ( isset( $font_entry['slug'], $font_entry['fontFamily'] ) && $slug === $font_entry['slug'] ) {
                                $font_family = $font_entry['fontFamily'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    $resolved = is_string( $font_family ) ? $font_family : '';

    return $resolved;
}

/**
 * Determine the letter-spacing value for the heading font.
 *
 * Defaults to normal spacing unless the heading font resolves to Anton, in which case
 * the site uses the requested 0.5px spacing.
 *
 * @return string CSS letter-spacing value.
 */
function global360blocks_get_heading_letter_spacing_value() {
    static $cached = null;

    if ( null !== $cached ) {
        return $cached;
    }

    $font_family = strtolower( global360blocks_get_heading_font_family() );
    $is_anton = false;

    if ( $font_family ) {
    if ( false !== strpos( $font_family, 'anton' ) || false !== strpos( $font_family, 'wp--preset--font-family--anton' ) ) {
            $is_anton = true;
        }
    }

    $value = $is_anton ? '0.5px' : 'normal';

    $cached = apply_filters( 'global360blocks_heading_letter_spacing_value', $value, $font_family );

    return $cached;
}

/**
 * Build shared CSS for heading letter-spacing support.
 *
 * @param string $context Either 'frontend' or 'editor'.
 * @return string
 */
function global360blocks_get_heading_letter_spacing_css( $context = 'frontend' ) {
    $letter_spacing = global360blocks_get_heading_letter_spacing_value();

    if ( ! $letter_spacing ) {
        return '';
    }

    $root_selector = ':root{--heading-letter-spacing:' . esc_attr( $letter_spacing ) . ';}';
    $heading_selector = ':where(h1,h2,h3,h4,h5,h6){letter-spacing:var(--heading-letter-spacing,normal);}';

    if ( 'editor' === $context ) {
        $heading_selector = '.editor-styles-wrapper ' . $heading_selector;
    }

    return $root_selector . $heading_selector;
}

/**
 * Enqueue heading letter-spacing support on the frontend.
 */
function global360blocks_enqueue_heading_letter_spacing_styles() {
    $css = global360blocks_get_heading_letter_spacing_css( 'frontend' );

    if ( '' === $css ) {
        return;
    }

    $handle = 'global360blocks-heading-typography';

    if ( ! wp_style_is( $handle, 'enqueued' ) ) {
        wp_register_style( $handle, false, array(), SB_GLOBAL_BLOCKS_VERSION );
        wp_enqueue_style( $handle );
    }

    wp_add_inline_style( $handle, $css );
}
add_action( 'wp_enqueue_scripts', 'global360blocks_enqueue_heading_letter_spacing_styles', 1 );

/**
 * Enqueue heading letter-spacing support for the block editor.
 */
function global360blocks_enqueue_heading_letter_spacing_editor_styles() {
    $css = global360blocks_get_heading_letter_spacing_css( 'editor' );

    if ( '' === $css ) {
        return;
    }

    $handle = 'global360blocks-heading-typography-editor';

    if ( ! wp_style_is( $handle, 'enqueued' ) ) {
        wp_register_style( $handle, false, array(), SB_GLOBAL_BLOCKS_VERSION );
        wp_enqueue_style( $handle );
    }

    wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_editor_assets', 'global360blocks_enqueue_heading_letter_spacing_editor_styles', 1 );

/**
 * Register shared frontend styles used across multiple blocks.
 */
function global360blocks_register_global_shared_styles() {
    static $registered = false;

    if ( $registered ) {
        return;
    }

    $handle        = 'global360blocks-global-shared-style';
    $relative_file = 'assets/css/global-shared.min.css';
    $absolute_path = plugin_dir_path( __FILE__ ) . $relative_file;

    if ( file_exists( $absolute_path ) ) {
        wp_register_style(
            $handle,
            plugins_url( $relative_file, __FILE__ ),
            array(),
            filemtime( $absolute_path )
        );
    }

    $registered = true;
}
add_action( 'wp_enqueue_scripts', 'global360blocks_register_global_shared_styles', 1 );

/**
 * Ensure shared frontend styles are queued when needed.
 */
function global360blocks_enqueue_global_shared_style() {
    global360blocks_register_global_shared_styles();

    if ( wp_style_is( 'global360blocks-global-shared-style', 'registered' ) ) {
        wp_enqueue_style( 'global360blocks-global-shared-style' );
    }
}

/**
 * Wrap common trademark symbols in a superscript span for consistent sizing.
 *
 * @param string $text Heading or paragraph content.
 * @return string
 */
function global360blocks_wrap_trademark_symbols( $text ) {
    if ( '' === $text ) {
        return $text;
    }

    if ( false === strpos( $text, '™' ) && false === strpos( $text, '®' ) && false === strpos( $text, '℠' ) ) {
        return $text;
    }

    $result = preg_replace_callback(
        '/(<sup\b[^>]*>.*?<\/sup>)|([™®℠])/us',
        static function ( $matches ) {
            if ( ! empty( $matches[1] ) ) {
                return $matches[1];
            }

            return '<sup class="full-hero-trademark">' . $matches[2] . '</sup>';
        },
        $text
    );

    return null !== $result ? $result : $text;
}

function sb_global_blocks_bootstrap_updater() {
    if ( isset( $GLOBALS['sb_global_blocks_updater'] ) ) {
        return;
    }

    $GLOBALS['sb_global_blocks_updater'] = new SB_Global_Blocks_Updater(
        array(
            'manifest_url' => SB_GLOBAL_BLOCKS_MANIFEST_URL,
            'plugin_file'  => SB_GLOBAL_BLOCKS_PLUGIN_FILE,
            'version'      => SB_GLOBAL_BLOCKS_VERSION,
        )
    );
}
add_action( 'plugins_loaded', 'sb_global_blocks_bootstrap_updater', 5 );

function sb_global_blocks_rename_github_package( $source, $remote_source, $upgrader, $hook_extra ) {
    $source_path  = untrailingslashit( $source );
    $source_dir   = basename( $source_path );
    $expected_dir = '360-global-blocks';

    $is_target = false;

    if ( isset( $hook_extra['plugin'] ) && strcasecmp( $hook_extra['plugin'], '360-Global-Blocks/360-global-blocks.php' ) === 0 ) {
        $is_target = true;
    }

    if ( isset( $hook_extra['slug'] ) && '360-global-blocks' === $hook_extra['slug'] ) {
        $is_target = true;
    }

    if ( ! $is_target && false !== stripos( $source_dir, '360-global-blocks' ) ) {
        $is_target = true;
    }

    if ( ! $is_target ) {
        return $source;
    }

    $desired_path = trailingslashit( dirname( $source_path ) ) . $expected_dir;

    if ( strcasecmp( $source_dir, $expected_dir ) === 0 ) {
        return trailingslashit( $source_path );
    }

    global $wp_filesystem;

    if ( ! $wp_filesystem && defined( 'ABSPATH' ) ) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
    }

    if ( $wp_filesystem && $wp_filesystem->exists( $desired_path ) ) {
        $wp_filesystem->delete( $desired_path, true );
    }

    $moved = false;

    if ( $wp_filesystem && $wp_filesystem->move( trailingslashit( $source_path ), trailingslashit( $desired_path ), true ) ) {
        $moved = true;
    }

    if ( ! $moved && @rename( $source_path, $desired_path ) ) {
        $moved = true;
    }

    if ( $moved ) {
        return trailingslashit( $desired_path );
    }

    return $source;
}
add_filter( 'upgrader_source_selection', 'sb_global_blocks_rename_github_package', 10, 4 );

function sb_global_blocks_ensure_install_location( $response, $hook_extra, $result ) {
    $expected_dir = '360-global-blocks';
    $is_target    = false;

    if ( isset( $hook_extra['plugin'] ) && strcasecmp( $hook_extra['plugin'], '360-Global-Blocks/360-global-blocks.php' ) === 0 ) {
        $is_target = true;
    }

    if ( isset( $hook_extra['slug'] ) && '360-global-blocks' === $hook_extra['slug'] ) {
        $is_target = true;
    }

    if ( ! $is_target ) {
        return $response;
    }

    $destination = isset( $result['destination'] ) ? $result['destination'] : '';
    if ( ! $destination ) {
        return $response;
    }

    $destination_dir = trailingslashit( $destination );
    $expected_path   = trailingslashit( WP_PLUGIN_DIR ) . $expected_dir . '/';

    if ( strtolower( $destination_dir ) === strtolower( $expected_path ) ) {
        return $response;
    }

    global $wp_filesystem;

    if ( ! $wp_filesystem && defined( 'ABSPATH' ) ) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
    }

    $moved = false;

    if ( $wp_filesystem && $wp_filesystem->exists( $destination_dir ) ) {
        if ( $wp_filesystem->exists( $expected_path ) ) {
            $wp_filesystem->delete( $expected_path, true );
        }

        if ( $wp_filesystem->move( $destination_dir, $expected_path, true ) ) {
            $moved = true;
        }
    }

    if ( ! $moved && is_dir( $destination_dir ) ) {
        if ( is_dir( $expected_path ) ) {
            sb_global_blocks_rrmdir( $expected_path );
        }

        if ( @rename( untrailingslashit( $destination_dir ), untrailingslashit( $expected_path ) ) ) {
            $moved = true;
        }
    }

    if ( $moved ) {
        $result['destination'] = $expected_path;
        return $result;
    }

    return $response;
}
add_filter( 'upgrader_post_install', 'sb_global_blocks_ensure_install_location', 10, 3 );

if ( ! function_exists( 'sb_global_blocks_rrmdir' ) ) {
    function sb_global_blocks_rrmdir( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $items = scandir( $dir );
        if ( ! $items ) {
            return;
        }

        foreach ( $items as $item ) {
            if ( '.' === $item || '..' === $item ) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if ( is_dir( $path ) ) {
                sb_global_blocks_rrmdir( $path );
            } else {
                @unlink( $path );
            }
        }

        @rmdir( $dir );
    }
}

function sb_global_blocks_get_update_debug_data() {
    $updater = isset( $GLOBALS['sb_global_blocks_updater'] ) ? $GLOBALS['sb_global_blocks_updater'] : null;

    if ( ! $updater instanceof SB_Global_Blocks_Updater ) {
        return array();
    }

    $remote = $updater->request();
    $transient  = get_site_transient( 'update_plugins' );
    $plugin_key = plugin_basename( SB_GLOBAL_BLOCKS_PLUGIN_FILE );
    $update_row = ( $transient && isset( $transient->response[ $plugin_key ] ) ) ? $transient->response[ $plugin_key ] : null;

    return array(
        'installed_version'       => $updater->get_version(),
        'remote_version'          => $remote ? ( isset( $remote->version ) ? $remote->version : 'n/a' ) : 'n/a',
        'remote_requires_wp'      => $remote && isset( $remote->requires ) ? $remote->requires : 'n/a',
        'remote_requires_php'     => $remote && isset( $remote->requires_php ) ? $remote->requires_php : 'n/a',
        'download_url'            => $remote && isset( $remote->download_url ) ? $remote->download_url : 'n/a',
        'remote_last_updated'     => $remote && isset( $remote->last_updated ) ? $remote->last_updated : 'n/a',
        'transient_detected'      => $update_row ? $update_row->new_version : 'n/a',
        'manifest_url'            => SB_GLOBAL_BLOCKS_MANIFEST_URL,
        'last_error'              => $updater->get_last_error() ? $updater->get_last_error() : 'none',
    );
}

function sb_global_blocks_update_debug_notice() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! isset( $_GET['sb-update-debug'] ) ) {
        return;
    }

    $data = sb_global_blocks_get_update_debug_data();
    if ( empty( $data ) ) {
        return;
    }

    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || ! in_array( $screen->id, array( 'plugins', 'update-core' ), true ) ) {
        return;
    }

    echo '<div class="notice notice-info"><p><strong>360 Global Blocks Update Debug</strong></p><ul style="margin-left:20px;">';
    foreach ( $data as $label => $value ) {
        $display = is_scalar( $value ) ? $value : wp_json_encode( $value );
        echo '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $label ) ) ) . ':</strong> ' . esc_html( $display ) . '</li>';
    }
    echo '</ul></div>';
}
add_action( 'admin_notices', 'sb_global_blocks_update_debug_notice' );

function sb_global_blocks_add_update_tools_page() {
    add_management_page(
        __( '360 Blocks Updates', '360-global-blocks' ),
        __( '360 Blocks Updates', '360-global-blocks' ),
        'manage_options',
        '360-blocks-updates',
        'sb_global_blocks_render_update_tools_page'
    );
}
add_action( 'admin_menu', 'sb_global_blocks_add_update_tools_page' );

function sb_global_blocks_render_update_tools_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', '360-global-blocks' ) );
    }

    echo '<div class="wrap"><h1>' . esc_html__( '360 Global Blocks - Update Diagnostics', '360-global-blocks' ) . '</h1>';

    $data = sb_global_blocks_get_update_debug_data();
    if ( empty( $data ) ) {
        echo '<p>' . esc_html__( 'Updater service is not initialised.', '360-global-blocks' ) . '</p></div>';
        return;
    }

    $transient  = get_site_transient( 'update_plugins' );
    $plugin_key = plugin_basename( __FILE__ );
    $update_row = ( $transient && isset( $transient->response[ $plugin_key ] ) ) ? $transient->response[ $plugin_key ] : null;

    echo '<table class="widefat striped" style="max-width:680px">';
    foreach ( $data as $label => $value ) {
        $display = is_scalar( $value ) ? $value : wp_json_encode( $value );
        echo '<tr><th scope="row">' . esc_html( ucwords( str_replace( '_', ' ', $label ) ) ) . '</th><td>' . esc_html( $display ) . '</td></tr>';
    }

    if ( $update_row ) {
        echo '<tr><th scope="row">' . esc_html__( 'Update detected', '360-global-blocks' ) . '</th><td>' . esc_html( $update_row->new_version ) . '</td></tr>';
    } else {
        echo '<tr><th scope="row">' . esc_html__( 'Update detected', '360-global-blocks' ) . '</th><td>' . esc_html__( 'No entry present in update_plugins transient.', '360-global-blocks' ) . '</td></tr>';
    }
    echo '</table>';

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:20px;">';
    wp_nonce_field( 'sb_global_blocks_force_check' );
    echo '<input type="hidden" name="action" value="sb_global_blocks_force_check" />';
    echo '<input type="submit" class="button button-primary" value="' . esc_attr__( 'Force Update Check Now', '360-global-blocks' ) . '" />';
    echo '</form>';

    echo '<p style="margin-top:15px;">' . esc_html__( 'Tip: after forcing a check, revisit the Plugins screen or click “Check again” on Dashboard → Updates.', '360-global-blocks' ) . '</p>';

    echo '</div>';
}

function sb_global_blocks_handle_force_check() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Sorry, you are not allowed to perform this action.', '360-global-blocks' ) );
    }

    check_admin_referer( 'sb_global_blocks_force_check' );

    if ( isset( $GLOBALS['sb_global_blocks_updater'] ) && $GLOBALS['sb_global_blocks_updater'] instanceof SB_Global_Blocks_Updater ) {
        $GLOBALS['sb_global_blocks_updater']->force_check();
    }

    wp_safe_redirect( add_query_arg( array( 'page' => '360-blocks-updates', 'status' => 'forced' ), admin_url( 'tools.php' ) ) );
    exit;
}
add_action( 'admin_post_sb_global_blocks_force_check', 'sb_global_blocks_handle_force_check' );

// Include Health Icons Loader
require_once plugin_dir_path(__FILE__) . 'inc/health-icons-loader.php';

// Health Icons AJAX Handlers
add_action('wp_ajax_get_health_icon', 'handle_get_health_icon_ajax');
add_action('wp_ajax_nopriv_get_health_icon', 'handle_get_health_icon_ajax');

function handle_get_health_icon_ajax() {
    check_ajax_referer('health_icons_nonce', 'nonce');
    
    $icon_key = sanitize_text_field($_POST['icon_key']);
    
    if (empty($icon_key)) {
        wp_die();
    }
    
    $loader = HealthIconsLoader::getInstance();
    $svg_content = $loader->getIcon($icon_key);
    
    if ($svg_content) {
        wp_send_json_success($svg_content);
    } else {
        wp_send_json_error('Icon not found');
    }
}

// Extract a YouTube video ID from a URL.
if ( ! function_exists( 'global360blocks_get_youtube_video_id' ) ) {
    function global360blocks_get_youtube_video_id( $url ) {
        if ( empty( $url ) ) {
            return '';
        }

        $parsed = wp_parse_url( $url );
        if ( empty( $parsed['host'] ) ) {
            return '';
        }

        $host = strtolower( $parsed['host'] );
        $candidate = '';

        if ( false !== strpos( $host, 'youtube.com' ) ) {
            if ( ! empty( $parsed['query'] ) ) {
                parse_str( $parsed['query'], $query_vars );
                if ( ! empty( $query_vars['v'] ) ) {
                    $candidate = $query_vars['v'];
                }
            }

            if ( ! $candidate && ! empty( $parsed['path'] ) ) {
                $path_segments = array_values( array_filter( explode( '/', $parsed['path'] ) ) );
                if ( ! empty( $path_segments ) ) {
                    $patterns = array( 'embed', 'shorts', 'live' );
                    if ( in_array( $path_segments[0], $patterns, true ) && isset( $path_segments[1] ) ) {
                        $candidate = $path_segments[1];
                    } else {
                        $candidate = end( $path_segments );
                    }
                }
            }
        } elseif ( false !== strpos( $host, 'youtu.be' ) ) {
            if ( ! empty( $parsed['path'] ) ) {
                $candidate = trim( $parsed['path'], '/' );
            }
        }

        if ( ! $candidate ) {
            return '';
        }

        if ( preg_match( '/([A-Za-z0-9_-]{11})/', $candidate, $matches ) ) {
            return $matches[1];
        }

        return '';
    }
}

// Helper function to get YouTube embed URL
if ( ! function_exists( 'global360blocks_get_youtube_embed_url' ) ) {
    function global360blocks_get_youtube_embed_url( $url ) {
        if ( empty( $url ) ) {
            return '';
        }

        $video_id = global360blocks_get_youtube_video_id( $url );

        if ( ! $video_id ) {
            if ( false !== strpos( $url, 'youtube.com/embed/' ) ) {
                return $url;
            }

            return $url;
        }

        $base = 'https://www.youtube.com/embed/' . $video_id;
        $args = array(
            'rel'            => 0,
            'modestbranding' => 1,
            'playsinline'    => 1,
        );

        return add_query_arg( $args, $base );
    }
}

// Helper function to derive a YouTube thumbnail URL.
if ( ! function_exists( 'global360blocks_get_youtube_thumbnail_url' ) ) {
    function global360blocks_get_youtube_thumbnail_url( $video_id, $quality = 'hqdefault' ) {
        if ( empty( $video_id ) ) {
            return '';
        }

        $quality = in_array( $quality, array( 'maxresdefault', 'hqdefault', 'mqdefault', 'sddefault' ), true )
            ? $quality
            : 'hqdefault';

        return sprintf( 'https://i.ytimg.com/vi/%1$s/%2$s.jpg', rawurlencode( $video_id ), $quality );
    }
}

function global360blocks_render_popular_practices_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/popular-practices' );

    $title = !empty($attributes['title']) ? esc_html($attributes['title']) : 'Popular Practices';
    $clinics = !empty($attributes['clinics']) ? $attributes['clinics'] : [];
    
    // Get all clinic posts
    $clinic_pages = get_posts(array(
        'post_type' => 'clinic',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'rand'
    ));
    
    // If no clinic CPT, fallback to pages/posts with clinic keywords
    if (empty($clinic_pages)) {
        $clinic_pages = get_posts(array(
            'post_type' => array('page', 'post'),
            'posts_per_page' => 20,
            'orderby' => 'rand',
            's' => 'clinic practice medical'
        ));
    }
    
    $cards = array();
    $random_pool = !empty($clinic_pages) ? array_values($clinic_pages) : array();

    foreach ($clinics as $index => $clinic) {
        $clinic_id   = !empty($clinic['clinicId']) ? intval($clinic['clinicId']) : 0;
        $custom_name = !empty($clinic['customName']) ? sanitize_text_field($clinic['customName']) : '';
        $custom_logo = !empty($clinic['customLogo']) ? esc_url_raw($clinic['customLogo']) : '';
        $custom_url  = !empty($clinic['customUrl']) ? esc_url_raw($clinic['customUrl']) : '';

        $card = null;

        if ($clinic_id) {
            $clinic_page = get_post($clinic_id);
            if ($clinic_page instanceof WP_Post) {
                $clinic_name = $custom_name ?: get_the_title($clinic_page);
                $clinic_url  = $custom_url ?: get_permalink($clinic_page);

                if ($custom_logo) {
                    $clinic_logo_url = $custom_logo;
                } elseif (function_exists('cpt360_get_clinic_logo_url')) {
                    $clinic_logo_url = cpt360_get_clinic_logo_url($clinic_page->ID);
                } else {
                    $clinic_logo_url = get_the_post_thumbnail_url($clinic_page, 'medium');
                }

                $card = array(
                    'name' => $clinic_name,
                    'url'  => $clinic_url,
                    'logo' => $clinic_logo_url,
                );
            }
        } elseif ($custom_name || $custom_logo || $custom_url) {
            $card = array(
                'name' => $custom_name ?: 'Clinic',
                'url'  => $custom_url ?: '#',
                'logo' => $custom_logo,
            );
        } elseif (!empty($random_pool)) {
            $random_key    = array_rand($random_pool);
            $random_clinic = $random_pool[$random_key];

            $clinic_name = $custom_name ?: get_the_title($random_clinic);
            $clinic_url  = $custom_url ?: get_permalink($random_clinic);

            if ($custom_logo) {
                $clinic_logo_url = $custom_logo;
            } elseif (function_exists('cpt360_get_clinic_logo_url')) {
                $clinic_logo_url = cpt360_get_clinic_logo_url($random_clinic->ID);
            } else {
                $clinic_logo_url = get_the_post_thumbnail_url($random_clinic, 'medium');
            }

            $card = array(
                'name' => $clinic_name,
                'url'  => $clinic_url,
                'logo' => $clinic_logo_url,
            );

            array_splice($random_pool, $random_key, 1);
            $random_pool = array_values($random_pool);
        } elseif (empty($clinic_pages)) {
            $card = array(
                'name' => $custom_name ?: 'Sample Clinic ' . ($index + 1),
                'url'  => $custom_url ?: '#',
                'logo' => $custom_logo ?: '',
            );
        }

        if ($card) {
            $cards[] = $card;
        }
    }

    if (empty($cards)) {
        return '';
    }

    $card_count   = count($cards);
    $grid_classes = 'practices-grid';
    if ($card_count < 4) {
        $grid_classes .= ' practices-grid--count-' . $card_count;
    }

    $grid_style = '';
    if ($card_count > 0 && $card_count < 4) {
        $grid_width = ($card_count * 280) + max(0, ($card_count - 1) * 20);
        $grid_style = sprintf(' style="max-width:%spx"', esc_attr((string) $grid_width));
    }

    $output = '<div class="wp-block-global360blocks-popular-practices popular-practices-block">';
    $output .= '<div class="popular-practices-content">';
    $output .= '<h2 class="popular-practices-title">' . $title . '</h2>';
    $output .= '<div class="' . esc_attr($grid_classes) . '"' . $grid_style . '>';

    foreach ($cards as $card) {
        $clinic_name     = !empty($card['name']) ? $card['name'] : '';
        $clinic_url      = !empty($card['url']) ? $card['url'] : '#';
        $clinic_logo_url = !empty($card['logo']) ? $card['logo'] : '';

        $output .= '<a href="' . esc_url($clinic_url) . '" class="practice-card">';
        $output .= '<div class="practice-logo">';

        if (!empty($clinic_logo_url)) {
            $output .= '<img src="' . esc_url($clinic_logo_url) . '" alt="' . esc_attr($clinic_name) . ' Logo" />';
        } else {
            $output .= '<div class="logo-placeholder">Logo</div>';
        }

        $output .= '</div>';
        $output .= '<h3 class="practice-name">' . esc_html($clinic_name) . '</h3>';
        $output .= '</a>';
    }

    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Render callback for Two Column CTA block
function global360blocks_render_two_column_cta_block($attributes, $content) {
    global360blocks_enqueue_block_assets_from_manifest('global360blocks/two-column-cta');

    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $button_text = !empty($attributes['buttonText']) ? esc_html($attributes['buttonText']) : 'Take Risk Assessment Now';
    $button_url = !empty($attributes['buttonUrl']) ? esc_url($attributes['buttonUrl']) : '';
    $background_color = !empty($attributes['backgroundColor']) ? esc_attr($attributes['backgroundColor']) : '';

    $wrapper_style = $background_color ? ' style="background-color: ' . $background_color . ';"' : '';

    $output = '<div class="wp-block-global360blocks-two-column-cta"' . $wrapper_style . '>';
    $output .= '<div class="two-column-cta__inner">';
    $output .= '<div class="two-column-cta__content">';
    
    if ($heading) {
        $output .= '<h2 class="two-column-cta__heading">' . $heading . '</h2>';
    }
    
    $output .= '<div class="two-column-cta__body">';
    $output .= $content;
    $output .= '</div>';
    $output .= '</div>';
    
    $output .= '<div class="two-column-cta__button-wrapper">';
    if ($button_url) {
        $output .= '<a href="' . $button_url . '" class="two-column-cta__button">' . $button_text . '</a>';
    } else {
        $output .= '<span class="two-column-cta__button">' . $button_text . '</span>';
    }
    $output .= '</div>';
    
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Render callback for Two Column Slider block
function global360blocks_render_two_column_slider_block($attributes) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/two-column-slider' );

    $slides = !empty($attributes['slides']) ? $attributes['slides'] : [];
    $autoplay = !empty($attributes['autoplay']) ? $attributes['autoplay'] : true;
    $autoplay_speed = !empty($attributes['autoplaySpeed']) ? intval($attributes['autoplaySpeed']) : 5000;
    $show_dots = !empty($attributes['showDots']) ? $attributes['showDots'] : true;
    $show_arrows = !empty($attributes['showArrows']) ? $attributes['showArrows'] : true;
        if (empty($slides)) {
        return '';
    }
    
    $output = '<div class="wp-block-global360blocks-two-column-slider">';
    $output .= '<div class="two-column-slider-container">';
    $output .= '<div class="slider-wrapper">';
    
    if ($show_arrows) {
        $output .= '<button class="slider-nav prev" onclick="previousSlide(this)" aria-label="Previous slide"><span class="screen-reader-text">Previous slide</span></button>';
    }
    
    $output .= '<div class="slide-container" data-current-slide="0" data-autoplay="' . ($autoplay ? 'true' : 'false') . '">';
    $output .= '<div class="slide-track">';

    foreach ($slides as $index => $slide) {
    $heading       = !empty($slide['heading']) ? wp_kses_post($slide['heading']) : '';
    $text          = !empty($slide['text']) ? wp_kses_post($slide['text']) : '';
    $image_url     = !empty($slide['imageUrl']) ? esc_url($slide['imageUrl']) : '';
    $background    = !empty($slide['contentBackground']) ? sanitize_text_field($slide['contentBackground']) : '';
        $heading_attr  = !empty($slide['heading']) ? esc_attr( wp_strip_all_tags( $slide['heading'] ) ) : '';
		
        $active_class      = $index === 0 ? 'active' : '';
        $image_state_class = $image_url ? 'has-image' : 'no-image';
    $content_style     = $background ? ' style="background-color: ' . esc_attr( $background ) . ';"' : '';

    $output .= '<div class="slide ' . $active_class . ' ' . $image_state_class . '" data-slide="' . $index . '">';
    $output .= '<div class="slide-content"' . $content_style . '>';
        $output .= '<span class="slide-index">' . ($index + 1) . '</span>';
        if ($heading) {
            $output .= '<h2 class="slide-heading">' . $heading . '</h2>';
        }
        if ($text) {
            $output .= '<p class="slide-text">' . $text . '</p>';
        }
        $output .= '</div>';
        
        if ($image_url) {
            $output .= '<div class="slide-image">';
            $output .= '<img src="' . $image_url . '" alt="' . $heading_attr . '" />';
            $output .= '</div>';
        }
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '</div>';
    
    if ($show_arrows) {
        $output .= '<button class="slider-nav next" onclick="nextSlide(this)" aria-label="Next slide"><span class="screen-reader-text">Next slide</span></button>';
    }
    
    $output .= '</div>';
    
    if ($show_dots) {
        $output .= '<div class="slider-dots">';
        foreach ($slides as $index => $slide) {
            $active_class = $index === 0 ? 'active' : '';
            $output .= '<button class="dot ' . $active_class . '" onclick="goToSlide(this, ' . $index . ')" aria-label="Go to slide ' . ($index + 1) . '"></button>';
        }
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '</div>'; // Close .wp-block-global360blocks-two-column-slider
    
    return $output;
}

// Helper function to convert YouTube URLs to embed format
if (!function_exists('get_youtube_embed_url')) {
    function get_youtube_embed_url($url) {
        if (empty($url)) return '';
        
        $video_id = '';
        
        if (strpos($url, 'youtube.com/watch?v=') !== false) {
            $video_id = explode('v=', $url)[1];
            $video_id = explode('&', $video_id)[0];
        } elseif (strpos($url, 'youtu.be/') !== false) {
            $video_id = explode('youtu.be/', $url)[1];
            $video_id = explode('?', $video_id)[0];
        } elseif (strpos($url, 'youtube.com/embed/') !== false) {
            return $url; // Already an embed URL
        }
        
        return !empty($video_id) ? 'https://www.youtube.com/embed/' . $video_id : $url;
    }
}

// Helper function to check if URL is a YouTube URL
if (!function_exists('global360blocks_is_youtube_url')) {
    function global360blocks_is_youtube_url($url) {
        return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;
    }
}

// Render callback for Latest Articles block
function global360blocks_render_latest_articles_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/latest-articles' );

    $number_of_posts = isset($attributes['numberOfPosts']) ? (int) $attributes['numberOfPosts'] : 3;
    $show_excerpt = isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : true;
    $excerpt_length = isset($attributes['excerptLength']) ? (int) $attributes['excerptLength'] : 20;
    $columns = isset($attributes['columns']) ? (int) $attributes['columns'] : 3;
    
    // Query latest posts
    $posts = get_posts(array(
        'numberposts' => $number_of_posts,
        'post_status' => 'publish'
    ));
    
    if (empty($posts)) {
        return '<div class="latest-articles-block"><p>No articles found.</p></div>';
    }
    
    $output = '<div class="latest-articles-block" style="--columns: ' . $columns . ';">';
    $output .= '<div class="latest-articles-header">';
    $output .= '<h2>Our Latest Articles</h2>';
    $output .= '</div>';
    $output .= '<div class="latest-articles-grid">';
    
    foreach ($posts as $post) {
        $featured_image = get_the_post_thumbnail_url($post->ID, 'medium');
        $title = get_the_title($post->ID);
        $permalink = get_permalink($post->ID);
        
        $output .= '<article class="latest-article-item">';
        
        if ($featured_image) {
            $output .= '<div class="article-image">';
            $output .= '<a href="' . esc_url($permalink) . '">';
            $output .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr($title) . '">';
            $output .= '</a>';
            $output .= '</div>';
        }
        
        $output .= '<div class="article-content">';
        $output .= '<h3 class="article-title">';
        $output .= '<a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a>';
        $output .= '</h3>';
        
        if ($show_excerpt) {
            $excerpt = get_the_excerpt($post->ID);
            if (str_word_count($excerpt) > $excerpt_length) {
                $words = str_word_count($excerpt, 2);
                $excerpt = implode(' ', array_slice($words, 0, $excerpt_length)) . '...';
            }
            $output .= '<p class="article-excerpt">' . esc_html($excerpt) . '</p>';
        }
        
        $output .= '<div class="article-read-more">';
        $output .= '<a href="' . esc_url($permalink) . '" class="read-more-link">READ MORE →</a>';
        $output .= '</div>';
        
        $output .= '</div>';
        $output .= '</article>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

// Render callback for Video Two Column block
function global360blocks_render_video_two_column_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/video-two-column' );

    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $video_url = !empty($attributes['videoUrl']) ? esc_url($attributes['videoUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $legacy_body_text = !empty($attributes['bodyText']) ? wp_kses_post($attributes['bodyText']) : '';
    $video_title = !empty($attributes['videoTitle']) ? wp_kses_post($attributes['videoTitle']) : '';
    
    $output = '<div class="video-two-column-block">';
    $output .= '<div class="video-two-column-container">';
    
    // Left column - Video
    $output .= '<div class="video-two-column-video" style="display:flex;flex-direction:column;align-items:stretch;justify-content:flex-start;gap:16px;">';
    if ($video_title) {
        $output .= '<h2 class="video-two-column-video-title">' . $video_title . '</h2>';
    }
    if ( $video_url ) {
        $is_youtube          = global360blocks_is_youtube_url( $video_url );
        $youtube_id          = $is_youtube ? global360blocks_get_youtube_video_id( $video_url ) : '';
        $use_lite_embed      = apply_filters( 'global360blocks_video_two_column_use_lite_embed', true, $attributes );
        $can_use_lite_embed  = $use_lite_embed && $youtube_id;

        if ( $can_use_lite_embed ) {
            $embed_url     = global360blocks_get_youtube_embed_url( $video_url );
            $thumbnail_url = global360blocks_get_youtube_thumbnail_url( $youtube_id );
            $play_label    = $video_title ? sprintf( __( 'Play video: %s', 'global360blocks' ), wp_strip_all_tags( $video_title ) ) : __( 'Play video', 'global360blocks' );
            $iframe_title  = $video_title ? wp_strip_all_tags( $video_title ) : __( 'Embedded video', 'global360blocks' );

            $output .= '<div class="video-wrapper lite-yt" data-embed-url="' . esc_url( $embed_url ) . '" data-video-id="' . esc_attr( $youtube_id ) . '" data-title="' . esc_attr( $iframe_title ) . '">';
            if ( $thumbnail_url ) {
                $output .= '<img class="lite-yt-thumb" src="' . esc_url( $thumbnail_url ) . '" alt="" loading="lazy" decoding="async" />';
            }
            $output .= '<button type="button" class="lite-yt-play" aria-label="' . esc_attr( $play_label ) . '">';
            $output .= '<span class="lite-yt-play-icon" aria-hidden="true"></span>';
            $output .= '</button>';
            $output .= '</div>';
            $output .= '<noscript>';
            $output .= '<iframe src="' . esc_url( $embed_url ) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="youtube-video"></iframe>';
            $output .= '</noscript>';
        } elseif ( $is_youtube ) {
            $embed_url = global360blocks_get_youtube_embed_url( $video_url );
            $output   .= '<div class="video-wrapper">';
            $output   .= '<iframe src="' . esc_url( $embed_url ) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="youtube-video"></iframe>';
            $output   .= '</div>';
        } else {
            $output .= '<div class="video-wrapper">';
            $output .= '<video controls class="column-video">';
            $output .= '<source src="' . $video_url . '" type="video/mp4">';
            $output .= 'Your browser does not support the video tag.';
            $output .= '</video>';
            $output .= '</div>';
        }
    }
    $output .= '</div>';
    
    // Right column - Content
    $output .= '<div class="video-two-column-content">';
    if ($heading) {
        $output .= '<h2 class="video-two-column-heading">' . $heading . '</h2>';
    }

    $body_html = '';
    if (!empty($content)) {
        $body_html = $content;
    } elseif (!empty($legacy_body_text)) {
        $body_html = $legacy_body_text;
    }

    if ($body_html) {
        $output .= '<div class="video-two-column-body">' . $body_html . '</div>';
    }
    
    // Assessment button
    if (!empty($assess_id)) {
        global360blocks_enqueue_global_shared_style();

        $output .= '<div class="video-two-column-button">';
        $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

// Render callback for Find Doctor block
function global360blocks_render_find_doctor_block($attributes) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/find-doctor' );

    $image_url = isset($attributes['imageUrl']) ? $attributes['imageUrl'] : '';
    $image_id = isset($attributes['imageId']) ? $attributes['imageId'] : 0;
    $heading = isset($attributes['heading']) ? $attributes['heading'] : '';
    $body_text = isset($attributes['bodyText']) ? $attributes['bodyText'] : '';

    $output = '<div class="find-doctor-block">';
    $output .= '<div class="find-doctor-container">';
    
    // Image column
    $output .= '<div class="find-doctor-image">';
    if ($image_url) {
        $alt_text = $heading ? esc_attr($heading) : 'Find Doctor Image';
        $output .= '<div class="image-wrapper">';
        $output .= '<img src="' . esc_url($image_url) . '" alt="' . $alt_text . '" />';
        $output .= '</div>';
    }
    $output .= '</div>';
    
    // Content column
    $output .= '<div class="find-doctor-content">';
    if ($heading) {
        $output .= '<h2 class="find-doctor-heading">' . wp_kses_post($heading) . '</h2>';
    }
    if ($body_text) {
        $output .= '<p class="find-doctor-body">' . wp_kses_post($body_text) . '</p>';
    }
    $output .= '<div class="find-doctor-button">';
    $output .= '<a href="/find-a-doctor/" class="btn btn_global">Find a Doctor Now</a>';
    $output .= '</div>';
    $output .= '</div>';
    
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}



// Info Cards block now uses render.php file - old hardcoded function removed

// Register custom block category for 360 Blocks (before blocks)
add_filter('block_categories_all', function($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => '360-blocks',
                'title' => __('360 Blocks', '360-global-blocks'),
            ),
        )
    );
}, 10, 2);

// Include symptoms AI render functions
// require_once plugin_dir_path(__FILE__) . 'blocks/symptoms-ai/render.php';

// Include page title hero render functions
require_once plugin_dir_path(__FILE__) . 'blocks/page-title-hero/render.php';

// Block category is already registered above - removed duplicate

// Register block
function global360blocks_register_blocks() {
    register_block_type(
        __DIR__ . '/blocks/simple-hero',
        array(
            'render_callback' => 'global360blocks_render_simple_hero_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/full-hero',
        array(
            'render_callback' => 'global360blocks_render_full_hero_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/cta',
        array(
            'render_callback' => 'global360blocks_render_cta_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/two-column',
        array(
            'render_callback' => 'global360blocks_render_two_column_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/two-column-text',
        array(
            'render_callback' => 'global360blocks_render_two_column_text_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/video-two-column',
        array(
            'render_callback' => 'global360blocks_render_video_two_column_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/latest-articles',
        array(
            'render_callback' => 'global360blocks_render_latest_articles_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/find-doctor',
        array(
            'render_callback' => 'global360blocks_render_find_doctor_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/info-cards',
        array(
            'render_callback' => function( $attributes, $content, $block ) {
                ob_start();
                include __DIR__ . '/blocks/info-cards/render.php';
                return ob_get_clean();
            },
        )
    );

    register_block_type(
        __DIR__ . '/blocks/rich-text',
        array(
            'render_callback' => function( $attributes, $content, $block ) {
                ob_start();
                include __DIR__ . '/blocks/rich-text/render.php';
                return ob_get_clean();
            },
        )
    );

    register_block_type(
        __DIR__ . '/blocks/popular-practices',
        array(
            'render_callback' => 'global360blocks_render_popular_practices_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/two-column-slider',
        array(
            'render_callback' => 'global360blocks_render_two_column_slider_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/two-column-cta',
        array(
            'render_callback' => 'global360blocks_render_two_column_cta_block',
        )
    );

    register_block_type(
        __DIR__ . '/blocks/page-title-hero',
        array(
            'render_callback' => 'global360blocks_render_page_title_hero_block',
        )
    );
}

// Render callback for CTA block
function global360blocks_render_cta_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/cta' );

    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_url = !empty($attributes['imageUrl']) ? esc_url($attributes['imageUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';

    global360blocks_enqueue_global_shared_style();
    
    $output = '<div class="cta-block">';
    $output .= '<div class="cta-container" style="background-image: url(' . $image_url . ');">';
    $output .= '<div class="cta-content">';
    if ($heading) {
        $output .= '<h2 class="cta-heading">' . $heading . '</h2>';
    }
    $output .= '<div class="cta-button">';
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
    $output .= '</div>';
    $output .= '</div></div></div>';
    return $output;
}

if ( ! function_exists( 'global360blocks_filter_two_column_body' ) ) {
    function global360blocks_filter_two_column_body( $html, $heading = '' ) {
        if ( empty( $html ) ) {
            return $html;
        }

        $html = preg_replace( '/<img[^>]*>/i', '', $html );
        $html = preg_replace( '/Replace\s*Image\s*Remove\s*Image/i', '', $html );
        $html = preg_replace( '/Replace\s*Image/i', '', $html );
        $html = preg_replace( '/Remove\s*Image/i', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*(Take\s+Risk\s+Assessment\s+Now)\s*<\/p>/i', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*(Body\s+content)\s*<\/p>/i', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*(?:&nbsp;|\xc2\xa0|\s)*<\/p>/i', '', $html );

        if ( ! empty( $heading ) ) {
            $quoted_heading = preg_quote( wp_strip_all_tags( $heading ), '/' );
            $heading_pattern = '/<h[1-6][^>]*>\s*' . $quoted_heading . '\s*<\/h[1-6]>/i';
            $html = preg_replace( $heading_pattern, '', $html );
        }

        return $html;
    }
}

// Render callback for Two Column block
function global360blocks_render_two_column_block( $attributes, $content, $block = null ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/two-column' );
    global360blocks_enqueue_global_shared_style();

    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_url = !empty($attributes['imageUrl']) ? esc_url($attributes['imageUrl']) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $legacy_body_text = !empty($attributes['bodyText']) ? wp_kses_post($attributes['bodyText']) : '';
    $layout          = isset( $attributes['layout'] ) && 'image-right' === $attributes['layout'] ? 'image-right' : 'image-left';
    $background_color = ! empty( $attributes['backgroundColor'] ) ? sanitize_hex_color( $attributes['backgroundColor'] ) : '';
    $heading_color    = ! empty( $attributes['headingColor'] ) ? sanitize_hex_color( $attributes['headingColor'] ) : '';

    $background_style = $background_color ? 'background-color: ' . $background_color . ';' : '';
    
    // Use block wrapper attributes so declared supports (e.g., align) are applied
    $wrapper_args = array( 'class' => 'two-column-block' );
    if ( $background_style ) {
        $wrapper_args['style'] = $background_style;
    }

    $wrapper_attributes = function_exists('get_block_wrapper_attributes')
        ? get_block_wrapper_attributes( $wrapper_args )
        : 'class="two-column-block"' . ( $background_style ? ' style="' . esc_attr( $background_style ) . '"' : '' );
    $output = '<div ' . $wrapper_attributes . '>';
    $output .= '<div class="two-column-container layout-' . esc_attr( $layout ) . '">';

    $image_column = '<div class="two-column-image">';
    if ( $image_url ) {
        $image_column .= '<img src="' . $image_url . '" alt="" class="column-image" />';
    }
    $image_column .= '</div>';

    $content_column = '<div class="two-column-content"' . ( $background_style ? ' style="' . esc_attr( $background_style ) . '"' : '' ) . '>';
    $content_column .= '<div class="two-column-content-inner">';
    if ( $heading ) {
        $heading_style_attr = $heading_color ? ' style="color: ' . esc_attr( $heading_color ) . ';"' : '';
        $content_column .= '<h2 class="two-column-heading"' . $heading_style_attr . '>' . $heading . '</h2>';
    }
    $body_html = '';
    if (is_string($content) && trim($content) !== '') {
        $body_html = trim($content);
    } elseif (is_object($block) && property_exists($block, 'inner_blocks') && !empty($block->inner_blocks)) {
        foreach ($block->inner_blocks as $inner_block) {
            if (is_object($inner_block) && method_exists($inner_block, 'render')) {
                $body_html .= $inner_block->render();
            }
        }
    } elseif (!empty($legacy_body_text)) {
        $body_html = wpautop($legacy_body_text);
    }

    if ($body_html) {
        $body_html = global360blocks_filter_two_column_body( $body_html, $heading );
        $content_column .= '<div class="two-column-body">' . $body_html . '</div>';
    }

    $content_column .= '<div class="two-column-button">';
    $content_column .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
    $content_column .= '</div>';
    $content_column .= '</div>';
    $content_column .= '</div>';

    $output .= $image_column . $content_column;

    $output .= '</div></div>';
    return $output;
}

function global360blocks_render_two_column_text_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/two-column-text' );

    return $content;
}

// Render callback for Full Page Hero block
function global360blocks_render_full_hero_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/full-hero' );

    // Get Assessment ID from theme settings (360_global_settings array)
    $global_settings = get_option('360_global_settings', []);
    $assess_id = isset($global_settings['assessment_id']) ? $global_settings['assessment_id'] : '';
    
    $image_id    = isset( $attributes['bgImageId'] ) ? absint( $attributes['bgImageId'] ) : 0;
    $raw_image   = '';
    $image_width = 0;
    $image_height = 0;
    $image_alt   = '';

    if ( $image_id ) {
        $image_details = wp_get_attachment_image_src( $image_id, 'full' );

        if ( is_array( $image_details ) && ! empty( $image_details[0] ) ) {
            $raw_image   = $image_details[0];
            $image_width = isset( $image_details[1] ) ? (int) $image_details[1] : 0;
            $image_height = isset( $image_details[2] ) ? (int) $image_details[2] : 0;
        }

        $maybe_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
        if ( is_string( $maybe_alt ) ) {
            $image_alt = wp_strip_all_tags( $maybe_alt );
        }
    }

    if ( ! $raw_image && ! empty( $attributes['bgImageUrl'] ) ) {
        $raw_image = $attributes['bgImageUrl'];
    }

    $image_url = $raw_image ? esc_url( $raw_image ) : '';
    $heading = !empty($attributes['heading']) ? wp_kses_post($attributes['heading']) : '';
    $subheading = !empty($attributes['subheading']) ? wp_kses_post($attributes['subheading']) : '';

    $heading = global360blocks_wrap_trademark_symbols( $heading );
    $subheading = global360blocks_wrap_trademark_symbols( $subheading );
    $output = '<div class="full-hero-block">';
    if ( $image_url ) {
        $output .= '<div class="full-hero-media">';

        $image_html = wp_get_attachment_image(
            $image_id,
            'full',
            false,
            array(
                'class'         => 'full-hero-image',
                'fetchpriority' => 'high',
                'loading'       => 'eager',
                'decoding'      => 'async',
            )
        );

        if ( $image_html ) {
            $output .= $image_html;
        } else {
            // Fallback for when wp_get_attachment_image fails but we have a URL
            $output .= sprintf( '<img src="%s" alt="%s" class="full-hero-image" fetchpriority="high" loading="eager" decoding="async" />', esc_url( $image_url ), esc_attr( $image_alt ) );
        }
        $output .= '</div>';
    }
    $output .= '<div class="full-hero-content">';
    if ($heading) {
        $output .= '<h1 class="full-hero-heading">' . $heading . '</h1>';
    }
    if ($subheading) {
        $output .= '<p class="full-hero-subheading">' . $subheading . '</p>';
    }
    global360blocks_enqueue_global_shared_style();
    $output .= '<pr360-questionnaire url="wss://app.patientreach360.com/socket" site-id="' . esc_attr($assess_id) . '">Take Risk Assessment Now</pr360-questionnaire>';
    $output .= '</div></div>';
    return $output;
}

// Render callback for Simple Hero block
function global360blocks_render_simple_hero_block( $attributes, $content ) {
    global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/test-hero' );

    $page_title = get_the_title();
    
    $output = '<div class="wp-block-global360blocks-simple-hero">';
    $output .= '<div class="simple-hero-content">';
    $output .= '<h1>' . esc_html($page_title) . '</h1>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

add_action( 'init', 'global360blocks_register_blocks' );

/**
 * Define the frontend asset manifest for block-level loading.
 *
 * @return array<string, array<string, array<string, string>>>
 */
function global360blocks_get_frontend_asset_manifest() {
    static $manifest = null;

    if ( null !== $manifest ) {
        return $manifest;
    }

    $manifest = array(
        'global360blocks/test-hero'         => array(
            'style' => array(
                'handle' => 'global360blocks-simple-hero-style-frontend',
                'file'   => 'blocks/simple-hero/build/style-index.css',
            ),
        ),
        'global360blocks/full-hero'         => array(
            'style' => array(
                'handle' => 'global360blocks-full-hero-style-frontend',
                'file'   => 'blocks/full-hero/build/style-index.css',
            ),
        ),
        'global360blocks/cta'               => array(
            'style' => array(
                'handle' => 'global360blocks-cta-style-frontend',
                'file'   => 'blocks/cta/build/style-index.css',
            ),
        ),
        'global360blocks/two-column'        => array(
            'style' => array(
                'handle' => 'global360blocks-two-column-style-frontend',
                'file'   => 'blocks/two-column/build/style-index.css',
            ),
        ),
        'global360blocks/two-column-cta'    => array(
            'style' => array(
                'handle' => 'global360blocks-two-column-cta-style-frontend',
                'file'   => 'blocks/two-column-cta/build/style-index.css',
            ),
        ),
        'global360blocks/two-column-text'   => array(
            'style' => array(
                'handle' => 'global360blocks-two-column-text-style-frontend',
                'file'   => 'blocks/two-column-text/build/style-index.css',
            ),
        ),
        'global360blocks/video-two-column'  => array(
            'style' => array(
                'handle' => 'global360blocks-video-two-column-style-frontend',
                'file'   => 'blocks/video-two-column/build/style-index.css',
            ),
            'script' => array(
                'handle' => 'global360blocks-video-two-column-lite',
                'file'   => 'blocks/video-two-column/frontend.js',
            ),
        ),
        'global360blocks/rich-text'         => array(
            'style' => array(
                'handle' => 'global360blocks-rich-text-style-frontend',
                'file'   => 'blocks/rich-text/build/style-index.css',
            ),
        ),
        'global360blocks/find-doctor'       => array(
            'style' => array(
                'handle' => 'find-doctor-block-css',
                'file'   => 'blocks/find-doctor/build/style-index.css',
            ),
        ),
        'global360blocks/latest-articles'   => array(
            'style' => array(
                'handle' => 'latest-articles-block-css',
                'file'   => 'blocks/latest-articles/build/style-index.css',
            ),
        ),
        'global360blocks/info-cards'        => array(
            'style' => array(
                'handle' => 'info-cards-block-css',
                'file'   => 'blocks/info-cards/build/style-index.css',
            ),
            'script' => array(
                'handle' => 'info-cards-block-frontend',
                'file'   => 'blocks/info-cards/build/view.js',
                'asset'  => 'blocks/info-cards/build/view.asset.php',
            ),
        ),
        'global360blocks/popular-practices' => array(
            'style' => array(
                'handle' => 'popular-practices-block-css',
                'file'   => 'blocks/popular-practices/build/style-index.css',
            ),
        ),
        'global360blocks/page-title-hero'   => array(
            'style' => array(
                'handle' => 'global360blocks-page-title-hero-style-frontend',
                'file'   => 'blocks/page-title-hero/build/style-index.css',
            ),
        ),
        'global360blocks/two-column-slider'   => array(
            'style' => array(
                'handle' => 'global360blocks-two-column-slider-style-frontend',
                'file'   => 'blocks/two-column-slider/build/style-index.css',
            ),
            'script' => array(
                'handle' => 'global360blocks-two-column-slider-view',
                'file'   => 'blocks/two-column-slider/build/view.js',
                'asset'  => 'blocks/two-column-slider/build/view.asset.php',
            ),
        ),
    );

    return $manifest;
}

/**
 * Register and enqueue a stylesheet from the plugin directory.
 *
 * @param string $handle Asset handle.
 * @param string $relative_file Relative path within the plugin.
 * @param array  $deps Optional dependencies.
 */
function global360blocks_enqueue_style_asset( $handle, $relative_file, $deps = array() ) {
    static $registered_styles = array();

    if ( isset( $registered_styles[ $handle ] ) ) {
        if ( true === $registered_styles[ $handle ] ) {
            wp_enqueue_style( $handle );
        }
        return;
    }

    $absolute_path = plugin_dir_path( __FILE__ ) . $relative_file;

    if ( ! file_exists( $absolute_path ) ) {
        $registered_styles[ $handle ] = false;
        return;
    }

    wp_register_style(
        $handle,
        plugins_url( $relative_file, __FILE__ ),
        $deps,
        filemtime( $absolute_path )
    );

    wp_enqueue_style( $handle );
    $registered_styles[ $handle ] = true;
}

/**
 * Register and enqueue a script from the plugin directory.
 *
 * @param string      $handle      Script handle.
 * @param string      $relative_file Relative path to the script file.
 * @param string|null $asset_file  Optional path to the generated asset metadata.
 */
function global360blocks_enqueue_script_asset( $handle, $relative_file, $asset_file = null ) {
    static $registered_scripts = array();

    if ( isset( $registered_scripts[ $handle ] ) ) {
        if ( true === $registered_scripts[ $handle ] ) {
            wp_enqueue_script( $handle );
        }
        return;
    }

    $absolute_path = plugin_dir_path( __FILE__ ) . $relative_file;

    if ( ! file_exists( $absolute_path ) ) {
        $registered_scripts[ $handle ] = false;
        return;
    }

    $deps    = array();
    $version = filemtime( $absolute_path );

    if ( $asset_file ) {
        $asset_absolute = plugin_dir_path( __FILE__ ) . $asset_file;
        if ( file_exists( $asset_absolute ) ) {
            $asset_data = include $asset_absolute;
            if ( is_array( $asset_data ) ) {
                $deps    = isset( $asset_data['dependencies'] ) ? $asset_data['dependencies'] : $deps;
                $version = isset( $asset_data['version'] ) ? $asset_data['version'] : $version;
            }
        }
    }

    wp_register_script(
        $handle,
        plugins_url( $relative_file, __FILE__ ),
        $deps,
        $version,
        true
    );

    wp_enqueue_script( $handle );
    $registered_scripts[ $handle ] = true;
}

/**
 * Enqueue both style and script assets for a given block when it renders.
 *
 * @param string $block_name Block name from the manifest.
 */
function global360blocks_enqueue_block_assets_from_manifest( $block_name ) {
    $manifest = global360blocks_get_frontend_asset_manifest();

    if ( ! isset( $manifest[ $block_name ] ) ) {
        return;
    }

    $definition = $manifest[ $block_name ];

    if ( isset( $definition['style'] ) ) {
        $style = $definition['style'];
        $deps  = isset( $style['deps'] ) ? $style['deps'] : array();
        global360blocks_enqueue_style_asset( $style['handle'], $style['file'], $deps );
    }

    if ( isset( $definition['script'] ) ) {
        $script     = $definition['script'];
        $asset_file = isset( $script['asset'] ) ? $script['asset'] : null;
        global360blocks_enqueue_script_asset( $script['handle'], $script['file'], $asset_file );
    }
}

/**
 * Force-load asset bundles for templates that rely on plugin CSS without blocks.
 */
function global360blocks_enqueue_forced_assets() {
    if ( is_admin() ) {
        return;
    }

    $block_names = array();

    $posts_page_id   = (int) get_option( 'page_for_posts' );
    $is_posts_page   = $posts_page_id > 0 && is_page( $posts_page_id );
    $is_blog_context = is_home() || is_post_type_archive( 'post' ) || $is_posts_page;

    if ( ! $is_blog_context ) {
        $queried = get_queried_object();
        if ( $queried instanceof WP_Post && 'page' === $queried->post_type ) {
            $slug = $queried->post_name;
            if ( in_array( $slug, array( 'blog', 'news' ), true ) ) {
                $is_blog_context = true;
            }
        }
    }

    if ( $is_blog_context ) {
        $block_names[] = 'global360blocks/latest-articles';
    }

    $block_names = apply_filters( 'global360blocks_forced_asset_blocks', $block_names );

    if ( empty( $block_names ) ) {
        return;
    }

    foreach ( array_unique( $block_names ) as $block_name ) {
        global360blocks_enqueue_block_assets_from_manifest( $block_name );
    }
}
add_action( 'wp_enqueue_scripts', 'global360blocks_enqueue_forced_assets', 30 );

/**
 * Preload critical hero block assets before markup renders to avoid FOUC.
 */
function global360blocks_preload_above_fold_assets() {
    if ( is_admin() ) {
        return;
    }

    if ( ! function_exists( 'has_block' ) ) {
        return;
    }

    $post_id = get_queried_object_id();

    if ( ! $post_id ) {
        return;
    }

    $critical_blocks = array(
        'global360blocks/test-hero',
        'global360blocks/full-hero',
    );

    foreach ( $critical_blocks as $block_name ) {
        if ( has_block( $block_name, $post_id ) ) {
            global360blocks_enqueue_block_assets_from_manifest( $block_name );
        }
    }
}
add_action( 'wp', 'global360blocks_preload_above_fold_assets', 5 );

?>
