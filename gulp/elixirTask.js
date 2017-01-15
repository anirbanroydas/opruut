// rename webpack.config.js temporarily
var fs = require('fs');
var path = require('path');

var originalWebpackConfig = path.join(process.cwd(), 'webpack.config.js');
var temporaryWebpackConfig = path.join(process.cwd(), 'webpack.config.js.bak');

var renamed;

if (fs.existsSync(originalWebpackConfig)) {

	renamed = fs.rename(originalWebpackConfig, temporaryWebpackConfig)
}



var elixir = require('laravel-elixir');


require('laravel-elixir-webpack-react');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

function elixirTask() {

	elixir((mix) => {
	    mix.styles(['style.css'])
	       .webpack('app.js', 'public/assets/bundle.js', 'resources/assets/js', {

	       		module: {
			        loaders: [{   
			            test: /\.css$/, 
			            exclude: /\.useable\.css$/, 
			            loader: "style-loader!css-loader" 
			        },
			        {   
			        	test: /\.useable\.css$/, 
			            loader: "style-loader/useable!css-loader" 
			        }]
			    }	

	       	})
	       .version(['css/all.css', 'js/bundle.js'])
	});

	// rename temporary path to original one after the build task is finished
	renamed = fs.rename(temporaryWebpackConfig, originalWebpackConfig)
	

}



module.exports = elixirTask;

