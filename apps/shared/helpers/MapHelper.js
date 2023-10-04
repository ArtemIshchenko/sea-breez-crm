export default {
  // positionToString ({ lat, lng }) {
  positionToString (coords) {
    if (typeof coords !== 'object') {
      return null
    }
    const { lat, lng } = coords
    return (typeof lat === 'function' ? lat() : lat) +
      ',' +
      (typeof lng === 'function' ? lng() : lng)
  },
  stringToPosition (lit) {
    if (typeof lit !== 'string') {
      return null
    }
    const arr = lit.split(',')
    return { lat: Number(arr[0]), lng: Number(arr[1]) }
  }
}
