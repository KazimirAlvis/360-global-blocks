<?php
/**
 * Render callback for Page Title Hero block
 */
function global360blocks_render_page_title_hero_block($attributes) {
    global360blocks_enqueue_block_assets_from_manifest(
        'global360blocks/page-title-hero',
        array( 'style' => false )
    );

    $title = isset($attributes['title']) ? esc_html($attributes['title']) : '';
    $subtitle = isset($attributes['subtitle']) ? esc_html($attributes['subtitle']) : '';
    $background_image = isset($attributes['background_image']) ? esc_url($attributes['background_image']) : '';
    $background_overlay = isset($attributes['background_overlay']) ? $attributes['background_overlay'] : true;
    $text_alignment = isset($attributes['text_alignment']) ? esc_attr($attributes['text_alignment']) : 'center';
    
    // Use dynamic page title if no title is set
    if (empty($title)) {
        $title = get_the_title();
    }
    
    // Return empty if still no title
    if (empty($title)) {
        return '';
    }
    
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => 'wp-block-global360blocks-page-title-hero'
    ));
    
    $hero_style = '';
    if (!empty($background_image)) {
        $hero_style = sprintf(
            'background-image: url(%s); background-size: cover; background-position: center;',
            $background_image
        );
    }
    
    $output = '<div ' . $wrapper_attributes . '>';
    $output .= '<div class="sm_hero text-' . $text_alignment . '"';
    if (!empty($hero_style)) {
        $output .= ' style="' . $hero_style . '"';
    }
    $output .= '>';
    
    // Add overlay if enabled and background image exists
    if ($background_overlay && !empty($background_image)) {
        $output .= '<div class="hero-overlay"></div>';
    }
    
    $output .= '<div class="hero-content">';
    $output .= '<h1 class="hero-title">' . $title . '</h1>';
    
    if (!empty($subtitle)) {
        $output .= '<p class="hero-subtitle">' . $subtitle . '</p>';
    }
    
    $output .= '</div>'; // hero-content
    $output .= '</div>'; // sm_hero
    $output .= '</div>'; // wrapper
    
    return $output;
}
