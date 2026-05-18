import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';

registerBlockType( block.name, {
	icon: video,
	edit: Edit,
} );
