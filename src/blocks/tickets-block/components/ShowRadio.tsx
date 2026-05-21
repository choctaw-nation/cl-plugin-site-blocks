import { store as coreStore } from '@wordpress/core-data';
import { Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { ShowData } from '../types';

export default function ShowRadio( {
	show,
	venue,
	index,
}: {
	show: ShowData;
	venue: string | null;
	index: number;
} ) {
	const [ isLoading, setIsLoading ] = useState( true );
	const [ location, setLocation ] = useState( null );
	useSelect(
		( select ) => {
			if ( typeof show.venue !== 'number' ) {
				setIsLoading( false );
				return;
			}
			const record = select( coreStore ).getEntityRecord(
				'taxonomy',
				'choctaw-events-venue',
				show.venue
			);
			if ( record ) {
				setLocation( record.name );
				setIsLoading( false );
			}
		},
		[ show.venue ]
	);
	const id = `show-${ index }`;
	return isLoading ? (
		<Spinner />
	) : (
		<div key={ index } className={ show.isSoldOut ? 'sold-out' : '' }>
			<label htmlFor={ id }>
				<input
					type="radio"
					name="active-show"
					id={ id }
					disabled={ show.isSoldOut }
				/>{ ' ' }
				{ show.eventDateTime } at { location || venue }{ ' ' }
				{ show.isSoldOut && '(Sold Out)' }
			</label>
		</div>
	);
}
