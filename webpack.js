const webpackConfig = require('@nextcloud/webpack-vue-config')
const { merge } = require('webpack-merge')

const config = {
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
			},
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader']
			},
			{
				test: /\.(jpe?g|png|gif|gif|svg|eot|woff|ttf|svg|woff2)$/,
				loader: "file-loader"
			}
		]

	}
}

const mergedConfigs = merge(config, webpackConfig)

mergedConfigs.module.rules = mergedConfigs.module.rules.filter((v, i, a) => a.findIndex(t=> (t.test.toString() === v.test.toString())) === i)
module.exports = mergedConfigs
