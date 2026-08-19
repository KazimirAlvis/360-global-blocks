<?php

/**
 * YouTube URL helpers shared by dynamic blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'global360blocks_get_youtube_video_id' ) ) {
function global360blocks_get_youtube_video_id( $url ) {
	if ( empty( $url ) ) {
		return '';
	}

	$parsed = wp_parse_url( $url );
	if ( empty( $parsed['host'] ) ) {
		return '';
	}

	$host      = strtolower( $parsed['host'] );
	$candidate = '';

	if ( false !== strpos( $host, 'youtube.com' ) ) {
		if ( ! empty( $parsed['query'] ) ) {
			parse_str( $parsed['query'], $query_vars );
			if ( ! empty( $query_vars['v'] ) ) {
				$candidate = $query_vars['v'];
			}
		}

		if ( ! $candidate && ! empty( $parsed['path'] ) ) {
			$path_segments = array_values( array_filter( explode( '/', $parsed['path'] ) ) );
			if ( ! empty( $path_segments ) ) {
				$patterns = array( 'embed', 'shorts', 'live' );
				if ( in_array( $path_segments[0], $patterns, true ) && isset( $path_segments[1] ) ) {
					$candidate = $path_segments[1];
				} else {
					$candidate = end( $path_segments );
				}
			}
		}
	} elseif ( false !== strpos( $host, 'youtu.be' ) && ! empty( $parsed['path'] ) ) {
		$candidate = trim( $parsed['path'], '/' );
	}

	if ( $candidate && preg_match( '/([A-Za-z0-9_-]{11})/', $candidate, $matches ) ) {
		return $matches[1];
	}

	return '';
}
}

if ( ! function_exists( 'global360blocks_get_youtube_embed_url' ) ) {
function global360blocks_get_youtube_embed_url( $url ) {
	if ( empty( $url ) ) {
		return '';
	}

	$video_id = global360blocks_get_youtube_video_id( $url );
	if ( ! $video_id ) {
		return $url;
	}

	return add_query_arg(
		array(
			'rel'            => 0,
			'modestbranding' => 1,
			'playsinline'    => 1,
		),
		'https://www.youtube.com/embed/' . $video_id
	);
}
}

if ( ! function_exists( 'global360blocks_get_youtube_thumbnail_url' ) ) {
function global360blocks_get_youtube_thumbnail_url( $video_id, $quality = 'hqdefault' ) {
	if ( empty( $video_id ) ) {
		return '';
	}

	$quality = in_array( $quality, array( 'maxresdefault', 'hqdefault', 'mqdefault', 'sddefault' ), true )
		? $quality
		: 'hqdefault';

	return sprintf( 'https://i.ytimg.com/vi/%1$s/%2$s.jpg', rawurlencode( $video_id ), $quality );
}
}
