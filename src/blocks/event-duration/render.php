<?php
/**
 * Render callback for the Event Start block
 *
 * @package CL_SiteBlocks
 */

use ChoctawNation\CL_SiteBlocks\Jobs\Date_Formatter;
$format      = isset( $attributes['format'] ) ? $attributes['format'] : 'F j, Y g:i a';
$as_duration = isset( $attributes['asDuration'] ) ? $attributes['asDuration'] : false;
try {
	$date_formatter = new Date_Formatter( $format, $as_duration );
	$start_date     = $date_formatter->start_date ?? new DateTime( 'now', wp_timezone() );
	printf(
		'<time datetime="%1$s" %2$s>%3$s</time>',
		esc_attr( $start_date->format( DateTime::ATOM ) ),
		get_block_wrapper_attributes(),
		esc_html( $date_formatter->get_formatted_string() )
	);
} catch ( InvalidArgumentException $e ) {
	echo '<!-- Error rendering Event Duration block: ' . esc_html( $e->getMessage() ) . ' -->';
}
