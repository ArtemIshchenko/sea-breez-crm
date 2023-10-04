export default {

  // replaces '_' with whitespaces and capiteize first letter
  nameToLabel (str) {
    if (typeof str !== 'string') {
      return ''
    }
    return str.replace(/([A-Z])/g, ' $1')
      .toLowerCase()
      .replace('_', ' ')
      .replace(/^./, function (str) { return str.toUpperCase() })
  },

  insertLinks (text) {
    if (typeof str !== 'string') {
      return ''
    }
    return text.replace(/((http|https):\/\/[a-zA-Z0-9\-.]+\.[a-zA-Z]{2,3}(\/\S*)?)/g, '<a href="$1" target="_blank">$1</a>')
  },

  truncateWords (str, n, suffix = '...') {
    if (typeof str !== 'string') {
      return ''
    }
    let newStr = str.split(/\s+/).splice(0, n).join(' ')
    if (newStr !== str) {
      newStr += suffix
    }
    return newStr
  },

  escapeHtml (str) {
    if (typeof str !== 'string') {
      return ''
    }
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }
    return str.replace(/[&<>"']/g, function (m) {
      return map[m]
    })
  }
}
