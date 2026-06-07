/**
 * ESLint config for CF7 Nova Lite admin UI.
 *
 * Extends the official @wordpress/eslint-plugin preset, which is the same
 * baseline Gutenberg and the @wordpress/scripts toolchain enforce — that way
 * our code style stays close to anything a WP developer is already used to.
 *
 * The file is `.cjs` (not `.js`) because package.json sets "type": "module";
 * ESLint v8 still loads its config as CommonJS.
 */

module.exports = {
	root: true,

	env: {
		browser: true,
		es2022: true,
		node: true,
	},

	parserOptions: {
		ecmaVersion: 2022,
		sourceType: 'module',
		ecmaFeatures: { jsx: true },
	},

	// `recommended` covers JSX, hooks, i18n, and a11y. `recommended-with-formatting`
	// would fight Prettier; we keep formatting decisions in Prettier's court.
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],

	settings: {
		react: { version: '18' },
	},

	rules: {
		// We externalize React via wp.element, so the `react/react-in-jsx-scope`
		// classic rule does not apply — modern JSX transform handles it.
		'react/react-in-jsx-scope': 'off',

		// Project text domain — flags any __(), _x(), _n() call that forgets it.
		'@wordpress/i18n-text-domain': [
			'error',
			{ allowedTextDomain: [ 'cf7-nova-lite' ] },
		],

		// Discourage console noise in committed code. `warn` only — debugger
		// usage during dev still works, CI will flag stragglers.
		'no-console': [ 'warn', { allow: [ 'warn', 'error' ] } ],
	},

	overrides: [
		{
			// Build/config files run in Node, not the browser. Loosen accordingly.
			files: [ 'vite.config.js', '*.cjs' ],
			env: { node: true, browser: false },
			rules: { 'import/no-extraneous-dependencies': 'off' },
		},
	],

	ignorePatterns: [
		'build/',
		'node_modules/',
		'vendor/',
		'languages/',
	],
};
