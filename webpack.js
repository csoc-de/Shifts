const webpackConfig = require('@nextcloud/webpack-vue-config')
const { merge } = require('webpack-merge')
const path = require('path')

const config = {
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
			},
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader'],
			},
			{
				test: /\.(jpe?g|png|gif|svg|eot|woff|ttf|woff2)$/,
				loader: 'file-loader',
			},
		],
	},
	entry: {
		settings: path.resolve(path.join('settings', 'settings.js')),
	},
}

const mergedConfigs = merge(config, webpackConfig)

mergedConfigs.module.rules = mergedConfigs.module.rules.filter((v, i, a) => a.findIndex(t => (t.test.toString() === v.test.toString())) === i)
module.exports = mergedConfigs

module.exports.watchOptions = {
  aggregateTimeout: 200,
  poll: 750,
}
