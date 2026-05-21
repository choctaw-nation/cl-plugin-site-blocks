import { TicketedEventFields } from '@shared/types';
import useAcf from '@shared/useAcf';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, Spinner, Tip } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';
import { date } from '@wordpress/date';
import { useState, useEffect } from '@wordpress/element';
import { ShowData } from './types';
import ShowRadio from './components/ShowRadio';

export default function Edit() {
	const blockProps = useBlockProps();
	const { acf, isLoading } = useAcf();
	const [ shows, setShows ] = useState< ShowData[] >( [] );
	const venue = useSelect( ( select ) => {
		const venueId = select( editorStore ).getEditedPostAttribute(
			'choctaw-events-venue'
		);
		if ( venueId ) {
			const venueRecord = select( coreStore ).getEntityRecord(
				'taxonomy',
				'choctaw-events-venue',
				venueId
			);
			if ( venueRecord ) {
				return venueRecord.name;
			}
		}
		return null;
	}, [] );
	const isTicketedEvent = acf?.is_ticketed_event;
	const hasShows = acf?.ticket_details && acf.ticket_details.length > 0;

	useEffect( () => {
		if ( ! acf ) {
			return;
		}
		if ( isTicketedEvent && hasShows ) {
			const showsWithDateTimes = acf.ticket_details.map(
				( show: TicketedEventFields ) => {
					const location = show.alternate_location || venue;
					const eventDate =
						show.event_date.slice( 0, 4 ) +
						'-' +
						show.event_date.slice( 4, 6 ) +
						'-' +
						show.event_date.slice( 6 );
					return {
						eventDateTime: date(
							'l F j, Y • g:i A',
							eventDate + ' ' + show.event_time
						),
						venue: location,
						isSoldOut: show.is_sold_out,
					} as ShowData;
				}
			);
			setShows( showsWithDateTimes );
		}
	}, [ acf, venue, hasShows, isTicketedEvent ] );
	return (
		<>
			<InspectorControls>
				<Panel>
					<PanelBody initialOpen={ true }>
						<Tip>
							This block is only for ticketed events. If your
							event has tickets, please add them in the event
							details ACF boxes.
						</Tip>
					</PanelBody>
				</Panel>
			</InspectorControls>
			<div { ...blockProps }>
				{ isLoading && <Spinner /> }
				{ ! isLoading && isTicketedEvent && hasShows ? (
					<>
						<fieldset>
							{ shows.map( ( show: ShowData, index ) => (
								<ShowRadio
									show={ show }
									venue={ venue }
									index={ index }
									key={ index }
								/>
							) ) }
						</fieldset>
					</>
				) : (
					<Tip>
						This block is only for ticketed events. If your event
						has tickets, please add them in the event details ACF
						boxes.
					</Tip>
				) }
			</div>
		</>
	);
}
