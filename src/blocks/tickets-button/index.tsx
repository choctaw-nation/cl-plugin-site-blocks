import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { button } from '@wordpress/icons';
import './style.scss';

/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';
import { GET_TICKETS_STORE } from '@shared/consts';

registerBlockType( block.name, {
	icon: button,
	edit: Edit,
	save: () => {
		const blockProps = useBlockProps.save();
		const containerClasses = blockProps.className?.split( ' ' );
		const classes: string[] = [];
		if ( ! containerClasses?.includes( 'is-style-text' ) ) {
			classes.push( 'wp-element-button' );
		}
		return (
			<div { ...blockProps } data-wp-interactive={ GET_TICKETS_STORE }>
				{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
				<a
					className={ classes.join( ' ' ) }
					data-wp-bind--hidden="state.totallySoldOut"
					data-wp-bind--href="state.activeTicketLink"
					target="_blank"
					data-wp-bind--aria-disabled="state.ticketLinkIsNotReady"
					data-wp-class--disabled="state.ticketLinkIsNotReady"
					rel="noopener noreferrer"
				>
					Get Tickets
				</a>
				<button
					className={ classes.join( ' ' ) }
					data-wp-bind--disabled="state.totallySoldOut"
					data-wp-class--disabled="state.totallySoldOut"
					data-wp-bind--hidden="state.canBuyTickets"
				>
					Sold Out
				</button>
			</div>
		);
	},
} );
