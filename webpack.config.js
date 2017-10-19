const path = require( 'path' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
module.exports = {
	entry: {
		'pavoblog' : path.join( __dirname, 'upload/catalog/view/theme/default/stylesheet/pavoblog/pavoblog.scss' )
	},
	output: {
		filename: "[name].min.css",
		path: path.join( __dirname, 'upload/catalog/view/theme/default/stylesheet/' )
	},
	module: {
		loaders: [
			{
				test: /\.css$/,
				loader: [ 'style-loader', 'css-loader' ]
			},
			{
				test: /\.scss$/,
				exclude: /node_modules/,
				loader: ExtractTextPlugin.extract([ 'css-loader?minimize', 'sass-loader' ])
			},
			{
				// image extensions, fonts extensions
				test: /\.(png|jpg|jpeg|ttf|woff|woff2|eot|svg|gif|)$/,
				exclude: /node_modules/,
				loader: [ 'url-loader?emitFile=false' ]//, 'file-loader?emitFile=false'
			}
		]
	},
	devtool: 'eval-source-map',
	plugins: [
	    new ExtractTextPlugin({
		    filename: "[name].min.css",
		    disable: process.env.NODE_ENV === 'development'
		})
	]
}