/** The Data structure for the Event */
export type EventData = {
	id: number;
	title: string;
	description: string;
	start: string;
	end: string;
	isAllDay: boolean;
	venue: string;
	website?: string;
	shows?: ShowData[];
};
type ShowData = {
	date: string;
	tickets: string;
	venue: string;
	isSoldOut: boolean;
};
export class FileCreator {
	/**
	 * Default Duration: 60 minutes (min * sec * millisecond)
	 *
	 * @member number #EVENT_DURATION
	 */
	private EVENT_DURATION = 60 * 60 * 1000;

	/** The Event data */
	private event: EventData;

	/** Choctaw Landing Address */
	private address: string;

	constructor( data: EventData ) {
		this.event = data;
		this.address = `272 N State Highway 259A\nHochatown, OK 74728`;
	}
	/**
	 * Generates the ICS file and downloads it
	 *
	 */
	downloadICSFile() {
		const filename = `${ this.event.title }.ics`;
		const data = [
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'BEGIN:VEVENT',
			`DTSTART:${ this.getICalDateStrings( this.event.start ) }`,
			`DTEND:${ this.getICalDateStrings( this.event.end ) }`,
			`SUMMARY:${ this.escapeICSString( this.event.title ) }`,
			`LOCATION:${ this.escapeICSString( this.address ) }`,
			`DESCRIPTION:${ this.escapeICSString( this.createDescription() ) }`,
			'END:VEVENT',
			'END:VCALENDAR',
		].join( '\r\n' );

		const blob = new Blob( [ data ], {
			type: 'text/calendar;charset=utf-8',
		} );

		const link = document.createElement( 'a' );
		link.href = URL.createObjectURL( blob );
		link.download = filename;
		document.body.appendChild( link );
		link.click();
		document.body.removeChild( link );
	}

	/**
	 * Creates a date string in the iCalendar format (YYYYMMDDTHHMMSSZ) from a given date string.
	 */
	private getICalDateStrings( dateString: string ): string {
		const date = new Date( dateString );
		const year = date.getUTCFullYear();
		const month = String( date.getUTCMonth() + 1 ).padStart( 2, '0' );
		const day = String( date.getUTCDate() ).padStart( 2, '0' );
		const hours = String( date.getUTCHours() ).padStart( 2, '0' );
		const minutes = String( date.getUTCMinutes() ).padStart( 2, '0' );
		const seconds = String( date.getUTCSeconds() ).padStart( 2, '0' );
		const iCalDateString = `${ year }${ month }${ day }T${ hours }${ minutes }${ seconds }Z`;
		return iCalDateString;
	}

	/**
	 * Creates the full event description
	 */
	private createDescription(): string {
		let description = this.event.description;
		if ( this.event.venue ) {
			description += `\nVenue: ${ this.event.venue }`;
		}
		if ( this.event.website ) {
			description += `\nWebsite: ${ this.event.website }`;
		}
		if ( this.event.shows && this.event.shows.length > 0 ) {
			description += `\nShows:\n`;
			this.event.shows.forEach( ( show ) => {
				description += `- ${ new Date( show.date ).toLocaleString(
					'en-US',
					{ dateStyle: 'full', timeStyle: 'short' }
				) }: ${ show.venue } (${
					show.isSoldOut
						? 'Sold Out'
						: 'Tickets Available at ' + show.tickets
				})\n`;
			} );
		}
		return description;
	}

	/**
	 * Escapes special characters in a string for use in an ICS file.
	 *
	 * @param value The string to escape.
	 * @return The escaped string.
	 */
	private escapeICSString( value: string ): string {
		return value
			.replace( /\\/g, '\\\\' )
			.replace( /\n/g, '\\n' )
			.replace( /,/g, '\\,' )
			.replace( /;/g, '\\;' );
	}
}
