<?php
/**
 * One Column Video render callback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'global360blocks_one_column_video_get_youtube_embed_url' ) ) {
	function global360blocks_one_column_video_get_youtube_embed_url( $url ) {
		$url = is_string( $url ) ? trim( $url ) : '';

		if ( '' === $url ) {
			return '';
		}

		$video_id = '';

		if ( false !== strpos( $url, 'youtube.com/watch' ) ) {
			$parts = wp_parse_url( $url );
			if ( ! empty( $parts['query'] ) ) {
				parse_str( $parts['query'], $query_vars );
				if ( ! empty( $query_vars['v'] ) && is_string( $query_vars['v'] ) ) {
					$video_id = $query_vars['v'];
				}
			}
		} elseif ( false !== strpos( $url, 'youtu.be/' ) ) {
			$parts = wp_parse_url( $url );
			if ( ! empty( $parts['path'] ) ) {
				$video_id = ltrim( $parts['path'], '/' );
				$video_id = explode( '/', $video_id )[0];
			}
		} elseif ( false !== strpos( $url, 'youtube.com/embed/' ) ) {
			$parts = wp_parse_url( $url );
			if ( ! empty( $parts['path'] ) ) {
				$path = explode( '/', trim( $parts['path'], '/' ) );
				$embed_index = array_search( 'embed', $path, true );
				if ( false !== $embed_index && isset( $path[ $embed_index + 1 ] ) ) {
					$video_id = $path[ $embed_index + 1 ];
				}
			}
		}

		$video_id = is_string( $video_id ) ? preg_replace( '/[^a-zA-Z0-9_-]/', '', $video_id ) : '';

		if ( '' === $video_id ) {
			return '';
		}

		$params = array(
			'rel'            => '0',
			'modestbranding' => '1',
			'playsinline'    => '1',
		);

		return add_query_arg( $params, sprintf( 'https://www.youtube.com/embed/%s', rawurlencode( $video_id ) ) );
	}
}

if ( ! function_exists( 'global360blocks_render_one_column_video_block' ) ) {
	function global360blocks_render_one_column_video_block( $attributes ) {
		global360blocks_enqueue_block_assets_from_manifest( 'global360blocks/one-column-video' );

		$heading = isset( $attributes['heading'] ) ? (string) $attributes['heading'] : '';
		$video_url = isset( $attributes['videoUrl'] ) ? (string) $attributes['videoUrl'] : '';
		$background_color = isset( $attributes['backgroundColor'] ) ? (string) $attributes['backgroundColor'] : '';

		$embed_url = global360blocks_one_column_video_get_youtube_embed_url( $video_url );

		if ( '' === $embed_url ) {
			return '';
		}

		$wrapper_args = array( 'class' => 'one-column-video' );
		$bg_color = sanitize_hex_color( $background_color );
		if ( $background_color && ! $bg_color ) {
			$bg_color = sanitize_text_field( $background_color );
		}
		if ( $bg_color ) {
			$wrapper_args['style'] = '--one-column-video-background:' . esc_attr( $bg_color );
		}

		$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );

		ob_start();
		?>
		<div <?php echo $wrapper_attributes; ?>>
			<div class="one-column-video-inner">
				<?php if ( '' !== trim( wp_strip_all_tags( $heading ) ) ) : ?>
					<h2 class="one-column-video-heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>

				<div class="one-column-video-media">
					<div class="one-column-video-embed">
						<iframe
							class="one-column-video-iframe"
							src="<?php echo esc_url( $embed_url ); ?>"
							loading="lazy"
							frameborder="0"
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
							allowfullscreen
						></iframe>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
