<?php
/**
 * Uninstall handler for CF7 Nova Lite.
 *
 * WordPress executes this file once, in a clean PHP process, when the user
 * clicks "Delete" on the Plugins screen. It runs BEFORE the plugin folder is
 * removed, so this is the only opportunity to remove data the plugin owns.
 *
 * Phase 0: stub only — full cleanup (options, tables, post meta, transients)
 * lands in Phase 2 once the Schema layer exists.
 *
 * @package CF7_Nova_Lite
 */

declare( strict_types=1 );

/*
 * `WP_UNINSTALL_PLUGIN` is defined exclusively by WordPress core when the
 * uninstall flow is the legitimate caller. Bailing out otherwise prevents
 * data wipe via direct HTTP access to /wp-content/plugins/.../uninstall.php.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Phase 2 will perform here:
 *   - delete every option matching `cf7nl_*`
 *   - drop every table matching `{$wpdb->prefix}cf7nl_*`
 *   - remove post meta `_cf7nl_settings` from all CF7 form posts
 *   - remove user meta `cf7nl_prefs`
 *   - delete transients matching `cf7nl_*`
 *
 * All of the above must respect the multisite flag — when running on a
 * network, iterate over every blog with `get_sites()` and switch_to_blog().
 */
