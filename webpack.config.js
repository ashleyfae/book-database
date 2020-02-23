/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const postcssPresetEnv = require( 'postcss-preset-env' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const IgnoreEmitPlugin = require( 'ignore-emit-webpack-plugin' );

const production = process.env.NODE_ENV === '';

module.exports = {
	...defaultConfig,

	devtool: 'source-map',

	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
		moment: 'moment'
	},

	resolve: {
		...defaultConfig.resolve,
		modules: [
			`${ __dirname }/assets/js/src`,
			'node_modules',
		]
	},

	entry: {
		// JS
		"admin": './assets/js/src/admin/index.js',
		"admin-global": './assets/js/src/admin/global.js',
		"book-graphs": './assets/js/src/admin/analytics.js',

		// CSS
		"admin-style": './assets/sass/admin.scss',
		"admin-style-global": './assets/sass/admin-global.scss',
		"front-end": './assets/sass/front-end.scss'
	},

	module: {
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.(sc|sa|c)ss$/,
				exclude: /node_modules/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
					},
					{
						loader: 'css-loader',
						options: {
							sourceMap: ! production,
						},
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: ! production,
							sassOptions: {
								outputStyle: 'compressed'
							}
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							ident: 'postcss',
							plugins: () => [
								postcssPresetEnv( {
									stage: 3,
									features: {
										'custom-media-queries': {
											preserve: false,
										},
										'custom-properties': {
											preserve: true,
										},
										'nesting-rules': true,
									},
								} ),
							],
						},
					},
				],
			},
		]
	},

	output: {
		filename: 'assets/js/build/[name].min.js',
		path: __dirname,
	},

	plugins: [
		new webpack.ProvidePlugin( {
			$: 'jquery',
		} ),

		new IgnoreEmitPlugin( [ 'admin-style.min.js', 'front-end.min.js', 'front-end.js' ] ),

		new MiniCssExtractPlugin( {
			filename: 'assets/css/[name].min.css',
		} ),
	]

};