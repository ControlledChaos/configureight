<?php
/**
 * Theme configuration
 *
 * @package    BS Bludit
 * @subpackage Templates
 * @category   Utility
 * @since      1.0.0
 */

/**
 * Configuration constant
 *
 * @since 1.0.0
 */
if ( ! defined( 'BSB_CONFIG' ) ) {
	define(
		'BSB_CONFIG',
		[
			'debug'     => true,
			'toolbar'   => true,
			'byline'    => true,
			'post_date' => true,
			'read_time' => true
		]
	);
}

// Set debug mode.
if ( BSB_CONFIG['debug'] ) {
	define( 'DEBUG_MODE', true );
}
