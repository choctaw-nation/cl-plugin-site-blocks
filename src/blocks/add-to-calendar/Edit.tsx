import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { useEffect } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const currentPost = useSelect(
		( select ) => select( editorStore ).getCurrentPost(),
		[]
	);
	useEffect( () => {
		if ( ! attributes.eventId && currentPost?.id ) {
			setAttributes( { eventId: currentPost.id } );
		}
	}, [ currentPost, attributes.eventId, setAttributes ] );

	const blockProps = useBlockProps( { className: 'wp-element-button' } );

	return (
		<RichText
			{ ...blockProps }
			tagName={ 'button' }
			value="Add to Calendar"
			onChange={ () => {} }
		/>
	);
}
