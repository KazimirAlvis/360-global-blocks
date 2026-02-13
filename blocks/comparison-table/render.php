<?php
/**
 * Comparison Table render callback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'global360blocks_render_comparison_table_block' ) ) {
	function global360blocks_render_comparison_table_block( $attributes ) {
		global360blocks_enqueue_block_assets_from_manifest(
			'global360blocks/comparison-table',
			array( 'style' => false )
		);

		$heading           = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
		$subheading        = isset( $attributes['subheading'] ) ? $attributes['subheading'] : '';
		$columns           = isset( $attributes['columns'] ) && is_array( $attributes['columns'] ) ? $attributes['columns'] : array();
		$rows              = isset( $attributes['rows'] ) && is_array( $attributes['rows'] ) ? $attributes['rows'] : array();
		$footnote          = isset( $attributes['footnote'] ) ? $attributes['footnote'] : '';
		$background_color  = isset( $attributes['backgroundColor'] ) ? $attributes['backgroundColor'] : '';
		$header_background = isset( $attributes['headerBackgroundColor'] ) ? $attributes['headerBackgroundColor'] : '';
		$header_font_size  = isset( $attributes['headerFontSize'] ) ? $attributes['headerFontSize'] : 0;

		$column_count = count( $columns );

		if ( $column_count < 2 ) {
			return '';
		}

		$columns = array_values( array_map( 'wp_kses_post', $columns ) );

		$prepared_rows = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$row_cells = isset( $row['cells'] ) && is_array( $row['cells'] ) ? $row['cells'] : array();
			$row_cells = array_pad( array_slice( $row_cells, 0, $column_count ), $column_count, '' );

			$has_content = false;
			$prepared_cells = array();

			foreach ( $row_cells as $cell ) {
				$cell_content = wp_kses_post( $cell );
				$prepared_cells[] = $cell_content;

				if ( ! $has_content && '' !== trim( wp_strip_all_tags( $cell_content ) ) ) {
					$has_content = true;
				}
			}

			if ( $has_content ) {
				$prepared_rows[] = $prepared_cells;
			}
		}

		if ( empty( $prepared_rows ) ) {
			return '';
		}

		$wrapper_args = array(
			'class' => 'comparison-table-block',
		);

		$style_tokens = array();
		$brand_color  = global360blocks_get_brand_primary_color();
		$bg_color     = sanitize_hex_color( $background_color );
		$header_bg    = sanitize_hex_color( $header_background );

		if ( $brand_color ) {
			$style_tokens[] = '--comparison-accent:' . esc_attr( $brand_color );
		}

		if ( $background_color && ! $bg_color ) {
			$bg_color = sanitize_text_field( $background_color );
		}

		if ( $header_background && ! $header_bg ) {
			$header_bg = sanitize_text_field( $header_background );
		}

		if ( $bg_color ) {
			$style_tokens[] = '--comparison-background:' . esc_attr( $bg_color );
		}

		if ( $header_bg ) {
			$style_tokens[] = '--comparison-header-background:' . esc_attr( $header_bg );
		}

		if ( is_numeric( $header_font_size ) ) {
			$header_font_size = (int) $header_font_size;
		}

		if ( $header_font_size > 0 ) {
			$style_tokens[] = '--comparison-header-font-size:' . esc_attr( $header_font_size ) . 'px';
		}

		if ( ! empty( $style_tokens ) ) {
			$wrapper_args['style'] = implode( ';', $style_tokens );
		}

		$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );
		$caption_text       = $heading ? wp_strip_all_tags( $heading ) : __( 'Treatment comparison table', 'global360blocks' );

		ob_start();
		?>
		<div <?php echo $wrapper_attributes; ?>>
			<div class="comparison-table-inner">
				<?php if ( $heading || $subheading ) : ?>
					<div class="comparison-table-heading">
						<?php if ( $heading ) : ?>
							<h2><?php echo wp_kses_post( $heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $subheading ) : ?>
							<p class="comparison-table-subheading"><?php echo wp_kses_post( $subheading ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="comparison-table-wrapper">
					<table class="comparison-table">
						<caption class="screen-reader-text"><?php echo esc_html( $caption_text ); ?></caption>
						<thead>
							<tr>
								<?php foreach ( $columns as $column_index => $column_label ) : ?>
									<th scope="col"><?php echo '' !== trim( $column_label ) ? wp_kses_post( $column_label ) : '&nbsp;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $prepared_rows as $row_cells ) : ?>
								<tr>
									<?php foreach ( $row_cells as $cell_content ) : ?>
										<td><?php echo '' !== trim( $cell_content ) ? wp_kses_post( $cell_content ) : '&nbsp;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<?php if ( $footnote ) : ?>
					<p class="comparison-table-footnote"><?php echo wp_kses_post( $footnote ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
