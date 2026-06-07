<?php
/**
 * Plugin Name:       CF7 Nova Lite
 * Plugin URI:        https://example.com/cf7-nova
 * Description:       The missing modern layer for Contact Form 7 — visual builder, multi-step, submissions DB, conditional logic, and more. Free.
 * Version:           2.0.0-dev
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Rupash Das
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cf7-nova-lite
 * Domain Path:       /languages
 *
 * @package CF7_Nova_Lite
 */

declare( strict_types=1 );

/*
 * Abort if this file is accessed directly, outside the WordPress bootstrap.
 * `ABSPATH` is defined by WordPress in wp-load.php, so its presence proves we
 * are running inside a WP request and not from a stray HTTP hit.
 */
defined( 'ABSPATH' ) || exit;

/*
 * -----------------------------------------------------------------------------
 * Plugin Constants
 * -----------------------------------------------------------------------------
 * Every other file in the plugin reads from these. Bumping the version, moving
 * the plugin folder, or renaming the slug only has to happen here. Constants
 * are deliberately prefixed with `CF7NL_` to avoid collisions with WordPress
 * core, Contact Form 7, or any third-party plugin.
 */

/**
 * Human-facing plugin version. Bump on every release (semver).
 *
 * @var string
 */
define( 'CF7NL_VERSION', '2.0.0-dev' );

/**
 * Database schema version. Incremented only when the schema actually changes,
 * so the Schema installer can run targeted migrations (Phase 2).
 *
 * @var string
 */
define( 'CF7NL_DB_VERSION', '1' );

/**
 * Absolute path to this main plugin file. Used by `plugin_basename()`,
 * `register_activation_hook()`, and translation loaders.
 *
 * @var string
 */
define( 'CF7NL_FILE', __FILE__ );

/**
 * Filesystem path to the plugin root, with trailing slash. Use for `require`
 * and template loads. Never expose this to the front-end.
 *
 * @var string
 */
define( 'CF7NL_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Public URL to the plugin root, with trailing slash. Use for asset URLs in
 * enqueue calls.
 *
 * @var string
 */
define( 'CF7NL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename, e.g. "cf7-nova-lite/cf7-nova-lite.php". Required for
 * activation hooks and the `plugin_action_links_{$basename}` filter.
 *
 * @var string
 */
define( 'CF7NL_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin slug used for menu pages, capabilities, and asset handles.
 *
 * @var string
 */
define( 'CF7NL_SLUG', 'cf7-nova-lite' );

/**
 * Text domain for translations. Must match the `Text Domain` header above and
 * the directory name passed to `load_plugin_textdomain()`.
 *
 * @var string
 */
define( 'CF7NL_TEXT_DOMAIN', 'cf7-nova-lite' );

/*
 * -----------------------------------------------------------------------------
 * Boot
 * -----------------------------------------------------------------------------
 * The real bootstrap (autoloader, dependency check, service container, module
 * manager) lands in Phase 1 inside \CF7NL\Core\Plugin. This stub exists so the
 * plugin is activatable today and the activation/deactivation lifecycle can be
 * verified before any real code ships.
 *
 * Priority 5 runs before most third-party plugins on `plugins_loaded` but
 * after WordPress core has finished loading. Contact Form 7 declares its
 * classes at priority 10, so a CF7-presence check belongs at 10+, not here.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		// Phase 1: \CF7NL\Core\Plugin::instance()->boot();
	},
	5
);
