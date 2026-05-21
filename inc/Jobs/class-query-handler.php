<?php
/**
 * Query Loop Handler class for managing query loops related to upcoming events.
 *
 * @package ChoctawNation
 */

namespace ChoctawNation\CL_SiteBlocks\Jobs;

use WP_Block;
use WP_REST_Request;

/**
 * Query_Handler class to modify query variables for the Query Loop block to filter for upcoming events.
 */
class Query_Handler {
	/**
	 * The post type to target for query modifications.
	 *
	 * @var string $post_type
	 */
	private string $post_type;

	/**
	 * The query modifications to apply for upcoming events.
	 *
	 * @var array $events_query_mods
	 */
	private array $events_query_mods;

	/**
	 * The namespace to identify the specific Query Loop block variation for upcoming events.
	 *
	 * @var string $query_loop_namespace
	 */
	private string $query_loop_namespace;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->post_type            = 'choctaw-events';
		$this->query_loop_namespace = 'cl-site-blocks/choctaw-events-upcoming';
		$this->events_query_mods    = array(
			'meta_key'   => 'start_date',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'meta_query' => array(
				array(
					'key'     => 'start_date',
					'value'   => current_time( 'Ymd' ),
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			),
		);
		add_action(
			'init',
			function () {
				add_filter( 'rest_choctaw-events_query', array( $this, 'handle_rest_query' ), 10, 2 );
			}
		);
	}

	/**
	 * Pre-render callback for the Query Loop block to conditionally apply query modifications for upcoming events.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/pre_render_block/
	 *
	 * @param string|null $pre_render The pre-rendered block content, or null to continue with normal rendering.
	 * @param array       $block The block instance being rendered.
	 */
	public function pre_render_block( ?string $pre_render, $block ) {
		if ( 'core/query' !== $block['blockName'] ) {
			return $pre_render;
		}
		if ( empty( $block['attrs']['namespace'] ) ) {
			return $pre_render;
		}
		if ( $this->query_loop_namespace !== $block['attrs']['namespace'] ) {
			return $pre_render;
		}
		if ( ! has_filter( 'query_loop_block_query_vars', array( $this, 'update_query_loop_vars' ) ) ) {
			add_filter( 'query_loop_block_query_vars', array( $this, 'update_query_loop_vars' ), 10, 2 );
		}
		return $pre_render;
	}

	/**
	 * Modifies the query variables for the Query Loop block to filter for upcoming events.
	 *
	 * @param array $query The existing query variables.
	 * @return array The modified query variables.
	 */
	public function update_query_loop_vars( $query ) {
		$query_post_type = $query['post_type'] ?? '';
		if ( $this->post_type !== $query_post_type ) {
			return $query;
		}
		$query = array_merge( $query, $this->events_query_mods );
		return $query;
	}

	/**
	 * Handles REST API queries for the choctaw-events post type to filter for upcoming events.
	 *
	 * @param array           $args The existing query arguments.
	 * @param WP_REST_Request $request The REST API request object.
	 * @return array The modified query arguments.
	 */
	public function handle_rest_query( array $args, WP_REST_Request $request ): array {
		$events_query = $request->get_param( 'eventsQuery' );
		if ( empty( $events_query ) || 'upcoming' !== $events_query ) {
			return $args;
		}
		$args = array_merge( $args, $this->events_query_mods );
		return $args;
	}

	/**
	 * Cleans up the query modifications after rendering the Query Loop block to prevent affecting other queries.
	 *
	 * This should be hooked to the `pre_render_block` filter with a later priority than the `update_query_loop_vars` method.
	 *
	 * @param string|null $block_content The block content after rendering.
	 * @param array       $block The block instance being rendered.
	 */
	public function cleanup_upcoming_events_query_filter( $block_content, $block ) {
		if ( ( $block['attrs']['namespace'] ?? '' ) !== $this->query_loop_namespace ) {
			return $block_content;
		}

		remove_filter( 'query_loop_block_query_vars', array( $this, 'update_query_loop_vars' ), 10 );

		return $block_content;
	}
}