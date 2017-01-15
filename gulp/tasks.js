// Common packages
var gulp = require('gulp'),
	gutil = require('gulp-util'),
	// pkg = require('../package.json'),
	path = require('path');


// Browser-Sync and plugins
var browserSync = require('browser-sync');
var webPack = require('webpack'),
	webpackDevMiddleware = require('webpack-dev-middleware'),
	webpackHotMiddleware = require('webpack-hot-middleware');


// Load Webpack settings
var webpackSettings = require('../webpack.config.js'),
	bundler = webPack(webpackSettings);


// Config variables
var config = require('./config');

// console.log('config : ', config);


function tasks() {

	gulp.task('dev', function() {
		browserSync({
			proxy: {
				target: '0.0.0.0:9091',

				middleware: [
					webpackDevMiddleware(bundler, {
						publicPath: webpackSettings.output.publicPath,
						hot: true,
						stats: { colors: true }
					}),
					webpackHotMiddleware(bundler)
				]
			},

			files: [
				config.path.cssSrcPath + '/**/*.css',
				config.path.viewSrcPath + '/**/*.blade.php',
				config.path.cssOutputPath + '/**/*.css',
				config.path.builDir + '/**/*.json'

			],

	        snippetOptions: {
	            rule: {
	                match: /(<\/body>|<\/pre>)/i,
	                fn: (snippet, match) => snippet + match
	            }
	        }

		});

	});




	gulp.task('prod', function() {

		
	});




	// Aliases
	// gulp.task('watch', gulp.parallel(['serve']));
	gulp.task('connect', ['serve'], function() {
		gutil.log('gulp run successfully finished');
	});
	gulp.task('run', ['serve'], function() {
		gutil.log('gulp run successfully finished');
	});


}



module.exports = tasks;

