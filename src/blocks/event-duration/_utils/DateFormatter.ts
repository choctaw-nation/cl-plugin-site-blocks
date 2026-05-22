import { getDate, date } from '@wordpress/date';
import { AcfEventFields, AllowedDateTimeStringFormat } from '@shared/types';

export class DateFormatter {
	public startDate: Date | null;
	private format: AllowedDateTimeStringFormat;
	private asDuration: boolean;
	private canUseDuration: boolean;
	private endDate: Date | null;
	private ticketDetails: AcfEventFields[ 'ticket_details' ];
	private isTicketedEvent: boolean;
	private defaultStartTime = '00:00:00';

	constructor(
		acf: AcfEventFields,
		format: AllowedDateTimeStringFormat,
		asDuration: boolean
	) {
		if ( ! acf.start_date ) {
			throw new Error(
				'Start date is required to format the event date.'
			);
		}
		this.startDate = this.createDateTime( acf.start_date, acf.start_time );
		this.endDate = this.createDateTime(
			acf.end_date || acf.start_date,
			acf.end_time
		);
		this.ticketDetails = acf.ticket_details;
		this.format = format;
		this.asDuration = asDuration;
		this.canUseDuration =
			!! acf?.end_date ||
			acf.ticket_details?.some(
				( ticket ) => ticket.event_date !== acf.start_date
			);
		this.isTicketedEvent = acf.is_ticketed_event === 'true';
	}

	/**
	 * Creates a Date object from an ACF date string and an optional time string. If the time string is not provided, it defaults to 12:00 am.
	 * @param dateString Date string as "Ymd"
	 * @param time       Time string as "H:M:S"
	 * @return A Date object representing the date and time, or null if the date string is invalid.
	 */
	createDateTime(
		dateString: string | null,
		time: string | null = null
	): Date | null {
		if ( ! dateString ) {
			return null;
		}
		const datePart = getDate( dateString );
		if ( ! datePart ) {
			return null;
		}
		if ( ! time ) {
			time = this.defaultStartTime;
		}
		const timeParts = time.split( ':' );
		if ( timeParts.length !== 3 ) {
			return datePart; // If time format is invalid, return the date without time
		}
		const [ hours, minutes, seconds ] = timeParts.map( Number );
		datePart.setHours( hours, minutes, seconds );
		if ( ! timeParts ) {
			return datePart; // If time format is invalid, return the date without time
		}

		return datePart;
	}

	/**
	 * Returns the formatted date string based on the provided ACF event fields, format, and duration settings.
	 * @throws Will throw an error if the start date is missing or invalid.
	 */
	getFormattedString(): string {
		if ( ! this.startDate ) {
			throw new Error(
				'Start date is required to format the event date.'
			);
		}
		if ( this.asDuration ) {
			if ( this.format === 'g:i a' && this.canUseDuration ) {
				return this.getTimeDuration();
			}
			if ( ! this.canUseDuration ) {
				const format = this.format.startsWith( 'M' )
					? 'M d, Y'
					: 'F d, Y';
				return date( format, this.startDate );
			}
			if ( this.isTicketedEvent ) {
				const ticketedDuration = this.getTicketedEventDuration();
				if ( ! ticketedDuration ) {
					throw new Error(
						'Ticketed event duration could not be determined.'
					);
				}
				return this.getFormattedDurationString( ticketedDuration );
			}
			return this.getFormattedDurationString( {
				start: this.startDate,
				end: this.endDate!,
			} );
		}
		return date( this.format, this.startDate );
	}

	private getTimeDuration(): string {
		const inSameMeridiem =
			this.startDate?.getHours()! < 12 === this.endDate?.getHours()! < 12;
		const times = {
			start: inSameMeridiem
				? date( 'g:i', this.startDate! )
				: date( 'g:i a', this.startDate! ),
			end: this.endDate ? date( 'g:i a', this.endDate! ) : '',
		};

		return `${ times.start } – ${ times.end }`;
	}

	/**
	 * Loops through the ticketDetails object to find (and create) the first and last Date objects of the event
	 */
	private getTicketedEventDuration(): { start: Date; end: Date } | null {
		if ( ! this.ticketDetails || this.ticketDetails.length === 0 ) {
			return null;
		}
		let start: Date | null = null,
			end: Date | null = null;
		this.ticketDetails.forEach( ( ticket ) => {
			const currentDate = this.createDateTime(
				ticket.event_date,
				ticket.event_time
			)!;
			if ( ! start || currentDate < start ) {
				start = currentDate;
			}
			if ( ! end || currentDate > end ) {
				end = currentDate;
			}
		} );

		if ( ! start || ! end ) {
			return null;
		}

		return {
			start,
			end,
		};
	}

	private getFormattedDurationString( duration: {
		start: Date;
		end: Date;
	} ): string {
		// Handle duration formats that only show month/day for start and day/year for end
		if ( this.format !== 'M d – d, Y' && this.format !== 'F d – d, Y' ) {
			throw new Error(
				'Invalid duration format. Expected "M d – d, Y" or "F d – d, Y".'
			);
		}
		const parts = {
			start: {
				month: duration.start.getMonth(),
				day: duration.start.getDate(),
				year: duration.start.getFullYear(),
			},
			end: {
				month: duration.end.getMonth(),
				day: duration.end.getDate(),
				year: duration.end.getFullYear(),
			},
		};
		const monthFormat = this.format.startsWith( 'M' ) ? 'M' : 'F';
		const inSameMonth = parts.start.month === parts.end.month;
		const inSameYear = parts.start.year === parts.end.year;
		const inSameDay =
			inSameMonth && inSameYear && parts.start.day === parts.end.day;

		if ( inSameDay ) {
			return date( `${ monthFormat } d, Y`, duration.start );
		}
		const dateString = { start: '', end: '' };
		if ( inSameYear ) {
			dateString.start = date( `${ monthFormat } d`, duration.start );
			dateString.end = inSameMonth
				? date( `d, Y`, duration.end )
				: date( `${ monthFormat } d Y`, duration.end );
			return `${ dateString.start } – ${ dateString.end }`;
		}
		dateString.start = date( `${ monthFormat } d, Y`, duration.start );
		dateString.end = date( `${ monthFormat } d, Y`, duration.end );
		return `${ dateString.start } – ${ dateString.end }`;
	}
}
