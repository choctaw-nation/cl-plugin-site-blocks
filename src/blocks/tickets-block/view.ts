import { GET_TICKETS_STORE } from '@shared/consts';
import { store, getContext } from '@wordpress/interactivity';

type ServerState = {
	shows: Array< TicketedEventDetails >;
};

type TicketedEventDetails = {
	key: string;
	eventDateTime: string;
	prettyEventDateTime: string;
	ticketLink: string;
	location: string;
	isSoldOut: boolean;
};
type State = {
	activeTicketLink: string | null;
	canBuyTickets: () => boolean;
	totallySoldOut: () => boolean;
	ticketLinkIsNotReady: () => boolean;
	selectedShow: string | null;
};
export const { state, callbacks } = store( GET_TICKETS_STORE, {
	state: {
		activeTicketLink: null,
		selectedShow: null,
		canBuyTickets() {
			return state.shows.some( ( show ) => ! show.isSoldOut );
		},
		ticketLinkIsNotReady() {
			return state.activeTicketLink === null && state.canBuyTickets();
		},
		totallySoldOut() {
			return state.shows.every( ( show ) => show.isSoldOut );
		},
	} as State & ServerState,
	actions: {
		setActiveShow() {
			const context = getContext< { show: TicketedEventDetails } >();
			state.activeTicketLink = context.show.ticketLink;
			state.selectedShow = context.show.eventDateTime;
		},
	},
	callbacks: {
		isActiveShow() {
			const context = getContext< { show: TicketedEventDetails } >();
			return state.activeTicketLink === context.show.ticketLink;
		},
		ticketLinkIsReady() {
			return state.activeTicketLink !== null;
		},
	},
} );
