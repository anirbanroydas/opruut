var path = require('path');



var config = {
	path: {
		src: path.join(process.cwd(), 'resources/assets/js', '**/*.{js,jsx}'),
		resourcePath: path.join(process.cwd(), 'resources'),
		assetsPath: path.join(process.cwd(), 'resources/assets'),
		cssSrcPath: path.join(process.cwd(), 'resources/assets/css'),
		jsSrcPath: path.join(process.cwd(), 'resources/assets/js'),
		scssSrcPath: path.join(process.cwd(), 'resources/assets/scss'),
		viewSrcPath: path.join(process.cwd(), 'resources/views'),
		buildDir: path.join(process.cwd(), 'public/build'),
		publicPath: path.join(process.cwd(), 'public'),
		cssOutputPath: path.join(process.cwd(), 'public/css'),
		jsOutputPath: path.join(process.cwd(), 'public/js'),
		viewOutputPath: path.join(process.cwd(), 'public/views'),
		assetsOutputPath: path.join(process.cwd(), 'public/assets'),
	},
	laravel: {
		path: {
			appDir: path.join(process.cwd(), 'app'),
			configDir: path.join(process.cwd(), 'config'),
			databaseDir: path.join(process.cwd(), 'database'),
			publicDir: path.join(process.cwd(), 'public'),
			resourcesDir: path.join(process.cwd(), 'resources'),
			routesDir: path.join(process.cwd(), 'routes'),
		}
	}
}



// console.log('resourcePath : ', config.path.resourcePath);
// console.log('assetsPath : ', config.path.assetsPath);
// console.log('cssSrcPath : ', config.path.cssSrcPath);
// console.log('jsSrcPath : ', config.path.jsSrcPath);
// console.log('scssSrcPath : ', config.path.scssSrcPath);
// console.log('viewSrcPath : ', config.path.viewSrcPath);
// console.log('buildDir : ', config.path.buildDir);
// console.log('publicPath : ', config.path.publicPath);
// console.log('cssOutputPath : ', config.path.cssOutputPath);
// console.log('jsOutputPath : ', config.path.jsOutputPath);
// console.log('viewOutputPath : ', config.path.viewOutputPath);


module.exports = config;