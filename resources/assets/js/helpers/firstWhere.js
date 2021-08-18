/**
 * resources/assets/js/helpers/firstWhere.js
 */
import { findIndex } from 'lodash'

/**
 * Finds and returns first item in array that matches check
 * @param {Array} array
 * @param {[Function, Array]} check function for check, or array with [ keyName, searchValue ]
 * @returns first matched object from array
 */
export const firstWhere = function(array, check) {
  var index = -1
  if (typeof check === 'function') {
    index = findIndex(array, check)
  } else if (typeof check === 'object') {
    index = findIndex(array, o => ( o[check[0]] === check[1] ))
  }
  if (index > -1) {
    return array[index]
  }
  return {}
}

export default firstWhere
