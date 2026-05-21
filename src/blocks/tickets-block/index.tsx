import { registerBlockType } from '@wordpress/blocks';
import { currencyDollar } from '@wordpress/icons';
import './style.scss';
/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';

registerBlockType( block.name, {
	icon: currencyDollar,
	edit: Edit,
	save: () => null,
} );
