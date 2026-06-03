import { registerBlockType } from '@wordpress/blocks';
import { button } from '@wordpress/icons';
import './style.scss';

/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';

registerBlockType( block.name, {
	icon: button,
	edit: Edit,
	save: () => null,
} );
