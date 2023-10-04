module.exports = function (grunt) {
    grunt.initConfig({
        sass: {
            dev: {
              options: {
                loadPath: [
                  '.'
                ]
              },
              files: {
                "web/css/styles.css": "assets/scss/styles.scss"
              }
            },
            prod: {
              options: {
                loadPath: [
                  '.'
                ],
                style: 'compressed'
              },
              files: {
                "web/css/styles.min.css": "assets/scss/styles.scss"
              }
            }
        },
        concat: {
          options: {
            sourceMap: true
          },
          lib: {
            src: [
              'node_modules/jquery/dist/jquery.js',
              'node_modules/bootstrap/dist/js/bootstrap.js',
              'vendor/yiisoft/yii2/assets/yii.js',
              'vendor/yiisoft/yii2/assets/yii.validation.js',
              'vendor/yiisoft/yii2/assets/yii.activeForm.js'
            ],
            dest: 'web/js/lib.js'
          },
          scripts: {
            src: [
              'assets/js/scripts.js'
            ],
            dest: 'web/js/scripts.js'
          }
        },
        uglify: {
            options: {
                mangle: false
            },
            lib: {
                files: {
                    'web/js/lib.min.js': 'web/js/lib.js'
                }
            },
            scripts: {
                files: {
                    'web/js/scripts.min.js': 'web/js/scripts.js'
                }
            }
        },
        watch: {
            js: {
                files: ['assets/js/**/*.js'],
                tasks: ['concat', 'uglify'],
                options: {
                    livereload: true
                }
            },
            sass: {
                files: ['assets/scss/**/*.scss'],
                tasks: ['sass'],
                options: {
                    livereload: true
                }
            }
        },
        copy: {
          fonts: {
            files: [
              // Copy font awesome files
              {
                expand: true,
                flatten: true,
                src: ['node_modules/@fortawesome/fontawesome-free/webfonts/*'],
                dest: 'web/fonts/',
                filter: 'isFile'
              }
            ]
          }
        }
    });

    // Plugin loading
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Task definition
    grunt.registerTask('build', ['sass', 'concat', 'uglify']);
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('static', ['copy']);
};
