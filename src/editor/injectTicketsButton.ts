import { subscribe, select, dispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { store as blockEditorStore } from '@wordpress/block-editor';
import domReady from '@wordpress/dom-ready';

const TICKETS_BLOCK = 'cl-site-blocks/tickets-block';
const TICKETS_BUTTON_BLOCK = 'cl-site-blocks/tickets-button';

let isHandlingChange = false;
domReady( () => {
	subscribe( () => {
		if ( isHandlingChange ) {
			return;
		}

		const blocks = select( blockEditorStore ).getBlocks();
		const hasTickets = findBlock( blocks, TICKETS_BLOCK );
		const hasTicketsButton = findBlock( blocks, TICKETS_BUTTON_BLOCK );
		if ( hasTicketsButton && ! hasTickets ) {
			// If the tickets button exists but there are no tickets blocks, remove the tickets button
			const ticketsButtonBlock = findBlock(
				blocks,
				TICKETS_BUTTON_BLOCK
			);
			if ( ticketsButtonBlock ) {
				isHandlingChange = true;
				dispatch( blockEditorStore ).removeBlock( ticketsButtonBlock );
				isHandlingChange = false;
			}
			return;
		}
		if ( ! hasTickets || hasTicketsButton ) {
			return;
		}

		try {
			isHandlingChange = true;

			dispatch( blockEditorStore ).insertBlock(
				createBlock( TICKETS_BUTTON_BLOCK )
			);
		} catch ( err ) {
			// eslint-disable-next-line no-console
			console.error( err );
			return;
		} finally {
			isHandlingChange = false;
		}
	} );
} );

/**
 * Recursively search an array of block objects (and inner blocks) for a block of a given type
 *
 * @param blockList An array of block objects
 * @param blockType the name of the block to find
 * @return the clientId of the found block, or false if not found
 */
function findBlock( blockList: any[], blockType: string ): string | false {
	if ( ! blockList || blockList.length === 0 ) {
		return false;
	}
	for ( const block of blockList ) {
		if ( block.name === blockType ) {
			return block.clientId;
		}
		if ( block.innerBlocks?.length > 0 ) {
			const found = findBlock( block.innerBlocks, blockType );
			if ( found ) {
				return found;
			}
		}
	}
	return false;
}
