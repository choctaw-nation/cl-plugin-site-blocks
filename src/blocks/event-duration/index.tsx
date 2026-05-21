import { registerBlockType } from '@wordpress/blocks';
import { scheduled } from '@wordpress/icons';
/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';

registerBlockType( block.name, {
	icon: scheduled,
	edit: Edit,
	save: () => null,
} );
