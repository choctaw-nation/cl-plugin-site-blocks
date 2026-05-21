<?php
/**
 * Date Formatter
 * Handles formatting dates for the Event Duration block, including edge cases like all-day events and multi-day events.
 *
 * Mirrors the DateFormatter class in src/blocks/event-duration/_utils/DateFormatter.ts, which is used in the Edit component to ensure consistent date formatting in the editor.
 *
 * For more details on these fields, see [/plugins/cno-plugin-events/inc/acf/class-custom-fields.php](https://github.com/choctaw-nation/cno-plugin-events/blob/main/inc/acf/class-custom-fields.php)
 *
 * @package ChoctawNation
 */

namespace ChoctawNation\CL_SiteBlocks\Jobs;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Date Formatter class for the Event Duration block
 */
class Date_Formatter {
	/**
	 * Start date as DateTimeImmutable
	 *
	 * @var ?DateTimeImmutable
	 */
	public ?DateTimeImmutable $start_date;

	/**
	 * End date as DateTimeImmutable
	 *
	 * @var ?DateTimeImmutable
	 */
	private ?DateTimeImmutable $end_date;

	/**
	 * Date format string
	 *
	 * @var string
	 */
	private string $format;

	/**
	 * Whether to format as duration
	 *
	 * @var bool
	 */
	private bool $as_duration;

	/**
	 * Whether duration format can be used
	 *
	 * @var bool
	 */
	private bool $can_use_duration;

	/**
	 * Ticket details array
	 *
	 * @var array
	 */
	private array $ticket_details;

	/**
	 * Whether event is ticketed
	 *
	 * @var bool
	 */
	private bool $is_ticketed_event;

	/**
	 * Default start time
	 *
	 * @var string
	 */
	private string $default_start_time = '12:00 am';

	/**
	 * Timezone for date formatting
	 *
	 * @var DateTimeZone $timezone
	 */
	private DateTimeZone $timezone;

	/**
	 * String format for ticketed event date/time display
	 *
	 * @var string $ticked_event_date_time_string
	 */
	private string $ticked_event_date_time_string;

	/**
	 * Constructor
	 *
	 * @param string $format       Date format string.
	 * @param bool   $as_duration  Whether to format as duration.
	 *
	 * @throws \InvalidArgumentException If start_date is missing.
	 */
	public function __construct( string $format, bool $as_duration ) {
		$this->format                        = $format;
		$this->as_duration                   = $as_duration;
		$this->ticked_event_date_time_string = 'l F j, Y g:i a';
		$start_date                          = get_field( 'start_date' );
		if ( empty( $start_date ) ) {
			throw new \InvalidArgumentException( 'Start date is required to format the event date.' );
		}
		$start_time              = ! empty( get_field( 'start_time' ) ) ? get_field( 'start_time' ) : $this->default_start_time;
		$end_date                = ! empty( get_field( 'end_date' ) ) ? get_field( 'end_date' ) : $start_date;
		$end_time                = ! empty( get_field( 'end_time' ) ) ? get_field( 'end_time' ) : null;
		$this->timezone          = wp_timezone();
		$this->is_ticketed_event = ! empty( get_field( 'is_ticketed_event' ) ) && 'true' === get_field( 'is_ticketed_event' );

		$this->start_date       = $this->create_date_time( $start_date, $start_time );
		$this->end_date         = $this->create_date_time( $end_date, $end_time );
		$this->ticket_details   = get_field( 'ticket_details' ) ?? array();
		$this->can_use_duration = $start_date !== $end_date || $this->has_ticket_with_different_date();
	}

	/**
	 * Creates a DateTimeImmutable from an ACF date string and optional time string.
	 *
	 * @param ?string $date_string Date string as "F j, Y".
	 * @param ?string $time        Time string as "g:i a", defaults to "12:00 am".
	 * @param ?string $custom_format Optional custom format string for parsing, defaults to "F j, Y g:i a".
	 *
	 * @return ?DateTimeImmutable DateTimeImmutable object or null if invalid.
	 */
	private function create_date_time( $date_string, $time = null, ?string $custom_format = null ): ?DateTimeImmutable {
		if ( empty( $date_string ) ) {
			return null;
		}
		if ( empty( $time ) ) {
			$time = $this->default_start_time;
		}
		$format = $custom_format ?? 'F j, Y g:i a';

		$date = DateTimeImmutable::createFromFormat( $format, "{$date_string} {$time}", $this->timezone );
		if ( ! $date ) {
			return null;
		}

		return $date;
	}

	/**
	 * Checks if any ticket has a different event_date than start_date.
	 *
	 * @return bool True if any ticket has a different date.
	 */
	private function has_ticket_with_different_date(): bool {

		if ( ! $this->is_ticketed_event || empty( $this->ticket_details ) ) {
			return false;
		}

		foreach ( $this->ticket_details as $ticket ) {
			$ticket_date = $this->create_date_time( $ticket['event_date'], $ticket['event_time'], $this->ticked_event_date_time_string );
			if ( ! $ticket_date ) {
				continue;
			}
			if ( $ticket_date !== $this->start_date ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the formatted date string based on the ACF event fields, format, and duration settings.
	 *
	 * @return string Formatted date string.
	 *
	 * @throws \RuntimeException If start_date is missing or format is invalid.
	 */
	public function get_formatted_string() {
		if ( ! $this->start_date ) {
			throw new \RuntimeException( 'Start date is required to format the event date.' );
		}

		if ( $this->as_duration ) {
			if ( 'g:i a' === $this->format && $this->can_use_duration ) {
				return $this->get_time_duration();
			}

			if ( ! $this->can_use_duration ) {
				$standard_format = str_contains( $this->format, 'M' ) ? 'M d, Y' : 'F d, Y';
				return $this->format_date( $standard_format, $this->start_date );
			}

			if ( $this->is_ticketed_event ) {
				$ticketed_duration = $this->get_ticketed_event_duration();
				if ( ! $ticketed_duration ) {
					throw new \RuntimeException( 'Ticketed event duration could not be determined.' );
				}

				return $this->get_formatted_duration_string( $ticketed_duration );
			}

			return $this->get_formatted_duration_string(
				array(
					'start' => $this->start_date,
					'end'   => $this->end_date,
				)
			);
		}

		return $this->format_date( $this->format, $this->start_date );
	}

	/**
	 * Gets time duration string (e.g., "9:00 am – 5:00 pm").
	 *
	 * @return string Formatted time duration.
	 */
	private function get_time_duration() {
		$in_same_meridiem = ( (int) $this->start_date->format( 'H' ) < 12 ) === ( (int) $this->end_date->format( 'H' ) < 12 );
		$times            = array(
			'start' => $in_same_meridiem
				? $this->format_date( 'g:i', $this->start_date )
				: $this->format_date( 'g:i a', $this->start_date ),
			'end'   => $this->end_date ? $this->format_date( 'g:i a', $this->end_date ) : '',
		);

		return $times['start'] . ' – ' . $times['end'];
	}

	/**
	 * Gets ticketed event duration (earliest and latest ticket times).
	 *
	 * @return array|null Array with 'start' and 'end' DateTimeImmutable objects, or null if no valid tickets.
	 */
	private function get_ticketed_event_duration() {
		if ( empty( $this->ticket_details ) || ! is_array( $this->ticket_details ) ) {
			return null;
		}

		$start = null;
		$end   = null;

		foreach ( $this->ticket_details as $ticket ) {
			$current_date = $this->create_date_time(
				$ticket['event_date'] ?? null,
				$ticket['event_time'] ?? null,
				$this->ticked_event_date_time_string
			);

			if ( ! $current_date ) {
				continue;
			}

			if ( ! $start || $current_date < $start ) {
				$start = $current_date;
			}

			if ( ! $end || $current_date > $end ) {
				$end = $current_date;
			}
		}

		if ( ! $start || ! $end ) {
			return null;
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * Formats a duration string (e.g., "January 12 – 15, 2026").
	 *
	 * @param array $duration Array with 'start' and 'end' DateTimeImmutable objects.
	 *
	 * @return string Formatted duration string.
	 *
	 * @throws \RuntimeException If duration format is invalid.
	 */
	private function get_formatted_duration_string( $duration ) {
		if ( 'M d – d, Y' !== $this->format && 'F d – d, Y' !== $this->format ) {
			return '';
		}

		$start = $duration['start'];
		$end   = $duration['end'];

		$month_format  = 'M' === $this->format[0] ? 'M' : 'F';
		$in_same_month = $start->format( 'm' ) === $end->format( 'm' );
		$in_same_year  = $start->format( 'Y' ) === $end->format( 'Y' );

		if ( $in_same_year ) {
			$start_str = $this->format_date( "$month_format d", $start );
			$end_str   = $in_same_month
				? $this->format_date( 'd, Y', $end )
				: $this->format_date( "$month_format d Y", $end );

			return $start_str . ' – ' . $end_str;
		}

		$start_str = $this->format_date( "$month_format d, Y", $start );
		$end_str   = $this->format_date( "$month_format d, Y", $end );

		return $start_str . ' – ' . $end_str;
	}

	/**
	 * Formats a date using PHP's date format.
	 *
	 * @param string            $format Format string.
	 * @param DateTimeImmutable $date   Date object to format.
	 *
	 * @return string Formatted date string.
	 */
	private function format_date( $format, $date ) {
		return $date->format( $format );
	}
}
