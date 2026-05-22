import { registerBlockVariation } from '@wordpress/blocks';
import { loop } from '@wordpress/icons';

const MY_VARIATION_NAME = 'cl-site-blocks/choctaw-events-upcoming';

registerBlockVariation( 'core/query', {
	name: MY_VARIATION_NAME,
	title: 'Upcoming Events Loop',
	description: 'Displays upcoming events',
	icon: loop,
	isActive: ( { namespace, query } ) =>
		namespace === MY_VARIATION_NAME && query?.postType === 'choctaw-events',
	attributes: {
		namespace: MY_VARIATION_NAME,
		query: {
			postType: 'choctaw-events',
			order: 'asc',
			inherit: false,
			eventsQuery: 'upcoming',
		},
	},
	allowedControls: [ 'parents' ],
	scope: [ 'inserter' ],
	innerBlocks: [
		[
			'core/post-template',
			{ layout: { type: 'grid', columnCount: 3 } },
			[
				[ 'core/post-featured-image' ],
				[ 'core/post-title', { level: 3, isLink: true } ],
			],
		],
	],
} );
