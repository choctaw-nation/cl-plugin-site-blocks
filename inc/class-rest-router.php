<?php
/**
 * REST Router class for handling REST API routes related to the Add to Calendar block.
 *
 * @package ChoctawNation
 */

namespace ChoctawNation\CL_SiteBlocks;

use ChoctawNation\CL_SiteBlocks\Jobs\Rest_Data_Formatter;
use DateTimeImmutable;
use Error;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST Router class for the Add to Calendar block
 */
class Rest_Router extends WP_REST_Controller {
	/**
	 * Register REST API routes for the Add to Calendar block.
	 */
	public function register_routes() {
		$namespace = 'cl-events/v1';
		register_rest_route(
			$namespace,
			'events/?(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_event' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id'     => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
						'sanitize_callback' => function ( $param ) {
							return absint( $param );
						},
					),
					'format' => array(
						'required'          => true,
						'description'       => 'The format of the calendar data to return (e.g., "ical").',
						'validate_callback' => function ( $param ) {
							return in_array( $param, array( 'ical' ), true );
						},
						'sanitize_callback' => function ( $param ) {
							return sanitize_key( $param );
						},
					),
					'show'   => array(
						'description'       => 'The ISO 8601 date string of the selected show (optional, used for ticketed events).',
						'validate_callback' => function ( $param ) {
							return DateTimeImmutable::createFromFormat( DATE_ATOM, $param ) !== false;
						},
						'sanitize_callback' => function ( $param ) {
							return sanitize_text_field( $param );
						},
					),
				),
			)
		);
	}

	/**
	 * Handle GET request to retrieve event data for the Add to Calendar block.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The REST response object or an error.
	 */
	public function get_event( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id            = $request->get_param( 'id' );
		$selected_show = $request->get_param( 'show' ) ?? '';

		// Fetch the event data based on the ID.
		$event_data = get_post( $id );

		if ( ! $event_data || 'choctaw-events' !== $event_data->post_type ) {
			return new WP_Error( 'event_not_found', 'Event not found.', array( 'status' => 404 ) );
		}
		$formatter = new Rest_Data_Formatter( $event_data, $selected_show );
		try {
			$formatted_data = $formatter->format_event_data();
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => $formatted_data,
				),
				200,
				array(
					'Content-Type'  => 'application/json',
					'Cache-Control' => 'max-age=3600',
				)
			);
		} catch ( Error $e ) {
			return new WP_Error( 'invalid_event_data', $e->getMessage(), array( 'status' => 400 ) );
		}
	}
}
