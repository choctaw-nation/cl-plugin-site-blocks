<?php
/**
 * Render callback for the Tickets Button block.
 *
 * @package ChoctawNation
 */

if ( 'false' === get_field( 'is_ticketed_event' ) ) {
	return;
}
$tickets_link = get_field( 'tickets_link' );
$is_sold_out  = get_field( 'is_sold_out' );
$wrapper_args = array(
	'href'   => esc_url( $tickets_link ),
	'target' => '_blank',
	'rel'    => 'noopener noreferrer',
	'class'  => $is_sold_out ? 'sold-out' : '',
);
if ( $is_sold_out ) {
	$wrapper_args['disabled'] = 'true';
	unset( $wrapper_args['href'], $wrapper_args['target'], $wrapper_args['rel'] );
}
printf(
	'<%1$s %2$s>%3$s</%1$s>',
	$is_sold_out ? 'button' : 'a',
	get_block_wrapper_attributes( $wrapper_args ),
	esc_html( $is_sold_out ? 'Sold Out' : 'Get Tickets' )
);