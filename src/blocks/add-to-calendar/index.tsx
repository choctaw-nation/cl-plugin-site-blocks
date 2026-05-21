import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { button } from '@wordpress/icons';
import './style.scss';

/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';
import { ADD_TO_CALENDAR_STORE } from '@shared/consts';

registerBlockType( block.name, {
	icon: button,
	edit: Edit,
	save( { attributes } ) {
		const blockProps = useBlockProps.save( {
			className: 'wp-element-button',
		} );
		return (
			<RichText.Content
				{ ...blockProps }
				data-wp-interactive={ ADD_TO_CALENDAR_STORE }
				data-wp-context={ JSON.stringify( {
					eventId: attributes.eventId,
				} ) }
				data-wp-on--click="actions.downloadIcalFile"
				data-wp-bind--disabled="state.buttonIsDisabled"
				tagName="button"
				value="Add to Calendar"
			/>
		);
	},
} );
