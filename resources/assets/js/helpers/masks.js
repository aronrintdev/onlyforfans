/**
 * Mask functions
 */

export const monthMask = value => {
  return [
    /[0-1]/,
    value.charAt(0) === '1' ? /[0-2]/ : /[0-9]/,
  ]
}

export const shortYearMask = value => {
  return [
    /[0-9]/,
    /[0-9]/,
  ]
}

export default {
  monthMask,
  shortYearMask,
}
