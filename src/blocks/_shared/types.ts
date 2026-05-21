import { durationFormats, formats } from '../event-duration/_utils/consts';

export type AcfEventFields = {
	swiper_image: string | number;
	fallback_image: string | number;
	is_ticketed_event: 'true' | 'false';
	ticket_details: TicketedEventFields[];
	is_all_day: boolean;
	start_date: string;
	end_date: string | null;
	start_time: string;
	end_time: string | null;
	brief_description: string;
	event_website: string | null;
};
export type TicketedEventFields = {
	event_date: string;
	event_time: string;
	ticket_link: string;
	use_alternate_location: boolean;
	alternate_location: string | null;
	is_sold_out: boolean;
};

export type AllowedDateTimeStringFormat =
	| ( typeof formats )[ number ][ 'value' ]
	| ( typeof durationFormats )[ number ][ 'value' ];
