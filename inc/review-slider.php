<?php
/** Shared presentation for manual and patient-submitted reviews. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function global360blocks_review_entity_label( $name, $url ) {
 return $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>' : esc_html( $name );
}
function global360blocks_review_slider( array $clean_reviews, array $attributes ) {
 $heading = $attributes['heading'] ?? '';
 $background_color = $attributes['backgroundColor'] ?? '';
		$reviews_per_slide = ! empty( $attributes['submitted'] ) ? 3 : ( ( function_exists( 'wp_is_mobile' ) && wp_is_mobile() ) ? 1 : 3 );
		$slides            = array_chunk( $clean_reviews, $reviews_per_slide );
		$total_slides      = count( $slides );

		$wrapper_args  = array( 'class' => 'patient-reviews-slider' . ( ! empty( $attributes['submitted'] ) ? ' patient-submitted-reviews' : '' ) );
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
												<?php if ( isset( $review['rating'] ) ) : ?><p class="patient-review-rating"><?php echo esc_html( sprintf( __( 'Rated %d out of 5', 'global360blocks' ), $review['rating'] ) ); ?></p><?php endif; ?>
												<?php if ( $review['clinic'] ) : ?>
													<p class="patient-review-clinic"><?php echo global360blocks_review_entity_label( $review['clinic'], $review['clinic_url'] ?? '' ); ?></p>
												<?php endif; ?>
												<?php if ( isset( $review['doctor'] ) ) : ?><p class="patient-review-doctor"><?php echo global360blocks_review_entity_label( $review['doctor'], $review['doctor_url'] ?? '' ); ?></p><?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="patient-reviews-slider-controls" aria-label="Reviews navigation"<?php echo $total_slides < 2 ? ' hidden' : ''; ?>>
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

				<?php if ( ! empty( $attributes['submitted'] ) ) : ?>
					<?php $button_text = array_key_exists( 'buttonText', $attributes ) ? trim( (string) $attributes['buttonText'] ) : __( 'View All Reviews', 'global360blocks' ); ?>
					<?php if ( '' !== $button_text ) : ?>
						<div class="patient-reviews-slider-action">
							<a class="btn btn_global patient-reviews-slider-button" href="<?php echo esc_url( home_url( '/patient-reviews/' ) ); ?>"><?php echo esc_html( $button_text ); ?></a>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php

		$output = ob_get_clean();

 return $output;
}
