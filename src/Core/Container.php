<?php
/**
 * Minimal dependency-injection container.
 *
 * @package CF7_Nova_Lite
 */

declare( strict_types=1 );

namespace CF7NL\Core;

defined( 'ABSPATH' ) || exit;

/**
 * A tiny service container for registering and resolving services.
 *
 * Why: When Phase 2 adds Logger, Schema, REST_Manager, each one depends on
 * others. Writing `new Logger(); new Schema(logger); new REST_Manager(logger, repo);`
 * in 10 places is a maintenance nightmare. Container centralizes the wiring.
 *
 * Design: Factories are stored, not executed until make() is called. Singletons
 * cache after first make() so Logger is only instantiated once per request.
 * Factories receive the container, so they can resolve their own deps:
 *   fn ( $c ) => new Schema( $c->make('logger') )
 *
 * Currently only has bind(), singleton(), make(). Other methods (has, instance,
 * forget, keys) will be added in later phases when they become necessary.
 */
final class Container {

	/**
	 * Stored recipes (factories) for each registered service.
	 * Shape: [ 'service_key' => [ 'factory' => callable, 'shared' => bool ] ]
	 *
	 * @var array<string, array{factory: callable, shared: bool}>
	 */
	private array $bindings = array();

	/**
	 * Cache for singleton instances. Once make() creates an object for a
	 * shared service, store it here so later make() calls return the same object.
	 *
	 * @var array<string, mixed>
	 */
	private array $instances = array();

	/**
	 * Register a service factory that creates a NEW instance every time make() called.
	 *
	 * Use case: stateless helpers, temporary objects. In practice, almost never used
	 * in a plugin — singleton() is the default.
	 *
	 * @param string   $key     Service lookup key.
	 * @param callable $factory Function that receives container, returns object.
	 */
	public function bind( string $key, callable $factory ): void {
		$this->bindings[ $key ] = array(
			'factory' => $factory,
			'shared'  => false,
		);
		unset( $this->instances[ $key ] );
	}

	/**
	 * Register a service factory whose first make() caches the object for
	 * reuse. Default for almost every service.
	 *
	 * Use case: Logger, Schema, Database, REST manager — things that should
	 * be instantiated exactly once per request.
	 *
	 * @param string   $key     Service lookup key.
	 * @param callable $factory Function that receives container, returns object.
	 */
	public function singleton( string $key, callable $factory ): void {
		$this->bindings[ $key ] = array(
			'factory' => $factory,
			'shared'  => true,
		);
		unset( $this->instances[ $key ] );
	}

	/**
	 * Resolve a service — return cached object if it's a singleton, or run
	 * the factory to create/retrieve it.
	 *
	 * @param string $key Service lookup key.
	 * @return mixed The resolved service object.
	 * @throws \OutOfBoundsException If the key was never registered.
	 */
	public function make( string $key ) {
		// Fast path: if this singleton was already instantiated, return it.
		if ( array_key_exists( $key, $this->instances ) ) {
			return $this->instances[ $key ];
		}

		// Check the factory exists.
		if ( ! isset( $this->bindings[ $key ] ) ) {
			throw new \OutOfBoundsException(
				sprintf( 'Container has no binding for key "%s".', $key )
			);
		}

		// Run the factory.
		$binding = $this->bindings[ $key ];
		$object  = ( $binding['factory'] )( $this );

		// Cache if this is a singleton.
		if ( $binding['shared'] ) {
			$this->instances[ $key ] = $object;
		}

		return $object;
	}
}
