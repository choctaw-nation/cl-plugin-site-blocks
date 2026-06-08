import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as coreDataStore } from '@wordpress/core-data';
import { AcfEventFields } from './types';

interface UseAcfArgs {
	postId?: number;
	postType?: string;
}

export default function useAcf( args: UseAcfArgs = {} ) {
	const { postId, postType } = args;
	const acf = useSelect(
		( select ) => {
			if ( postId && postType ) {
				const post = select( coreDataStore ).getEntityRecord(
					'postType',
					postType,
					postId,
					{ context: 'edit' }
				);
				return post?.acf as AcfEventFields;
			}
			return select( editorStore ).getEditedPostAttribute(
				'acf'
			) as AcfEventFields;
		},
		[ postId, postType ]
	);

	const canUseDuration = acf?.end_date || acf?.end_time;
	return {
		acf,
		isLoading: ! acf,
		canUseDuration,
	};
}
