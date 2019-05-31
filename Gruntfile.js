/**
 * Grunt tasks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
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
				scripts: {
					files: [
						{ // JS.
							expand: true,
							cwd: 'js/',
							src: '**',
							dest: 'dist/js/'
						}
					]
				},
				assets: {
					files: [
						{ // Flot - http://www.flotcharts.org/.
							expand: true,
							cwd: 'node_modules/Flot/',
							src: [
								'jquery.flot.js',
								'jquery.flot.time.js',
								'jquery.flot.resize.js'
							],
							dest: 'assets/flot'
						},
						{ // accounting.js - http://openexchangerates.github.io/accounting.js/.
							expand: true,
							cwd: 'node_modules/accounting/',
							src: 'accounting.js',
							dest: 'assets/accounting'
						},
						{ // Tippy.js - https://atomiks.github.io/tippyjs/.
							expand: true,
							cwd: 'node_modules/tippy.js/dist/',
							src: 'tippy.all.js',
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
						cwd: 'sass',
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
						dest: 'dist/css/'
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
						cwd: 'dist/css/',
						src: [
							'*.css',
							'!*.min.css'
						],
						dest: 'dist/css/',
						ext: '.min.css'
					} ]
				}
			},

			// Uglify.
			uglify: {
				scripts: {
					files: {
						// Pronamic Pay.
						'dist/js/admin.min.js': 'dist/js/admin.js',
						'dist/js/admin-reports.min.js': 'dist/js/admin-reports.js',
						'dist/js/admin-tour.min.js': 'dist/js/admin-tour.js',
						// Accounting.
						'assets/accounting/accounting.min.js': 'assets/accounting/accounting.js',
						// Flot.
						'assets/flot/jquery.flot.min.js': 'assets/flot/jquery.flot.js',
						'assets/flot/jquery.flot.resize.min.js': 'assets/flot/jquery.flot.resize.js',
						'assets/flot/jquery.flot.time.min.js': 'assets/flot/jquery.flot.time.js',
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
						'images',
						'js'
					]
				}
			},

			// Webfont.
			webfont: {
				icons: {
					src: 'fonts/images/*.svg',
					dest: 'dist/fonts',
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
							template: 'src/fonts/templates/variables.scss',
							dest: 'src/fonts/_variables.scss'
						} ]
					}
				}
			},

			// Sass Lint.
			sasslint: {
				options: {
					configFile: '.sass-lint.yml'
				},
				target: [
					'scss/**/*.scss'
				]
			},

			// JSHint.
			jshint: {
				all: [ 'Gruntfile.js', 'composer.json', 'package.json' ]
			},

			// PHP Code Sniffer.
			phpcs: {
				application: {
					src: [
						'**/*.php',
						'!node_modules/**',
						'!vendor/**',
						'!wordpress/**',
						'!wp-content/**'
					],
				},
				options: {
					bin: 'vendor/bin/phpcs',
					standard: 'phpcs.xml.dist',
					showSniffCodes: true
				}
			},

			// PHPLint.
			phplint: {
				all: [ 'src/**/*.php' ]
			},

			// PHP Mess Detector.
			phpmd: {
				application: {
					dir: 'src'
				},
				options: {
					bin: 'vendor/bin/phpmd',
					exclude: 'node_modules',
					reportFormat: 'text',
					rulesets: 'phpmd.ruleset.xml'
				}
			},

			// PHPUnit.
			phpunit: {
				options: {
					bin: 'vendor/bin/phpunit'
				},
				classes: {

				}
			}
		}
	);

	// Default task(s).
	grunt.registerTask( 'default', [ 'jshint', 'phplint', 'phpmd', 'phpcs', 'phpunit' ] );
	grunt.registerTask( 'assets', [ 'sasslint', 'sass', 'postcss', 'copy:scripts', 'copy:assets' ] );
	grunt.registerTask( 'min', [ 'uglify' ] );

	grunt.registerTask( 'build_assets', [
		'clean:assets',
		'assets',
		'min'
	] );
};
