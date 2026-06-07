/**
 * Build smoke test — temporary stub.
 *
 * Phase 0 ships before any real admin app exists, but `vite build` needs at
 * least one entry to actually produce output. This file lets the build
 * pipeline be verified end-to-end on day one.
 *
 * The folder name starts with `_` so Vite's discoverEntries() skips it —
 * it is run-by-hand only via `vite build --input ui/apps/_smoke/index.jsx`,
 * never bundled into the real plugin output.
 *
 * Delete this folder once `ui/apps/submissions/` (Phase 4) lands.
 */

import { createRoot } from 'react-dom/client';

const App = () => <p>CF7 Nova build pipeline is wired up.</p>;

const mount = document.getElementById( 'cf7nl-root' );
if ( mount ) {
	createRoot( mount ).render( <App /> );
}
