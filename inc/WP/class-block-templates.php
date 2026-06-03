<?php
/**
 * Registers block templates
 *
 * @package ChoctawNation
 */

namespace ChoctawNation\CL_SiteBlocks\Blocks;

/**
 * Class responsible for registering block templates for the plugin's custom post types and any custom block patterns.
 */
class Block_Templates {
	/**
	 * The post type slug to target for block templates.
	 *
	 * @var string $post_type_slug
	 */
	private string $post_type_slug;

	/**
	 * The directory path of the plugin (used for loading block pattern content).
	 *
	 * @var string $dir_path
	 */
	private string $dir_path;

	/**
	 * Constructor
	 *
	 * @param string $post_type_slug The slug of the post type to target for block templates (e.g., 'choctaw-events').
	 * @param string $dir_path The directory path of the plugin (not used in this class but can be stored for future use).
	 */
	public function __construct( string $post_type_slug, string $dir_path ) {
		$this->post_type_slug = $post_type_slug;
		$this->dir_path       = $dir_path;
	}

	/**
	 * Registers the default Events block template
	 */
	public function register_default_events_blocks(): void {
		// Get the current post type object
		$post_type_object = get_post_type_object( $this->post_type_slug );

		if ( $post_type_object ) {
			// Add a block template
			$post_type_object->template = array(
				array(
					'core/group',
					array(
						'tagName' => 'main',
						'align'   => 'wide',
						'layout'  => array(
							'type' => 'default',
						),
					),
					array(
						array(
							'core/columns',
							array(
								'align' => 'wide',
							),
							array(
								array(
									'core/column',
									array(),
									array(
										array(
											'core/post-featured-image',
											array(
												'scale' => 'contain',
											),
											array(),
										),

									),
								),
								array(
									'core/column',
									array(),
									array(
										array(
											'core/post-title',
											array(
												'level' => 1,
												'style' => array(
													'typography' => array(
														'textTransform' => 'uppercase',
													),
													'spacing' => array(
														'padding' => array(
															'top' => 'var:preset|spacing|sm',
															'bottom' => 'var:preset|spacing|sm',
															'left' => 'var:preset|spacing|base',
															'right' => 'var:preset|spacing|base',
														),
													),
												),
											),
											array(),
										),
										array(
											'core/group',
											array(
												'className' => 'fw-bold',
												'fontSize' => 'sm',
												'layout'   => array(
													'type' => 'flex',
													'flexWrap' => 'nowrap',
												),
											),
											array(
												array(
													'cl-site-blocks/event-duration',
													array(
														'format' => 'F d – d,
													Y',
														'asDuration' => true,
													),
													array(),
												),
												array(
													'core/post-terms',
													array(
														'term' => 'choctaw-events-venue',
														'prefix' => 'at ',
													),
													array(),
												),

											),
										),
										array(
											'core/paragraph',
											array(),
											array(),
										),
										array(
											'core/paragraph',
											array(),
											array(),
										),
										array(
											'cl-site-blocks/tickets-block',
											array(),
											array(),
										),
										array(
											'core/group',
											array(
												'layout' => array(
													'type' => 'flex',
													'flexWrap' => 'nowrap',
												),
											),
											array(
												array(
													'cl-site-blocks/tickets-button',
													array(
														'className' => 'is-style-primary',
													),
													array(),
												),
												array(
													'cl-site-blocks/add-to-calendar',
													array(
														'eventId' => 2043,
														'className' => 'is-style-outline',
													),
													array(),
												),

											),
										),
										array(
											'core/paragraph',
											array(),
											array(),
										),

									),
								),

							),
						),

					),
				),
			);
		}
	}

	/**
	 * Registers custom block patterns
	 */
	public function register_block_patterns(): void {
		register_block_pattern(
			'cl-site-blocks/title-bar',
			array(
				'title'       => 'Title Bar',
				'description' => 'A callout section with heading, text, and background image',
				'categories'  => array( 'text' ),
				'content'     => require $this->dir_path . '/patterns/title-bar.php',
			)
		);
	}
}
