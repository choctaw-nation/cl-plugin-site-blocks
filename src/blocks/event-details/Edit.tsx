import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Tip } from '@wordpress/components';

import useAcf from '@shared/useAcf';

export default function Edit( { context } ) {
	const { acf, isLoading } = useAcf( context );

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title="View Details Button" initialOpen={ true }>
					<Tip>
						The View Details button will dynamically render if the
						event is ticketed or if it has any blocks.
					</Tip>
				</PanelBody>
			</InspectorControls>
			{ acf?.is_ticketed_event === 'false' ? null : (
				<div { ...blockProps }>
					{ isLoading && 'Loading...' }
					{ ! isLoading && 'View Details' }
				</div>
			) }
		</>
	);
}
