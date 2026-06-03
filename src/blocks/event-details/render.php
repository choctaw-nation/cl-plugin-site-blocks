<?php
/**
 * Render callback for the Event Details block.
 *
 * @package ChoctawNation
 */

$context = $block->context;
if ( empty( $context['postId'] ) || empty( $context['postType'] ) ) {
	return;
}
$has_content       = ! empty( get_post_field( 'post_content', $context['postId'] ) ); 
$is_ticketed_event = 'true' === get_field( 'is_ticketed_event', $context['postId'] );
if ( $has_content || $is_ticketed_event ) {
	printf(
		'<a %s>View Details</a>',
		get_block_wrapper_attributes(
			array(
				'href' => esc_url( get_permalink( $context['postId'] ) ),
			)
		)
	);
}