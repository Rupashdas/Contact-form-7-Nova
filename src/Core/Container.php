<?php
/**
 * Tiny dependency-injection container.
 *
 * @package CF7_Nova_Lite
 */

declare( strict_types=1 );

namespace CF7NL\Core;

defined( 'ABSPATH' ) || exit;

/**
 * A minimal service container — enough to register services, share singletons,
 * inject pre-built instances, and resolve them by string key.
 *
 * Design notes:
 *  - No reflection / auto-wiring. Every binding is an explicit factory or a
 *    pre-built object. Reflection is convenient but trades clarity for magic;
 *    we can always add it later if a real need shows up.
 *  - Factories receive the container itself as their only argument, so they
 *    can resolve their own dependencies (`fn ( $c ) => new Foo( $c->make( 'logger' ) )`).
 *  - Singletons are cached after the first `make()` call, not at bind time —
 *    that keeps activation cheap and avoids constructing services that the
 *    request never actually uses.
 *
 * Typical usage:
 *
 *     $container = new Container();
 *     $container->singleton( 'logger', fn () => new Logger() );
 *     $container->singleton(
 *         'submissions',
 *         fn ( Container $c ) => new Submissions_Repository( $c->make( 'logger' ) )
 *     );
 *
 *     $repo = $container->make( 'submissions' );
 */
final class Container {

	/**
	 * Factories registered via bind() / singleton().
	 *
	 * Shape: [ key => [ 'factory' => callable, 'shared' => bool ] ]
	 *
	 * @var array<string, array{factory: callable, shared: bool}>
	 */
	private array $bindings = array();

	/**
	 * Resolved instances. For shared bindings, the first make() call caches
	 * the produced object here; later calls return the same reference.
	 *
	 * @var array<string, mixed>
	 */
	private array $instances = array();

	/**
	 * Register a factory that produces a NEW instance every time `make()` is
	 * called. Use this for stateless helpers or per-call DTOs.
	 *
	 * @param string   $key     Lookup key, e.g. 'logger'.
	 * @param callable $factory Receives the container, returns the object.
	 */
	public function bind( string $key, callable $factory ): void {
		$this->bindings[ $key ] = array(
			'factory' => $factory,
			'shared'  => false,
		);
		// Drop any cached instance so the next make() honours the new binding.
		unset( $this->instances[ $key ] );
	}

	/**
	 * Register a factory whose first resolution is cached and reused for the
	 * lifetime of this container. The default for almost every service.
	 *
	 * @param string   $key     Lookup key.
	 * @param callable $factory Receives the container, returns the object.
	 */
	public function singleton( string $key, callable $factory ): void {
		$this->bindings[ $key ] = array(
			'factory' => $factory,
			'shared'  => true,
		);
		unset( $this->instances[ $key ] );
	}

	/**
	 * Inject an already-constructed object. Useful in tests for swapping a
	 * real service with a mock, or for registering global handles that have
	 * no factory (e.g. the $wpdb singleton).
	 *
	 * @param string $key      Lookup key.
	 * @param mixed  $instance The object to return on every make() call.
	 */
	public function instance( string $key, $instance ): void {
		$this->instances[ $key ] = $instance;
		// Mark it shared so a stale factory cannot replace it on a later call.
		$this->bindings[ $key ] = array(
			'factory' => static fn () => $instance,
			'shared'  => true,
		);
	}

	/**
	 * Resolve a binding.
	 *
	 * @param string $key Lookup key passed to bind/singleton/instance.
	 *
	 * @return mixed The resolved object.
	 *
	 * @throws \OutOfBoundsException When the key has not been registered.
	 */
	public function make( string $key ) {
		// Cached singleton hit — fast path.
		if ( array_key_exists( $key, $this->instances ) ) {
			return $this->instances[ $key ];
		}

		if ( ! isset( $this->bindings[ $key ] ) ) {
			throw new \OutOfBoundsException(
				sprintf( 'No service bound for key "%s".', $key )
			);
		}

		$binding = $this->bindings[ $key ];
		$object  = ( $binding['factory'] )( $this );

		if ( $binding['shared'] ) {
			$this->instances[ $key ] = $object;
		}

		return $object;
	}

	/**
	 * @param string $key Lookup key.
	 * @return bool True when a binding (or instance) is registered.
	 */
	public function has( string $key ): bool {
		return isset( $this->bindings[ $key ] ) || array_key_exists( $key, $this->instances );
	}

	/**
	 * Forget a binding and its cached instance. Mostly useful in tests
	 * between scenarios.
	 *
	 * @param string $key Lookup key.
	 */
	public function forget( string $key ): void {
		unset( $this->bindings[ $key ], $this->instances[ $key ] );
	}

	/**
	 * @return array<int, string> All registered keys, useful for diagnostics.
	 */
	public function keys(): array {
		return array_values( array_unique(
			array_merge( array_keys( $this->bindings ), array_keys( $this->instances ) )
		) );
	}
}
