<?php
/**
 * Module contract — what every feature module must implement.
 *
 * @package CF7_Nova_Lite
 */

declare( strict_types=1 );

namespace CF7NL\Core\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Interface that every module (feature) must implement.
 *
 * Phase 1.4 Module_Manager will iterate modules and call is_enabled() and boot()
 * on each one. This interface declares the contract so the manager doesn't need
 * to know about specific module implementations.
 *
 * Example module:
 *
 *     namespace CF7NL\Modules\Submissions;
 *
 *     class Module implements \CF7NL\Core\Contracts\Module {
 *         public function slug(): string { return 'submissions'; }
 *         public function is_enabled(): bool { return get_option('cf7nl_mod_submissions', true); }
 *         public function boot(): void { add_action( 'cf7nl_...' ... ); }
 *     }
 */
interface Module {

	/**
	 * Unique identifier for this module. Used as the key in the modules registry.
	 *
	 * @return string Slug, e.g. 'submissions', 'multistep', 'analytics'.
	 */
	public function slug(): string;

	/**
	 * Whether this module is enabled. If false, Module_Manager skips boot().
	 *
	 * Lit modules check an option (toggle in admin), Pro modules check license.
	 *
	 * @return bool True if the module should be booted.
	 */
	public function is_enabled(): bool;

	/**
	 * Bootstrap the module: register hooks, REST routes, enqueue assets, etc.
	 *
	 * Called only once per request if is_enabled() returned true.
	 * Called on `cf7nl_modules_booted` hook (Phase 1.5).
	 */
	public function boot(): void;
}
