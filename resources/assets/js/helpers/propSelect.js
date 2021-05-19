/**
 * js/helpers/propSelect.js
 *
 * Helper for selecting data out of server responses
 */

/**
 * Helps select items from response result set
 * @param {Object} payload - response payload
 * @param {[String, String[]]} propertyName - name or names of property
 * @param {String} type ['array', 'object'] What type to return if response is empty
 * @returns {[Array, Object]}
 */
export const propSelect = function (payload, propertyName, type = 'array') {
  if (typeof propertyName === 'string') {
    return payload.hasOwnProperty(propertyName)
      ? payload[propertyName]
      : payload.hasOwnProperty('data')
        ? payload.data
        : type === 'array'
          ? [] : {}
  }
  for (var propName in propertyName) {
    if (payload.hasOwnProperty(propName)) {
      return payload[propName]
    }
  }
  return payload.hasOwnProperty('data')
    ? payload.data
    : type === 'array'
      ? [] : {}
}

export default propSelect
