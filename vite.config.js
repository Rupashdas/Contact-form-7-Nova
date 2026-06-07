/**
 * Vite build config for CF7 Nova Lite admin UI.
 *
 * Design goals:
 *  1. One bundle per admin page (code split, lazy load).
 *  2. Auto-discover entries from `ui/apps/<name>/index.jsx` so adding a new
 *     admin page never requires editing this file.
 *  3. Externalize `@wordpress/*` packages — they ship inside WordPress
 *     itself as `wp.element`, `wp.components`, etc. Bundling them again would
 *     ship duplicate React and break wp.element references across plugins.
 *  4. Emit a manifest so PHP can resolve hashed filenames at enqueue time.
 */

import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );

/**
 * Discover every `ui/apps/<name>/index.jsx` and return a Rollup-style
 * entries object: `{ name: '/abs/path/to/index.jsx' }`.
 *
 * Each subfolder becomes its own bundle. This means Phase 5 only has to
 * create `ui/apps/submissions/index.jsx` to get an enqueue-ready file —
 * no Vite config edit needed.
 */
function discoverEntries() {
	const appsDir = path.resolve( __dirname, 'ui/apps' );
	if ( ! fs.existsSync( appsDir ) ) {
		return {};
	}
	const entries = {};
	for ( const name of fs.readdirSync( appsDir ) ) {
		// Skip hidden / underscore folders so we can stash WIP code under
		// `ui/apps/_scratch/` without it polluting the build.
		if ( name.startsWith( '.' ) || name.startsWith( '_' ) ) {
			continue;
		}
		const entry = path.join( appsDir, name, 'index.jsx' );
		if ( fs.existsSync( entry ) ) {
			entries[ name ] = entry;
		}
	}
	return entries;
}

/**
 * WordPress packages that are guaranteed to be available on the page as
 * globals (when enqueued with the matching wp- handle as a dependency).
 * Externalizing them keeps each bundle small and prevents duplicate React.
 */
const wpExternals = {
	'@wordpress/element':   'wp.element',
	'@wordpress/components': 'wp.components',
	'@wordpress/i18n':       'wp.i18n',
	'@wordpress/api-fetch':  'wp.apiFetch',
	react:                   'wp.element',
	'react-dom':             'wp.element',
};

export default defineConfig( {
	plugins: [ react() ],

	// Vite dev server config — only matters when running `npm run dev` for
	// hot-reload work against a live WP install.
	server: {
		port: 5173,
		strictPort: true,
		cors: true,
	},

	build: {
		outDir: 'build',
		emptyOutDir: true,
		manifest: true,           // Emits build/.vite/manifest.json for PHP.
		sourcemap: true,          // Helpful during dev; strip on WP.org release.
		target: 'es2019',         // Matches @wordpress/browserslist-config.

		rollupOptions: {
			input: discoverEntries(),
			external: Object.keys( wpExternals ),
			output: {
				globals: wpExternals,
				// Predictable hashed filenames per entry, grouped by type.
				entryFileNames: 'apps/[name]/[name].[hash].js',
				chunkFileNames: 'chunks/[name].[hash].js',
				assetFileNames: 'assets/[name].[hash].[ext]',
			},
		},
	},

	resolve: {
		alias: {
			// `import { apiFetch } from '@/shared/api'` — short, refactor-safe.
			'@':       path.resolve( __dirname, 'ui' ),
			'@apps':   path.resolve( __dirname, 'ui/apps' ),
			'@shared': path.resolve( __dirname, 'ui/shared' ),
		},
	},
} );
