import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import useAcf from '@shared/useAcf';
import {
	Spinner,
	Panel,
	PanelBody,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

import useFormattedDate from './useFormattedDate';
import { durationFormats, formats } from './_utils/consts';

export default function Edit( { attributes, setAttributes, context } ) {
	const { format, asDuration } = attributes;
	const effectiveFormat =
		asDuration &&
		! durationFormats.some( ( option ) => option.value === format )
			? 'F d – d, Y'
			: format;
	const { acf, isLoading, canUseDuration } = useAcf( context );
	const date = useFormattedDate( acf, effectiveFormat, asDuration );
	const blockProps = useBlockProps();
	const [ options, setOptions ] = useState<
		readonly {
			label: string;
			value: string;
		}[]
	>( formats );

	useEffect( () => {
		if ( ! acf ) {
			return;
		}
		if ( asDuration ) {
			if (
				durationFormats.some( ( option ) => option.value === format )
			) {
				return;
			}
			setAttributes( { format: 'F d – d, Y' } );
		}
		if ( asDuration && canUseDuration ) {
			if ( ! acf.start_time || ! acf.end_time ) {
				setOptions(
					durationFormats.filter(
						( option ) => option.value !== 'g:i a'
					)
				);
				if ( format === 'g:i a' ) {
					setAttributes( { format: 'F d – d, Y' } );
				}
				return;
			}
			setOptions( durationFormats );
		} else {
			setOptions( formats );
		}
	}, [ asDuration, acf, canUseDuration, format, setAttributes ] );
	if ( isLoading ) {
		return <Spinner />;
	}

	return (
		<>
			<InspectorControls>
				<Panel>
					<PanelBody title="Settings">
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label="Date Format"
							value={ format }
							options={ options }
							onChange={ ( value ) =>
								setAttributes( { format: value } )
							}
						/>
						{ canUseDuration && (
							<ToggleControl
								__nextHasNoMarginBottom
								label="As Duration"
								checked={ asDuration }
								onChange={ ( value ) =>
									setAttributes( { asDuration: value } )
								}
								help="Shows the duration between the start and end date instead of the start date."
							/>
						) }
					</PanelBody>
				</Panel>
			</InspectorControls>
			<time { ...blockProps }>{ date }</time>
		</>
	);
}
