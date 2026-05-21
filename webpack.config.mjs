import path from 'path';
import { fileURLToPath } from 'url';
import config from '@wordpress/scripts/config/webpack.config.js';

const __filename = fileURLToPath( import.meta.url );
const __dirname = path.dirname( __filename );

const configs = Array.isArray( config ) ? config : [ config ];

const addAlias = ( webpackConfig ) => ( {
	...webpackConfig,
	resolve: {
		...webpackConfig.resolve,
		alias: {
			...webpackConfig.resolve?.alias,
			'@shared': path.resolve( __dirname, './src/blocks/_shared' ),
		},
	},
} );

const addEditorEntry = ( webpackConfig ) => {
	const isModuleBuild = webpackConfig.output?.module;

	if ( isModuleBuild ) {
		return webpackConfig;
	}

	const originalEntry = webpackConfig.entry;

	return {
		...webpackConfig,
		entry: async () => {
			const entries =
				typeof originalEntry === 'function'
					? await originalEntry()
					: originalEntry;

			return {
				...entries,
				injectTicketsButton: path.resolve(
					__dirname,
					'./src/editor/injectTicketsButton.ts'
				),
			};
		},
	};
};

export default configs.map( addAlias ).map( addEditorEntry );
