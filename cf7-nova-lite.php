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

/** @var string Human-facing plugin version. Bump on every release (semver). */
define( 'CF7NL_VERSION', '2.0.0-dev' );

/** @var string Database schema version. Bump only when the schema changes. */
define( 'CF7NL_DB_VERSION', '1' );

/** @var string Absolute path to this main plugin file. */
define( 'CF7NL_FILE', __FILE__ );

/** @var string Filesystem path to the plugin root, trailing slash. */
define( 'CF7NL_PATH', plugin_dir_path( __FILE__ ) );

/** @var string Public URL to the plugin root, trailing slash. */
define( 'CF7NL_URL', plugin_dir_url( __FILE__ ) );

/** @var string Plugin basename, e.g. "cf7-nova-lite/cf7-nova-lite.php". */
define( 'CF7NL_BASENAME', plugin_basename( __FILE__ ) );

/** @var string Plugin slug used for menu pages, capabilities, and asset handles. */
define( 'CF7NL_SLUG', 'cf7-nova-lite' );

/** @var string Text domain for translations — must match the header above. */
define( 'CF7NL_TEXT_DOMAIN', 'cf7-nova-lite' );

/*
 * -----------------------------------------------------------------------------
 * Autoloader
 * -----------------------------------------------------------------------------
 * PSR-4 mapping for `CF7NL\` → `src/`. Implemented inline so the plugin works
 * without `composer install` — the WP.org distribution does not ship vendor/.
 *
 * Composer's autoloader is loaded too, but only if vendor/ exists, so dev
 * tooling (PHPUnit) still resolves classes the same way.
 *
 *   Example:  CF7NL\Core\Plugin             → src/Core/Plugin.php
 *             CF7NL\Modules\Submissions\Module → src/Modules/Submissions/Module.php
 */
spl_autoload_register(
	static function ( string $class ): void {
		// Bail fast on unrelated classes — autoloaders run on every class touch.
		if ( 0 !== strpos( $class, 'CF7NL\\' ) ) {
			return;
		}

		// Strip the namespace prefix, then convert `\` to the OS path separator.
		$relative = substr( $class, strlen( 'CF7NL\\' ) );
		$path     = CF7NL_PATH . 'src' . DIRECTORY_SEPARATOR
			. str_replace( '\\', DIRECTORY_SEPARATOR, $relative ) . '.php';

		// `is_readable` covers existence + permission in one syscall.
		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
);

// Optional Composer autoload — only if dev dependencies are installed.
if ( is_readable( CF7NL_PATH . 'vendor/autoload.php' ) ) {
	require_once CF7NL_PATH . 'vendor/autoload.php';
}

/*
 * -----------------------------------------------------------------------------
 * Contact Form 7 dependency check
 * -----------------------------------------------------------------------------
 * Contact Form 7 is a hard requirement. Without it every hook we register is
 * a no-op, so it is friendlier to refuse to boot and show an admin notice
 * than to silently misbehave.
 *
 * The check runs late (on `plugins_loaded` priority 10) so CF7 has had its own
 * chance to load. We can't check at file-include time — that runs before
 * other plugins have been required.
 */

/**
 * Detect whether Contact Form 7 is loaded.
 *
 * @return bool True when CF7 is active and reachable.
 */
function cf7nl_is_cf7_active(): bool {
	return defined( 'WPCF7_VERSION' ) || class_exists( 'WPCF7' );
}

/**
 * Render an admin notice asking the user to install/activate Contact Form 7.
 *
 * Hooked from cf7nl_boot() only when CF7 is missing, so it is never shown
 * to users who have a healthy install.
 */
function cf7nl_render_cf7_missing_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__(
			'CF7 Nova Lite requires Contact Form 7. Please install and activate it.',
			'cf7-nova-lite'
		)
	);
}

/*
 * -----------------------------------------------------------------------------
 * Boot
 * -----------------------------------------------------------------------------
 * Priority 5 lets us check CF7 immediately after WordPress core finishes
 * loading. CF7 itself initializes on `plugins_loaded` priority 10, so by the
 * time the bootstrapping action body runs, CF7's classes are already declared.
 *
 * Phase 1: this stub will hand control to \CF7NL\Core\Plugin::instance()->boot().
 */
function cf7nl_boot(): void {
	if ( ! cf7nl_is_cf7_active() ) {
		add_action( 'admin_notices', 'cf7nl_render_cf7_missing_notice' );
		return;
	}

	// Phase 1.5: \CF7NL\Core\Plugin::instance()->boot();
}
add_action( 'plugins_loaded', 'cf7nl_boot', 5 );
