<?php
/**
 * Tickets Block render callback
 *
 * @package ChoctawNation
 */

if ( ! get_field( 'is_ticketed_event' ) ) {
	return;
}
$tickets_store = 'cno/ticketsBlock';
$show_details  = get_field( 'ticket_details' );
$venue_name    = get_the_terms( get_the_ID(), 'choctaw-events-venue' )[0]->name ?? '';
wp_interactivity_state(
	$tickets_store,
	array(
		'shows' => array_map(
			function ( $ticket ) use ( $venue_name ) {
				$event_datetime = DateTimeImmutable::createFromFormat(
					'l F j, Y g:i a',
					$ticket['event_date'] . ' ' . ( $ticket['event_time'] ),
					wp_timezone()
				);
				$alternate_location_id = $ticket['alternate_location'] ?? '';
				if ( $ticket['use_alternate_location'] && $alternate_location_id ) {
					$alternate_location = get_term( $alternate_location_id );
					if ( $alternate_location ) {
						$venue_name = $alternate_location->name;
					}
				}
				return array(
					'key'                 => sanitize_title( get_the_title() ) . '-' . $event_datetime->format( 'YmdHis' ),
					'eventDateTime'       => $event_datetime->format( 'c' ),
					'prettyEventDateTime' => $ticket['event_date'] . ' at ' . $ticket['event_time'],
					'ticketLink'          => $ticket['ticket_link'],
					'location'            => $venue_name,
					'isSoldOut'           => $ticket['is_sold_out'] ?? false,
				);
			},
			$show_details
		),
	)
);
?>
<div data-wp-interactive="<?php echo $tickets_store; ?>" <?php echo get_block_wrapper_attributes(); ?>>
	<fieldset>
		<template data-wp-each--show="state.shows" data-wp-each-key="context.show.key">
			<label data-wp-bind--for="context.show.key" data-wp-class--sold-out="context.show.isSoldOut">
				<input type="radio" name="<?php echo sanitize_title( get_the_title() ); ?>" data-wp-bind--id="context.show.key" data-wp-bind--checked="callbacks.isActiveShow"
					data-wp-bind--disabled="context.show.isSoldOut" data-wp-on--click="actions.setActiveShow" />
				<time data-wp-bind--datetime="context.show.eventDateTime" data-wp-text="context.show.prettyEventDateTime"></time>
				<span>at</span>
				<span data-wp-text="context.show.location"></span>
				<span data-wp-bind--hidden="!context.show.isSoldOut">(Sold Out)</span>
			</label>
		</template>
	</fieldset>
</div>