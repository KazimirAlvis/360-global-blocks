<?php
/**
 * FAQ Accordion render callback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'global360blocks_render_faq_accordion_block' ) ) {
	function global360blocks_render_faq_accordion_block( $attributes ) {
		global360blocks_enqueue_block_assets_from_manifest(
			'global360blocks/faq-accordion',
			array( 'style' => false )
		);

		$heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
		$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

		$sanitize_color = static function( $value ) {
			$value = is_string( $value ) ? trim( $value ) : '';
			if ( '' === $value ) {
				return '';
			}

			if ( 0 === strpos( $value, 'var(' ) && false !== strpos( $value, '--' ) ) {
				return $value;
			}

			$hex = sanitize_hex_color( $value );
			return $hex ? $hex : '';
		};

		$background_color  = isset( $attributes['backgroundColor'] ) ? $sanitize_color( $attributes['backgroundColor'] ) : '';
		$active_item_color = isset( $attributes['activeItemColor'] ) ? $sanitize_color( $attributes['activeItemColor'] ) : '';

		if ( empty( $items ) ) {
			$items = array(
				array(
					'question' => __( 'What can I expect immediately after treatment?', 'global360blocks' ),
					'answer'   => __( 'Use this space to reassure patients about immediate recovery expectations.', 'global360blocks' ),
				),
			);
		}

		$style_parts = array();

		if ( $background_color ) {
			$style_parts[] = '--faq-background:' . esc_attr( $background_color );
		}

		if ( $active_item_color ) {
			$style_parts[] = '--faq-active-background:' . esc_attr( $active_item_color );
		}

		$style_attribute = $style_parts ? implode( ';', $style_parts ) : '';
		$wrapper_classes = array( 'faq-accordion-block' );

		if ( isset( $attributes['className'] ) ) {
			$wrapper_classes[] = $attributes['className'];
		}

		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => implode( ' ', $wrapper_classes ),
				'style' => $style_attribute,
			)
		);

		$block_id = uniqid( 'faq-accordion-', true );

		ob_start();
		?>
		<div <?php echo $wrapper_attributes; ?>>
			<?php if ( $heading ) : ?>
				<h2 class="faq-accordion-heading"><?php echo wp_kses_post( $heading ); ?></h2>
			<?php endif; ?>
			<div class="faq-accordion-list" role="tablist">
				<?php foreach ( $items as $index => $item ) :
					$question = isset( $item['question'] ) ? $item['question'] : '';
					$answer   = isset( $item['answer'] ) ? $item['answer'] : '';
					$item_id  = $block_id . '-' . $index;
					$is_open  = 0 === $index;
					?>
					<div class="faq-accordion-item<?php echo $is_open ? ' is-open' : ''; ?>">
						<button
							type="button"
							class="faq-question"
							aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( $item_id ); ?>"
						>
							<span class="faq-question-text"><?php echo wp_kses_post( $question ); ?></span>
							<span class="faq-chevron" aria-hidden="true"></span>
						</button>
						<div
							id="<?php echo esc_attr( $item_id ); ?>"
							class="faq-answer"
							role="region"
							aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>"
							<?php echo $is_open ? '' : 'hidden'; ?>
						>
							<div class="faq-answer-inner">
								<?php echo wpautop( wp_kses_post( $answer ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
