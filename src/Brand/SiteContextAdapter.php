<?php

namespace Global360Blocks\Brand;

final class SiteContextAdapter {
	/** @var array<string,mixed>|null */
	private static $settings;

	/** @return array<string,mixed> */
	public static function all(): array {
		if ( null !== self::$settings ) {
			return self::$settings;
		}

		if ( function_exists( 'global360_platform' ) ) {
			self::$settings = global360_platform()->site_context()->all();
			return self::$settings;
		}

		$value = get_option( '360_global_settings', array() );
		self::$settings = is_array( $value ) ? $value : array();
		return self::$settings;
	}

	/** @return mixed */
	public static function get( string $key, $default = null ) {
		$settings = self::all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}
}
