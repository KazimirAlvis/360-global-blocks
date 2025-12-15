<?php
/**
 * Info Cards Block Render
 */

if (!defined('ABSPATH')) {
    exit;
}

global360blocks_enqueue_block_assets_from_manifest('global360blocks/info-cards');

// Debug: Check if we're in the render file
error_log('Info Cards render.php: Starting render');

// Get Health Icons loader
if (!class_exists('HealthIconsLoader')) {
    error_log('Info Cards render.php: HealthIconsLoader class not found');
    return '<div class="wp-block-global360blocks-info-cards"><p>Error: HealthIconsLoader not available</p></div>';
}

$health_icons_loader = HealthIconsLoader::getInstance();

// Get block attributes
$main_title = isset($attributes['mainTitle']) ? $attributes['mainTitle'] : 'Why Choose Us';
$cards = isset($attributes['cards']) ? $attributes['cards'] : array(
    array(
        'icon' => 'devices/stethoscope',
        'title' => 'Expert Care',
        'text' => 'Our experienced medical professionals provide top-quality healthcare services.'
    ),
    array(
        'icon' => 'body/heart_organ',
        'title' => 'Compassionate Service',
        'text' => 'We care about your wellbeing and provide personalized attention to every patient.'
    ),
    array(
        'icon' => 'people/doctor',
        'title' => 'Professional Team',
        'text' => 'Available 24/7 for emergency situations with rapid response capabilities.'
    )
);

// Helper function to clean SVG for CSS
if (!function_exists('clean_svg_for_css')) {
    function clean_svg_for_css($svg_content) {
        if (empty($svg_content)) return $svg_content;
        
        // Remove fill and stroke attributes except "none"
        $cleaned_svg = preg_replace('/fill="(?!none)[^"]*"/', '', $svg_content);
        $cleaned_svg = preg_replace('/stroke="(?!none)[^"]*"/', '', $cleaned_svg);
        
        // Add currentColor and CSS class to the root SVG element
        $cleaned_svg = preg_replace(
            '/<svg([^>]*)>/',
            '<svg$1 class="info-card-icon" fill="currentColor">',
            $cleaned_svg,
            1
        );
        
        return $cleaned_svg;
    }
}

// Generate CSS classes
$css_classes = array(
    'wp-block-global360blocks-info-cards'
);

if (isset($attributes['className'])) {
    $css_classes[] = $attributes['className'];
}

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => implode(' ', $css_classes)
));

// Render the block
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="info-cards-container">
        <?php if (!empty($main_title)): ?>
            <h2 class="info-cards-main-title"><?php echo esc_html($main_title); ?></h2>
        <?php endif; ?>
        
        <div class="info-cards-grid">
            <?php foreach ($cards as $card): ?>
                <?php 
                $icon_key = isset($card['icon']) ? trim((string) $card['icon']) : '';
                $card_title = isset($card['title']) ? $card['title'] : '';
                $card_text = isset($card['text']) ? $card['text'] : '';
                
                $svg_content = '';

                // Get Health Icon SVG content (skip entirely when icon is unset / "None")
                if ('' !== $icon_key) {
                    $svg_content = $health_icons_loader->getIcon($icon_key);
                }
                
                // Clean SVG for CSS styling
                if ($svg_content) {
                    $svg_content = clean_svg_for_css($svg_content);
                }
                
                // Fallback if icon not found (but only when an icon was requested)
                if ('' !== $icon_key && !$svg_content) {
                    $svg_content = '<svg viewBox="0 0 48 48" fill="currentColor" class="info-card-icon"><circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="2"/><text x="24" y="30" text-anchor="middle" font-size="12" fill="currentColor">?</text></svg>';
                }
                ?>
                <div class="info-card">
                    <?php if (!empty($svg_content)): ?>
                        <div class="info-card-icon-wrapper">
                            <?php echo $svg_content; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($card_title)): ?>
                        <h3 class="info-card-title"><?php echo wp_kses_post($card_title); ?></h3>
                    <?php endif; ?>
                    
                    <?php if (!empty($card_text)): ?>
                        <p class="info-card-text"><?php echo wp_kses_post($card_text); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
