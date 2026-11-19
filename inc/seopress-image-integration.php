<?php
/**
 * SEOPress social-image fallbacks for image-bearing Global Blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Determine whether SEOPress is active.
 *
 * @return bool
 */
function global360blocks_is_seopress_active() {
    return defined( 'SEOPRESS_VERSION' ) || function_exists( 'seopress_get_service' );
}

/**
 * Determine whether a post can use a Global Blocks social-image fallback.
 *
 * @param WP_Post $post Post object.
 * @return bool
 */
function global360blocks_post_supports_seopress_images( $post ) {
    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    $allowed_types = apply_filters( 'global360blocks_seopress_supported_post_types', array( 'post', 'page' ) );
    if ( empty( $allowed_types ) ) {
        return true;
    }

    return in_array( $post->post_type, $allowed_types, true );
}

/**
 * Check whether a SEOPress filter value already contains a usable image.
 *
 * SEOPress 10.x can pass either a URL or complete legacy meta markup through
 * these filters, so both representations must be supported.
 *
 * @param mixed $value Current filter value.
 * @return bool
 */
function global360blocks_seopress_value_has_image( $value ) {
    if ( ! is_string( $value ) || '' === trim( $value ) ) {
        return false;
    }

    if ( false === stripos( $value, '<meta' ) ) {
        return true;
    }

    if ( ! preg_match( '/\bcontent\s*=\s*(["\'])(.*?)\1/i', $value, $matches ) ) {
        return false;
    }

    return '' !== trim( html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' ) );
}

/**
 * Supply the primary Global Blocks image when SEOPress has no social image.
 *
 * Returning complete meta markup is compatible with both the legacy SEOPress
 * output path and its current service layer, which extracts the content URL.
 *
 * @param mixed  $value    Current SEOPress filter value.
 * @param string $property Meta property/name.
 * @return mixed
 */
function global360blocks_filter_seopress_social_image( $value, $property ) {
    if ( global360blocks_seopress_value_has_image( $value ) || ! is_singular() ) {
        return $value;
    }

    $post = global360blocks_resolve_post_object( null );
    if ( ! $post || ! global360blocks_post_supports_seopress_images( $post ) ) {
        return $value;
    }

    $image = global360blocks_get_primary_block_image( $post );
    if ( ! $image || empty( $image['url'] ) ) {
        return $value;
    }

    $attribute = 'twitter:image' === $property ? 'name' : 'property';

    return sprintf(
        '<meta %1$s="%2$s" content="%3$s">',
        $attribute,
        esc_attr( $property ),
        esc_url( $image['url'] )
    );
}

/**
 * Filter SEOPress Open Graph image output.
 *
 * @param mixed $value Current output or URL.
 * @return mixed
 */
function global360blocks_filter_seopress_opengraph_image( $value ) {
    return global360blocks_filter_seopress_social_image( $value, 'og:image' );
}

/**
 * Filter SEOPress Twitter image output.
 *
 * @param mixed $value Current output or URL.
 * @return mixed
 */
function global360blocks_filter_seopress_twitter_image( $value ) {
    return global360blocks_filter_seopress_social_image( $value, 'twitter:image' );
}

/**
 * Register SEOPress-facing integrations.
 */
function global360blocks_register_seopress_integrations() {
    if ( ! global360blocks_is_seopress_active() ) {
        return;
    }

    add_filter( 'seopress_social_og_thumb', 'global360blocks_filter_seopress_opengraph_image', 20 );
    add_filter( 'seopress_social_twitter_card_thumb', 'global360blocks_filter_seopress_twitter_image', 20 );
}
add_action( 'init', 'global360blocks_register_seopress_integrations', 20 );
