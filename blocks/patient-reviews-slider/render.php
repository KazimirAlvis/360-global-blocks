<?php
/**
 * Patient Reviews Slider render callback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'global360blocks_render_patient_reviews_slider_block' ) ) {
	function global360blocks_render_patient_reviews_slider_block( $attributes ) {
		global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/patient-reviews-slider' );

		$heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
		$background_color = isset( $attributes['backgroundColor'] ) ? $attributes['backgroundColor'] : '';
		$reviews = isset( $attributes['reviews'] ) && is_array( $attributes['reviews'] ) ? $attributes['reviews'] : array();

		$clean_reviews = array();
		foreach ( $reviews as $review ) {
			if ( ! is_array( $review ) ) {
				continue;
			}

			$name   = isset( $review['name'] ) ? trim( (string) $review['name'] ) : '';
			$clinic = isset( $review['clinic'] ) ? trim( (string) $review['clinic'] ) : '';
			$text   = isset( $review['review'] ) ? (string) $review['review'] : '';

			$text = wp_kses_post( $text );

			if ( '' === trim( wp_strip_all_tags( $text ) ) && '' === $name && '' === $clinic ) {
				continue;
			}

			$clean_reviews[] = array(
				'name'   => $name,
				'clinic' => $clinic,
				'review' => $text,
			);
		}

		if ( empty( $clean_reviews ) ) {
			return '';
		}

		$reviews_per_slide = ( function_exists( 'wp_is_mobile' ) && wp_is_mobile() ) ? 1 : 3;
		$slides            = array_chunk( $clean_reviews, $reviews_per_slide );
		$total_slides      = count( $slides );

		$wrapper_args  = array( 'class' => 'patient-reviews-slider' );
		$style_tokens  = array();
		$brand_color   = function_exists( 'global360blocks_get_brand_primary_color' ) ? global360blocks_get_brand_primary_color() : '';
		$bg_color      = sanitize_hex_color( $background_color );

		if ( $brand_color ) {
			$style_tokens[] = '--reviews-accent:' . esc_attr( $brand_color );
		}

		if ( $background_color && ! $bg_color ) {
			$bg_color = sanitize_text_field( $background_color );
		}

		if ( $bg_color ) {
			$style_tokens[] = '--reviews-background:' . esc_attr( $bg_color );
		}

		if ( ! empty( $style_tokens ) ) {
			$wrapper_args['style'] = implode( ';', $style_tokens );
		}

		$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );

		ob_start();
		?>
		<div <?php echo $wrapper_attributes; ?>>
			<div class="patient-reviews-slider-inner">
				<?php if ( $heading ) : ?>
					<div class="patient-reviews-slider-heading">
						<h2><?php echo wp_kses_post( $heading ); ?></h2>
					</div>
				<?php endif; ?>

				<div class="patient-reviews-slider-container" data-current-slide="0">
					<div class="patient-reviews-slider-viewport">
						<div class="patient-reviews-slider-track">
							<?php foreach ( $slides as $slide_index => $slide_reviews ) : ?>
								<div class="patient-reviews-slide<?php echo 0 === $slide_index ? ' active' : ''; ?>">
									<div class="patient-reviews-slide-grid">
										<?php foreach ( $slide_reviews as $review ) : ?>
											<div class="patient-review-card">
												<div class="patient-review-text"><?php echo '' !== trim( $review['review'] ) ? wp_kses_post( $review['review'] ) : '&nbsp;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
												<?php if ( $review['name'] ) : ?>
													<p class="patient-review-name"><?php echo esc_html( $review['name'] ); ?></p>
												<?php endif; ?>
												<?php if ( $review['clinic'] ) : ?>
													<p class="patient-review-clinic"><?php echo esc_html( $review['clinic'] ); ?></p>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<?php if ( $total_slides > 1 ) : ?>
					<div class="patient-reviews-slider-controls" aria-label="Reviews navigation">
						<button class="patient-reviews-slider-nav prev" type="button" aria-label="Previous reviews">
							<span class="screen-reader-text"><?php echo esc_html__( 'Previous', 'global360blocks' ); ?></span>
						</button>

						<div class="patient-reviews-slider-dots">
							<?php for ( $i = 0; $i < $total_slides; $i++ ) : ?>
								<button
									type="button"
									class="patient-reviews-slider-dot<?php echo 0 === $i ? ' active' : ''; ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'global360blocks' ), $i + 1 ) ); ?>"
								></button>
							<?php endfor; ?>
						</div>

						<button class="patient-reviews-slider-nav next" type="button" aria-label="Next reviews">
							<span class="screen-reader-text"><?php echo esc_html__( 'Next', 'global360blocks' ); ?></span>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
