module.exports = function(grunt) {

// Load multiple grunt tasks using globbing patterns
require('load-grunt-tasks')(grunt);

// Project configuration.
grunt.initConfig({
	pkg: grunt.file.readJSON('package.json'),

		checktextdomain: {
			options:{
				text_domain: 'mobile-dj-manager',
				create_report_file: true,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
					]
				},
				files: {
					src: [
						'**/*.php', // Include all files
						'!node_modules/**', // Exclude node_modules/
						'!build/.*', // Exclude build/
						'!docs/.*', // Exclude docs
						'!tests/.*' // Exclude tests
						],
					expand: true
				}
			},

			makepot: {
				target: {
					options: {
						domainPath: '/lang/',    // Where to save the POT file.
						exclude: ['build/.*'],
						mainFile: 'mobile-dj-manager.php',    // Main project file.
						potFilename: 'mobile-dj-manager.pot',    // Name of the POT file.
						potHeaders: {
							poedit: true,                 // Includes common Poedit headers.
							'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
									},
						type: 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
						updateTimestamp: true,    // Whether the POT-Creation-Date should be updated without other changes.
						processPot: function( pot, options ) {
							pot.headers['report-msgid-bugs-to'] = 'https://easydigitaldownloads.com/';
							pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)';
							pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>';
							pot.headers['language'] = 'en_US';
							var translation, // Exclude meta data from pot.
								excluded_meta = [
									'KB Support',
									'https://mdjm.co.uk',
									'Mike Howard',
									'http://mikesplugins.co.uk'
									];
										for ( translation in pot.translations[''] ) {
											if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
												if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
													console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
														delete pot.translations[''][ translation ];
													}
												}
											}
							return pot;
						}
					}
				}
			},

			dirs: {
				lang: 'lang',
			},

			potomo: {
				dist: {
					options: {
						poDel: true
					},
					files: [{
						expand: true,
						cwd: '<%= dirs.lang %>',
						src: ['*.po'],
						dest: '<%= dirs.lang %>',
						ext: '.mo',
						nonull: true
				}]
			}
		},

		// Clean up build directory
		clean: {
			main: ['build/<%= pkg.name %>']
		},

		// Copy the theme into the build directory
		copy: {
			main: {
				src:  [
					'**',
					'!_notes/**',
					'!node_modules/**',
					'!build/**',
					'!.git/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!.travis.yml',
					'!.tx/**',
					'!tests/**',
					'!docs/**',
					'!phpunit.xml',
					'!**/Gruntfile.js',
					'!**/package.json',
					'!**/README.md',
					'!**/*~'
				],
				dest: 'build/<%= pkg.name %>/'
			}
		},

		//Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip'
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
			}
		},

		glotpress_download: {
			core: {
				options: {
					domainPath: 'lang',
					url: 'https://translate.wordpress.org',
					slug: 'wp-plugins/mobile-dj-manager/stable',
					textdomain: 'mobile-dj-manager',
					filter: {
						minimum_percentage: 1,
					}
				}
			},
		},

		jshint: {
            files: [
                'assets/js/admin-scripts.js',
                'assets/js/mdjm-ajax.js'
            ],
            options: {
                expr: true,
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },

		uglify: {
			options: {
				manage: false
			},
			my_target: {
				files: {
				'assets/js/admin-scripts.min.js': ['assets/js/admin-scripts.js'],
				'assets/js/mdjm-ajax.min.js': ['assets/js/mdjm-ajax.js']
				}
			}
		},

		cssmin:	{
			build:	{
				files: {
					'assets/css/mdjm-admin.min.css': ['assets/css/mdjm-admin.css'],
					'templates/mdjm.min.css': ['templates/mdjm.css']
				}
			}
		},

		phpdocumentor: {
            dist: {
                options: {
                    ignore: 'node_modules'
                }
            }
        },

	});

	grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-phpdocumentor');
    grunt.loadNpmTasks('grunt-wp-i18n');

	// Default tasks
	grunt.registerTask( 'default', [
		'jshint',
		'uglify',
		'cssmin',
		'makepot'
	]);

	// Doc tasks
	grunt.registerTask('docs' [
		'phpdocumentor:dist'
	]);

	// Build task(s).
	grunt.registerTask( 'build', [ 'clean', 'copy', 'compress' ] );

};