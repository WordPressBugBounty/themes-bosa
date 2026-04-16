module.exports = function( grunt ) {
	'use strict';

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		// Setting folder templates.
		dirs: {
			css: 'assets/css',
			sass: 'assets/sass',
			images: 'assets/images'
		},

		// Other options.
		options: {
			text_domain: 'bosa'
		},

		// Check textdomain errors.
		checktextdomain: {
			options: {
				text_domain: '<%= options.text_domain %>',
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
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [
					'**/*.php',
					'!node_modules/**',
					'!deploy/**',
					'!languages/**'
				],
				expand: true
			}
		},

		// Copy files to deploy.
		copy: {
			deploy: {
				src: [
					'**',
					'!.*',
					'!*.md',
					'!.*/**',
					'!Gruntfile.js',
					'!package.json',
					'!package-lock.json',
					'!node_modules/**',
					'!assets/html-pages/**',
					'!assets/sass/**',
					'!pages/kf-icons.php',
					'!assets/images/demo-images/**'
				],
				dest: 'deploy/<%= pkg.name %>',
				expand: true,
				dot: true
			}
		},

		sass: {
			options: {
				style: 'expanded',
				lineNumbers: false
			},
			dist:{
				files: {
					'./style.css': 'assets/sass/style.scss',
					'./rtl.css': 'assets/sass/rtl.scss',
				}
			}
		}, 
		watch: {
			css: {
				files: ['assets/sass/**/*.scss'],
				tasks: ['sass'],
				options: {
					spawn: false
				}
			},
			js: {
		        files: ['assets/js/*.js'],
		        tasks: ['uglify']
		    }
		},
		// Uglify JS.
		uglify: {
			target: {
				options: {
					mangle: true
				},
				files: [{
					expand: true,
					cwd: 'assets/js',
					src: ['custom.js', '!.min.js'],
					dest: 'assets/js',
					ext: '.min.js'
				}]
			}
		},

		// Generates language pot file
		makepot: {
	        target: {
	            options: {
	                cwd: '',
	                domainPath: '/languages',
	                mainFile: 'style.css',
	                potFilename: 'bosa.pot',
	                exclude: [ 'node_modules/.*', 'assets/.*' ],
	                type: 'wp-theme'
	            }
	        }
	    }
	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	
	// Register tasks
	grunt.registerTask( 'default', [ 'sass', 'uglify' ] );
	grunt.registerTask( 'pot', [ 'makepot' ] );
	grunt.registerTask( 'css', [ 'sass' ] );
	grunt.registerTask( 'js', [ 'uglify' ] );
	grunt.registerTask( 'deploy', [ 'sass', 'uglify', 'makepot', 'copy:deploy' ] );

	grunt.registerTask( 'testdomain', [ 'checktextdomain' ] );
}