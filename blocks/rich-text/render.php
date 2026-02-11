<?php
/**
 * Rich Text Block Template
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

global360blocks_enqueue_block_assets_from_manifest(
    'global360blocks/rich-text',
    array( 'style' => false )
);

// Extract attributes
$content = $attributes['content'] ?? '';
$text_align = $attributes['textAlign'] ?? 'left';
$max_width = $attributes['maxWidth'] ?? 'none';
$show_drop_cap = $attributes['showDropCap'] ?? false;
$text_color = $attributes['textColor'] ?? '';
$background_color = $attributes['backgroundColor'] ?? '';
$custom_text_color = $attributes['style']['color']['text'] ?? '';
$custom_background_color = $attributes['style']['color']['background'] ?? '';

// Debug: Let's see what we're getting
error_log('Rich Text Block Debug:');
error_log('Content: ' . $content);
error_log('Attributes: ' . print_r($attributes, true));

// Build classes
$classes = [
    'wp-block-global360blocks-rich-text',
    'text-align-' . $text_align
];

if ($show_drop_cap) {
    $classes[] = 'has-drop-cap';
}

// Add color classes
if ($text_color) {
    $classes[] = 'has-' . $text_color . '-color';
    $classes[] = 'has-text-color';
}

if ($background_color) {
    $classes[] = 'has-' . $background_color . '-background-color';
    $classes[] = 'has-background';
}

if ($custom_text_color || $custom_background_color) {
    $classes[] = 'has-text-color';
}

// Build styles
$styles = [];

if ($max_width !== 'none') {
    $styles[] = 'max-width: ' . esc_attr($max_width);
    $styles[] = 'margin-left: auto';
    $styles[] = 'margin-right: auto';
}

if ($custom_text_color) {
    $styles[] = 'color: ' . esc_attr($custom_text_color);
}

if ($custom_background_color) {
    $styles[] = 'background-color: ' . esc_attr($custom_background_color);
}

// Build wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => implode(' ', $classes),
    'style' => !empty($styles) ? implode('; ', $styles) : null
]);

// Content classes for drop cap
$content_classes = ['rich-text-content'];
if ($show_drop_cap) {
    $content_classes[] = 'has-drop-cap';
}

// Always render the wrapper, even if content is empty
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="<?php echo esc_attr(implode(' ', $content_classes)); ?>">
        <?php 
        if (!empty($content)) {
            echo wp_kses_post($content);
        } else {
            echo '<p>No content yet - add some text in the editor!</p>';
        }
        ?>
    </div>
</div>
