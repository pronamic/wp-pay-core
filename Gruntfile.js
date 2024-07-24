/**
 * Grunt tasks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

module.exports = function( grunt ) {
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig(
		{
			// Package.
			pkg: grunt.file.readJSON( 'package.json' ),

			// Copy.
			copy: {
				assets: {
					files: [
						{ // Tippy.js - https://atomiks.github.io/tippyjs/.
							expand: true,
							cwd: 'node_modules/tippy.js/dist/',
							src: [
								'tippy.all.js',
								'tippy.all.js.map'
							],
							dest: 'assets/tippy.js/'
						}
					]
				}
			},

			// SASS.
			sass: {
				options: {
					style: 'expanded'
				},
				build: {
					files: [ {
						expand: true,
						cwd: 'scss',
						src: '*.scss',
						dest: 'css',
						ext: '.css'
					} ]
				}
			},

			// PostCSS.
			postcss: {
				options: {
					map: false
				},
				prefix: {
					options: {
						processors: [
							require( 'autoprefixer' )(),
							require( 'postcss-eol' )()
						]
					},
					files: [ {
						expand: true,
						cwd: 'css/',
						src: '*.css',
						dest: 'css/'
					} ]
				},
				min: {
					options: {
						processors: [
							require( 'cssnano' )(),
							require( 'postcss-eol' )()
						]
					},
					files: [ {
						expand: true,
						cwd: 'css/',
						src: [
							'*.css',
							'!*.min.css'
						],
						dest: 'css/',
						ext: '.min.css'
					} ]
				}
			},

			// Uglify.
			uglify: {
				scripts: {
					files: {
						// Tippy.js.
						'assets/tippy.js/tippy.all.min.js': 'assets/tippy.js/tippy.all.js'
					}
				}
			},

			// Clean.
			clean: {
				assets: {
					src: [
						'assets',
						'css',
						'images'
					]
				}
			},

			// Webfont.
			webfont: {
				icons: {
					src: 'fonts/src/images/*.svg',
					dest: 'fonts/dist',
					options: {
						font: 'pronamic-pay-icons',
						fontFamilyName: 'Pronamic Pay Icons',
						normalize: true,
						stylesheets: [ 'css' ],
						templateOptions: {
							baseClass: 'pronamic-pay-icon',
							classPrefix: 'pronamic-pay-icon-'
						},
						types: [ 'eot', 'woff2', 'woff', 'ttf', 'svg' ],
						fontHeight: 768,
						customOutputs: [ {
							template: 'fonts/src/templates/variables.scss',
							dest: 'fonts/src/_variables.scss'
						} ]
					}
				}
			}
		}
	);

	// Default task(s).
	grunt.registerTask( 'assets', [ 'sass', 'postcss', 'copy:assets' ] );
	grunt.registerTask( 'min', [ 'uglify' ] );

	grunt.registerTask( 'build_assets', [
		'clean:assets',
		'assets',
		'min'
	] );
};
