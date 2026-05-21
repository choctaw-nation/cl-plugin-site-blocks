import { AcfEventFields, AllowedDateTimeStringFormat } from '@shared/types';
import { DateFormatter } from './_utils/DateFormatter';

export default function useFormattedDate(
	acf: AcfEventFields,
	format: AllowedDateTimeStringFormat,
	asDuration: boolean
) {
	if ( ! acf?.start_date ) {
		return '';
	}
	const formatter = new DateFormatter( acf, format, asDuration );
	return formatter.getFormattedString();
}
