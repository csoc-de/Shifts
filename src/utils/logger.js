import { getLoggerBuilder } from '@nextcloud/logger'

const logger = getLoggerBuilder()
	.setApp('shifts')
	.detectUser()
	.build()

const logDebug = (message, context ={}) => {
	logger.debug(message, context)
}

const logError = (message, context ={}) => {
	logger.error(message, context)
}

const logFatal = (message, context ={}) => {
	logger.fatal(message, context)
}

const logInfo = (message, context ={}) => {
	logger.info(message, context)
}

const logWarn = (message, context ={}) => {
	logger.warn(message, context)
}

export default logger
export {
	logDebug,
	logError,
	logFatal,
	logInfo,
	logWarn,
}
