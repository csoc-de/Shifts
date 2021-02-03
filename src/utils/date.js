import logger from './logger'

export function dateFactory() {
	return new Date()
}

export function getYYYYMMDDFromDate(date) {
	return new Date(date.getTime() - (da.getTimezoneOffset() * 60000))
		.toISOString()
		.split('T')[0]
}

export function getUnixTimestampFromDate(date) {
	return Math.floor(date.getTime() / 1000)
}

export function getDateFromFirstdayParam(firstDayParam) {
	if (firstDayParam === 'now') {
		return dateFactory()
	}

	const [year, month, date] = firstDayParam.split('-')
		.map((str) => parseInt(str, 10))

	if (Number.isNaN(year) || Number.isNaN(month) || Number.isNaN(date)) {
		logger.error('Frist day paramter contains non numerical components, falling back to today')
		return dateFactory()
	}

	const dateObject = dateFactory()
	dateObject.setFullYear(year, month - 1, date)
	dateObject.setHours(0,0,0,0)

	return dateObject
}

export function getYYYYMMDDFromFirstdayParam(firstDayParam) {
	if (firstDayParam === 'now') {
		return getYYYYMMDDFromDate(dateFactory())
	}

	return firstDayParam
}

export function getDateFromDateTimeValue(dateTimeValue) {
	return new Date(
		dateTimeValue.year,
		dateTimeValue.month -1,
		dateTimeValue.day,
		dateTimeValue.hour,
		dateTimeValue.minute,
		0,
		0
	)
}

export function modifyDate(date, {day = 0, week = 0, month = 0}) {
	date = new Date(date.getTime())
	date.setDate(date.getDate() + day)
	date.setDate(date.getDate() + week * 7)
	date.setMonth(date.getMonth() + month)

	return date
}
