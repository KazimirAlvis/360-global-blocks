<?php

define( 'ABSPATH', __DIR__ . '/' );
define( 'SEOPRESS_VERSION', '10.4' );

class WP_Post {
    public $ID;
    public $post_type;

    public function __construct( $id, $post_type ) {
        $this->ID        = $id;
        $this->post_type = $post_type;
    }
}

$test_post      = new WP_Post( 42, 'page' );
$test_singular  = true;
$test_image     = array( 'url' => 'https://example.com/block-image.jpg' );
$test_filters   = array();

function add_action() {}
function add_filter( $hook, $callback ) { global $test_filters; $test_filters[ $hook ] = $callback; }
function apply_filters( $hook, $value ) { return $value; }
function is_singular() { global $test_singular; return $test_singular; }
function global360blocks_resolve_post_object() { global $test_post; return $test_post; }
function global360blocks_get_primary_block_image() { global $test_image; return $test_image; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return filter_var( $value, FILTER_SANITIZE_URL ); }
function seopress_get_service() {}

function seopress_image_expect( $condition, $message ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: $message\n" );
        exit( 1 );
    }
    echo "PASS: $message\n";
}

require dirname( __DIR__ ) . '/inc/seopress-image-integration.php';

global360blocks_register_seopress_integrations();
seopress_image_expect( isset( $test_filters['seopress_social_og_thumb'] ), 'Open Graph integration registers with SEOPress' );
seopress_image_expect( isset( $test_filters['seopress_social_twitter_card_thumb'] ), 'Twitter integration registers with SEOPress' );

$existing_url = 'https://example.com/explicit.jpg';
seopress_image_expect( $existing_url === global360blocks_filter_seopress_opengraph_image( $existing_url ), 'explicit SEOPress image is preserved' );

$existing_markup = '<meta property="og:image" content="https://example.com/default.jpg">';
seopress_image_expect( $existing_markup === global360blocks_filter_seopress_opengraph_image( $existing_markup ), 'existing SEOPress meta markup is preserved' );

$og_fallback = global360blocks_filter_seopress_opengraph_image( '' );
seopress_image_expect( false !== strpos( $og_fallback, 'property="og:image"' ) && false !== strpos( $og_fallback, $test_image['url'] ), 'empty Open Graph image receives the primary block image' );

$twitter_fallback = global360blocks_filter_seopress_twitter_image( '<meta name="twitter:image" content="">' );
seopress_image_expect( false !== strpos( $twitter_fallback, 'name="twitter:image"' ) && false !== strpos( $twitter_fallback, $test_image['url'] ), 'empty legacy Twitter markup receives the primary block image' );

$test_singular = false;
seopress_image_expect( '' === global360blocks_filter_seopress_opengraph_image( '' ), 'non-singular requests remain unchanged' );
$test_singular = true;

$test_post = new WP_Post( 43, 'clinic' );
seopress_image_expect( '' === global360blocks_filter_seopress_opengraph_image( '' ), 'unsupported post types remain unchanged' );

$test_post  = new WP_Post( 44, 'page' );
$test_image = null;
seopress_image_expect( '' === global360blocks_filter_seopress_opengraph_image( '' ), 'posts without a collected block image remain unchanged' );

echo "Global Blocks SEOPress image tests passed.\n";
