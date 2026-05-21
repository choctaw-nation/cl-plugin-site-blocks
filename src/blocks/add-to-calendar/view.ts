import { ADD_TO_CALENDAR_STORE, GET_TICKETS_STORE } from '@shared/consts';
import { store, getContext } from '@wordpress/interactivity';
import { FileCreator } from './view/FileCreator';

type State = {
	isLoading: boolean;
	errorMessage: string | null;
	buttonIsDisabled: () => boolean;
};
type Context = {
	eventId: number;
};

const { state: ticketsState } = store( GET_TICKETS_STORE );

const { state } = store( ADD_TO_CALENDAR_STORE, {
	state: {
		buttonIsDisabled() {
			return ticketsState.selectedShow === null || state.isLoading;
		},
	} as State,
	actions: {
		async downloadIcalFile() {
			state.isLoading = true;
			try {
				const context = getContext< Context >();
				const response = await fetch(
					`/wp-json/cl-events/v1/events/${
						context.eventId
					}?format=ical${
						ticketsState.selectedShow
							? `&show=${ ticketsState.selectedShow }`
							: ''
					}`
				);
				if ( ! response.ok ) {
					throw new Error( 'Network response was not ok' );
				}
				const data = await response.json();
				const fileCreator = new FileCreator( data.data );
				fileCreator.downloadICSFile();
			} catch ( error ) {
				state.errorMessage = 'Error downloading iCal file! ' + error;
			} finally {
				state.isLoading = false;
			}
		},
	},
} );
