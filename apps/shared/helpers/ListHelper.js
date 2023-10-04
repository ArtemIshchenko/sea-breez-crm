const helper = {
  equal: (array1, array2) => {
    if (!array1 || !array2) {
      return false
    }
    if (!Array.isArray(array1) || !Array.isArray(array2)) {
      return false
    }
    if (array1.length !== array2.length) {
      return false
    }
    for (let i = 0; i < array1.length; i++) {
      if (array1[i] !== array2[i]) {
        return false
      }
    }
    return true
  },
  getKeyByValue: (val, obj) => {
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop) && obj[prop] === val) {
        return prop
      }
    }
  }
}

export default helper
