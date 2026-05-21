import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
export default function Edit() {
	const [ elTag, setElTag ] = useState( 'button' );
	const blockProps = useBlockProps();
	const containerClasses = blockProps.className?.split( ' ' );
	const classes: string[] = [];
	const buttonIsLink = containerClasses?.includes( 'is-style-text' );
	if ( ! buttonIsLink ) {
		classes.push( 'wp-element-button' );
	}
	useEffect( () => {
		if ( buttonIsLink ) {
			setElTag( 'a' );
		} else {
			setElTag( 'button' );
		}
	}, [ buttonIsLink ] );
	return (
		<div { ...blockProps }>
			<RichText
				className={ classes.join( ' ' ) }
				tagName={ elTag }
				value="Get Tickets"
				onChange={ () => {} }
			></RichText>
		</div>
	);
}
