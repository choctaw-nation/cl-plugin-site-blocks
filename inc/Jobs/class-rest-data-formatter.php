<?php
/**
 * REST Data Formatter class for formatting event data for the Add to Calendar block.
 *
 * @package ChoctawNation
 */

namespace ChoctawNation\CL_SiteBlocks\Jobs;

use DateTimeImmutable;
use DateTimeZone;
use Error;
use WP_Error;
use WP_Post;

/**
 * REST Data Formatter class for the Add to Calendar block
 */
class Rest_Data_Formatter {
	/**
	 * The timezone to use for formatting date and time values.
	 *
	 * @var DateTimeZone $timezone
	 */
	private DateTimeZone $timezone;

	/**
	 * The event post
	 *
	 * @var WP_Post $event
	 */
	private WP_Post $event;

	/**
	 * The selected show date (if applicable) for ticketed events, in ISO 8601 format.
	 *
	 * @var string|null $selected_show_date
	 */
	private ?string $selected_show_date;

	/**
	 * An array to hold ACF field values for the event, to avoid multiple calls to get_field() throughout the class.
	 *
	 * @var array $acf
	 */
	private array $acf;

	/**
	 * A boolean indicating whether the event is an all-day event, determined based on ACF fields. This is used to simplify logic when formatting event data, especially for determining default times for all-day events.
	 *
	 * @var bool $all_day_event
	 */
	private bool $all_day_event;

	/**
	 * The name of the venue associated with the event, retrieved from the 'choctaw-events-venue' taxonomy. This is used to include venue information in the formatted event data for the Add to Calendar block.
	 *
	 * @var string $venue
	 */
	private string $venue;

	/**
	 * Constructor for the Rest_Data_Formatter class.
	 *
	 * @param WP_Post     $event The event post object to format data from.
	 * @param string|null $selected_show_date The selected show date in ISO 8601 format (optional, used for ticketed events).
	 */
	public function __construct( WP_Post $event, ?string $selected_show_date = null ) {
		$this->timezone           = wp_timezone();
		$this->event              = $event;
		$this->selected_show_date = $selected_show_date;
		$this->acf                = array(
			'start_date'        => get_field( 'start_date', $this->event->ID ),
			'end_date'          => get_field( 'end_date', $this->event->ID ),
			'start_time'        => get_field( 'start_time', $this->event->ID ),
			'end_time'          => get_field( 'end_time', $this->event->ID ),
			'event_url'         => get_field( 'event_website_url', $this->event->ID ),
			'is_all_day'        => get_field( 'is_all_day', $this->event->ID ),
			'is_ticketed_event' => (bool) get_field( 'is_ticketed_event', $this->event->ID ),
			'ticket_details'    => get_field( 'ticket_details', $this->event->ID ),
		);
		$this->all_day_event      = $this->acf['is_all_day'] || ( empty( $this->acf['start_time'] ) && false === $this->acf['is_ticketed_event'] );
		$this->venue              = get_the_terms( $this->event->ID, 'choctaw-events-venue' )[0]->name ?? '';
	}

	/**
	 * Formats the event data for the Add to Calendar block response.
	 *
	 * @throws Error If the event start or end date/time cannot be parsed, an error is thrown indicating invalid event data. This ensures that the API response will include an appropriate error message and status code if the event data is not in the expected format.
	 */
	public function format_event_data(): array {
		$event_start = $this->get_event_start();
		$event_end   = $this->get_event_end();
		if ( ! $event_start || ! $event_end ) {
			throw new Error( 'Invalid event date or time format.' );
		}
		$formatted_data = array(
			'id'          => $this->event->ID,
			'title'       => $this->event->post_title,
			'description' => ! empty( $this->event->post_excerpt ) ? $this->event->post_excerpt : get_field( 'brief_description', $this->event->ID ),
			'start'       => $event_start->format( DATE_ATOM ),
			'end'         => $event_end->format( DATE_ATOM ),
			'isAllDay'    => $this->all_day_event,
			'venue'       => $this->venue,
		);
		if ( $this->acf['event_url'] ) {
			$formatted_data['website'] = $this->acf['event_url'];
		}

		if ( $this->acf['is_ticketed_event'] && ! empty( $this->acf['ticket_details'] ) ) {
			$formatted_data['shows'] = $this->get_event_shows();
		}
		return $formatted_data;
	}

	/**
	 * Determines the event start time based on whether it's a ticketed event with a selected show date or a regular event. For ticketed events, the selected show date is used as the start time. For regular events, the start date and time from ACF fields are used, with a default time of 12:00 am for all-day events.
	 */
	private function get_event_start(): DateTimeImmutable|false {
		if ( $this->acf['is_ticketed_event'] && $this->selected_show_date ) {
			// For ticketed events, use the selected show date as the event start time.
			return DateTimeImmutable::createFromFormat( DATE_ATOM, $this->selected_show_date, $this->timezone );
		}
		return DateTimeImmutable::createFromFormat( 'F j, Y g:i a', $this->acf['start_date'] . ' ' . ( $this->all_day_event ? '12:00 am' : $this->acf['start_time'] ), $this->timezone );
	}

	/**
	 * Determines the event end time based on whether it's a ticketed event with a selected show date or a regular event. For ticketed events, the selected show date is used as the end time (since each show is treated as a separate event). For regular events, the end date and time from ACF fields are used, with a default time of 11:59 pm for all-day events.
	 */
	private function get_event_end(): DateTimeImmutable|false {
		if ( $this->acf['is_ticketed_event'] && $this->selected_show_date ) {
			// For ticketed events, use the selected show date as the event end time.
			return DateTimeImmutable::createFromFormat( DATE_ATOM, $this->selected_show_date, $this->timezone );
		}
		$end_date = ! empty( $this->acf['end_date'] ) ? $this->acf['end_date'] : $this->acf['start_date'];
		$end_time = ! empty( $this->acf['end_time'] ) ? $this->acf['end_time'] : $this->acf['start_time'];
		return DateTimeImmutable::createFromFormat( 'F j, Y g:i a', $end_date . ' ' . ( $this->all_day_event ? '11:59 pm' : $end_time ), $this->timezone );
	}

	/**
	 * Formats the show data for ticketed events to include in the response. Each show includes the date, ticket link, venue, and sold-out status. The show date is formatted as an ISO 8601 string for consistency with the rest of the event data. Venue information is included for each show, with support for alternate locations if specified in the ACF fields.
	 */
	private function get_event_shows(): array {
		return array_map(
			function ( $show ) {
				$show_start = DateTimeImmutable::createFromFormat( 'l F j, Y g:i a', $show['event_date'] . ' ' . $show['event_time'], $this->timezone );
				$show_venue = $this->venue;
				if ( $show['use_alternate_location'] ) {
					$show_venue = $show['alternate_location'];
				}
				return array(
					'date'      => $show_start->format( DATE_ATOM ),
					'tickets'   => $show['ticket_link'],
					'venue'     => $show_venue,
					'isSoldOut' => $show['is_sold_out'],
				);
			},
			$this->acf['ticket_details']
		);
	}
}
