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

		$output = global360blocks_review_slider( $clean_reviews, $attributes );

		if ( ! empty( $clean_reviews ) ) {
			$schema_items = array();
			foreach ( $clean_reviews as $index => $review ) {
				$review_text = trim( wp_strip_all_tags( $review['review'] ) );
				if ( '' === $review_text ) {
					continue;
				}
				$item = array(
					'@type'        => 'Review',
					'position'     => $index + 1,
					'reviewBody'   => $review_text,
					'itemReviewed' => array(
						'@type' => 'MedicalOrganization',
						'name'  => get_bloginfo( 'name' ),
					),
				);
				if ( $review['name'] ) {
					$item['author'] = array(
						'@type' => 'Person',
						'name'  => $review['name'],
					);
				}
				$schema_items[] = $item;
			}
			if ( ! empty( $schema_items ) ) {
				$schema = array(
					'@context'        => 'https://schema.org',
					'@type'           => 'ItemList',
					'itemListElement' => $schema_items,
				);
				$output .= '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
			}
		}

		return $output;
	}
}
