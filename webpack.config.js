// Common imports
var gulp = require('gulp');
var environments = require('gulp-environments');
var path = require('path');
var autoprefixer = require('autoprefixer');

// Require webpack utilities
var webpack = require('webpack');
var ExtractTextPlugin = require('extract-text-webpack-plugin');

// Config variables
var config = require('./gulp/config');

// console.log('config : ', config);


// Babel loaders
var babelLoaders = ['babel-loader'];



if (environments.development()) {
    // Only hot load on dev
    babelLoaders.push('webpack-module-hot-accept');
    babelLoaders.unshift('react-hot');
}




var webpackSettings = {
    
    debug: environments.development(),
    
    entry: {
        app: [ path.join(config.path.jsSrcPath, 'app.js') ],
        vendor: [ "axios", "classnames", "d3", "immutability-helper",  
            "react", "react-d3-library", "react-dom", "react-loading", "react-modal", 
            "react-redux", "react-router", "react-spinkit", "react-tap-event-plugin",
            "redux", "redux-form", "redux-persist", "redux-thunk"
        ]
        // Each additional bundle you require (e.g. index page js, or contact page js)
        // should be added here and referenced as a script tag in the corresponding template
        // index: [ path.join(srcPath, 'index.jsx') ],
    },

    output: {
        path: config.path.assetsOutputPath,
        publicPath: '/assets/',
        filename: environments.production()? '[name].[chunkhash].bundle.js' : '[name].bundle.js'
    },
    
    resolve: {
        extensions: ['', '.js', '.jsx', '.json', '.scss', '.css'],
        // root: path.resolve(__dirname, './node_modules')
    },
    
    module: {
        // preLoaders: [{
        //     test: /\.jsx?/,
        //     loader: "eslint-loader",
        //     exclude: /(node_modules|bower_components)/
        // }],
        loaders: [

            {
                test: /\.jsx?/,
                exclude: /(node_modules|bower_components)/,
                include: [config.path.jsSrcPath],
                loaders: babelLoaders,
            },
            
            {
                test: /(\.scss|\.css)$/,
                exclude: /node_modules(?!\/react-toolbox)/,
                include: [
                    path.resolve(__dirname, "resources/assets/sass"),
                    path.resolve(__dirname, "resources/assets/css"),
                    path.resolve(__dirname, "node_modules/react-toolbox/lib")
                ],
                loader: ExtractTextPlugin.extract('style', 'css?sourceMap&modules&camelCase&importLoaders=1&localIdentName=[name]__[local]___[hash:base64:5]!postcss!sass')
            },

            {
                test: /(\.scss|\.css)$/,
                exclude: /(node_modules\/react-toolbox|resources\/assets)/,
                include: [
                    path.resolve(__dirname, "node_modules")
                ],
                loader: ExtractTextPlugin.extract('style', 'css?sourceMap&importLoaders=1!postcss!sass')
            },

            // {
            //     test: /(\.scss|\.css)$/,
            //     exclude: /(node_modules|bower_components)/,
            //     loader: ExtractTextPlugin.extract('style', 'css?sourceMap&modules&camelCase&importLoaders=1&localIdentName=[name]__[local]___[hash:base64:5]!postcss!sass')
            // },
            
            // {   
            //     test: /\.css$/, 
            //     exclude: /\.useable\.css$/, 
            //     loader: "style-loader!css-loader" 
            // },
            
            // {   test: /\.useable\.css$/, 
            //     loader: "style-loader/useable!css-loader" 
            // },
            
            // {
            //     test: /\.scss$/,
            //     loaders: ["style-loader", "css-loader", "sass-loader"]
            // }

        ]
    },

    postcss: [autoprefixer],
    
    sassLoader: {
        data: '@import "themes/_config.scss";',
        includePaths: [path.resolve(__dirname, './resources/assets/sass')]
    },


    plugins: environments.production() ? [

        new webpack.optimize.CommonsChunkPlugin({
            // name: 'commons',           
            names: ['commons', 'vendor', 'bootstrap'],
            filename: '[name].[chunkhash].bundle.common.js',
            // sminChunks: Infinity
        }),
        new ExtractTextPlugin('[name].[contenthash].bundle.css', { allChunks: true }),
        new webpack.optimize.DedupePlugin(),
        new webpack.optimize.UglifyJsPlugin(),
        new webpack.DefinePlugin({
            'process.env': {
                'NODE_ENV': JSON.stringify('production')
            }
        })

    ] : [

        new webpack.optimize.CommonsChunkPlugin({
            // name: 'commons',           
            names: ['commons', 'vendor', 'bootstrap'],
            filename: '[name].js',
            // minChunks: Infinity
        }),
        new webpack.optimize.OccurenceOrderPlugin(),
        new ExtractTextPlugin('[name].bundle.css', { allChunks: true }),
        
        new webpack.NoErrorsPlugin(),
        new webpack.HotModuleReplacementPlugin(),
        new webpack.DefinePlugin({
          'process.env.NODE_ENV': JSON.stringify('development')
        })
    
    ],
    
    // eslint: {
    //     parserOptions: {
    //         ecmaVersion: 6,
    //         sourceType: 'module',
    //         ecmaFeatures: {
    //             jsx: true,
    //         }
    //     },
    // rules: {
    //     semi: 2
    // },
    //     extends: ['eslint:recommended', 'plugin:react/recommended'],
    //     plugins: [
    //         'react'
    //     ]
    // }
};





/**
Dev build settings
*/
if (environments.development()) {

    // Set devtool
    webpackSettings.devtool = "eval";

    // !!! Edit this if you don't use multiple entry point Webpack files
    for ( var key in webpackSettings.entry ) {
        // For each entry point in your settings
        if (webpackSettings.entry.hasOwnProperty(key)) {
            // ... add the webpack dev server and hotloader
            if (key !== 'vendor') {
                // dont add wepbadk dev server and middleware for verndor entry point
                // since its not going to change
                webpackSettings.entry[key].unshift('webpack-hot-middleware/client');
                webpackSettings.entry[key].unshift('webpack/hot/dev-server');
            }
        }
    }
}





module.exports = webpackSettings;


