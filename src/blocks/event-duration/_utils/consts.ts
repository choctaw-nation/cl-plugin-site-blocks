export const formats = [
	{ value: 'F j, Y', label: 'January DD, YYYY' },
	{ value: 'M d, Y', label: 'Jan 01, YYYY' },
	{ value: 'm/d/Y', label: 'MM/DD/YYYY' },
	{ value: 'g:i a', label: 'h:mm pm' },
	{ value: 'F j, Y g:i a', label: 'January DD, YYYY h:mm pm' },
	{ value: 'l F j, Y • g:i a', label: 'Friday, January DD, YYYY • h:mm pm' },
	{ value: 'M d, Y • g:i a', label: 'Jan DD, YYYY • h:mm pm' },
] as const;

export const durationFormats = [
	{ label: 'January 12 – 15, 2026', value: 'F d – d, Y' },
	{ label: 'Jan 12 – 15, 2026', value: 'M d – d, Y' },
	{ label: '9:00am – 5:00pm', value: 'g:i a' },
] as const;
