/**
 * Quick logger for safer logging
 */

import { defaults, indexOf, capitalize } from 'lodash'

export const defaultOptions = {
  levels: ['debug', 'info', 'warn', 'error'],
  level: 'debug',
  traceLevel: false
}

export class Logger {
  /**
   * Options with defaults:
   * ```
   * {
   *    levels: ['debug', 'info', 'warn', 'error'] // Log levels from lowest to highest
   *    level: 'debug' // Min level to print
   *    traceLevel: false // Prints stacktrace at this level and above, set to false to disable
   * }
   * ```
   * @param {*} options
   */
  constructor(options = {}) {
    this.options = defaults(options, defaultOptions)
    this.updateAliasFunctions()
  }

  /**
   * Add alias functions for the levels stored
   */
  updateAliasFunctions() {
    this.options.levels.forEach(level => {
      this[level] = function (...args) {
        return this.log(level, ...args)
      }
    })
  }

  /**
   * Check if allowed to print, this will handle -1 for level not in levels as well
   * @param {*} level
   */
  levelAllowed(level) {
    return indexOf(this.options.levels, this.options.level) <= indexOf(this.options.levels, level)
  }

  /**
   * Check if trace is suppose to be printed
   * @param {*} level
   */
  traceAllowed(level) {
    return this.options.traceLevel &&
      indexOf(this.options.levels, this.options.traceLevel) <= indexOf(this.options.levels, level)
  }

  /**
   * Handle logging to console
   * @param {*} level
   * @param  {...any} args
   */
  log(level, ...args) {
    this.lastLog = { level, args }
    if (this.levelAllowed(level)) {
      const message = `Logger | ${capitalize(level)} |`
      if (typeof console[level] === 'function') {
        console[level](message, ...args)
      } else {
        console.log(message, ...args)
      }
      if (this.traceAllowed(level)) {
        console.trace(`Logger Trace | ${capitalize(level)}`, ...args)
      }
    }
    return this
  }

  /**
   * Forces stacktrace from last log made.
   * use: `Logger.debug('message').withTrace()`
   */
  withTrace() {
    if (this.lastLog) {
      const { level, args } = this.lastLog
      if (!this.traceAllowed(level)) { // If true, then trace will have already printed
        console.trace(`Logger Trace | ${capitalize(level)}`, ...args)
      }
    }
    return this
  }
}

const isProduction = process.env.NODE_ENV === 'production'
/**
 * Logger instance with defaults based on environment
 */
export default new Logger({
  level: isProduction ? 'error' : 'debug',
})
