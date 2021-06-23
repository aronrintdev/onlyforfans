/**
 * resources/assets/js/helpers/contains.js
 *
 * Shortcut helper for lodash find index to see if array or object contains item
 */
import { isArray, isObject, find, indexOf } from 'lodash'

export const contains = function (collection, item) {
  if (isArray(collection)) {
    return indexOf(collection, item) > -1
  }
  if (isObject(collection)) {
    return find(collection, item) !== undefined
  }
  return collection === item
}

export default contains
