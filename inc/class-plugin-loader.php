<?php
/**
 * Plugin Loader
 *
 * @package ChoctawNation
 * @subpackage CL_SiteBlocks
 */

namespace ChoctawNation\CL_SiteBlocks;

/** Inits the Plugin */
class Plugin_Loader {
	/**
	 * The directory path of the plugin
	 *
	 * @var string $dir_path
	 */
	private string $dir_path;

	/**
	 * The directory URL of the plugin
	 *
	 * @var string $dir_url
	 */
	private string $dir_url;

	/**
	 * Constructor
	 *
	 * @param string $dir_path The directory path of the plugin
	 * @param string $dir_url The directory URL of the plugin
	 */
	public function __construct( string $dir_path, string $dir_url ) {
		$this->dir_path = $dir_path;
		$this->dir_url  = $dir_url;
	}

	/**
	 * Initializes the Plugin
	 *
	 * @return void
	 */
	public function activate(): void {
		// nothing to do
	}

	/**
	 * Handles Plugin Deactivation
	 * (this is a callback function for the `register_deactivation_hook` function)
	 *
	 * @return void
	 */
	public function deactivate(): void {
		// nothing to do
	}

	/**
	 * Handles Plugin Uninstallation
	 * (this is a callback function for the `register_uninstall_hook` function)
	 */
	public static function uninstall(): void {
		// nothing to do
	}

	/**
	 * Loads the Plugin
	 */
	public function load_plugin(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
		$rest_router = new Rest_Router();
		add_action( 'rest_api_init', array( $rest_router, 'register_routes' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		$query_handler = new Jobs\Query_Handler();
		add_filter( 'pre_render_block', array( $query_handler, 'pre_render_block' ), 10, 2 );
		add_filter( 'render_block_core/query', array( $query_handler, 'cleanup_upcoming_events_query_filter' ), 10, 2 );
	}

	/**
	 * Register Gutenberg Block
	 */
	public function register_blocks() {
		$blocks_path = $this->dir_path;
		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
		 * based on the registered block metadata.
		 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
		 *
		 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
		 */
		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
			wp_register_block_types_from_metadata_collection( $blocks_path . '/build/blocks', $blocks_path . '/build/blocks-manifest.php' );
			return;
		}
	}

	/**
	 * Enqueues the block editor assets
	 */
	public function enqueue_editor_assets() {
		$ids = array(
			'injectTicketsButton',
			'upcomingEventsQueryVariation',
		);
		foreach ( $ids as $file_id ) {
			$asset_file = $this->dir_path . "/build/{$file_id}.asset.php";
			if ( file_exists( $asset_file ) ) {
				$asset = require $asset_file;
				wp_enqueue_script(
					"cl-site-blocks-{$file_id}",
					$this->dir_url . "build/{$file_id}.js",
					$asset['dependencies'],
					$asset['version'],
					array( 'strategy' => 'defer' )
				);
			}
		}
	}
}
