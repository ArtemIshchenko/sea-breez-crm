const helper = {
  // to d.m.Y str
  toHumanStr: ($date, addTime = false) => {
    if (!$date) {
      return ''
    }
    $date = helper.normalizeDate($date)
    let $humanDate = ('0' + $date.getDate()).slice(-2) + '.' + ('0' + ($date.getMonth() + 1)).slice(-2) + '.' + $date.getFullYear()
    if (addTime) {
      $humanDate += ' ' + ('0' + $date.getHours()).slice(-2) + ':' + ('0' + $date.getMinutes()).slice(-2)
    }
    return $humanDate
  },

  // to m/d/Y str
  toSlashedStr: ($date) => {
    $date = helper.normalizeDate($date)
    return ('0' + ($date.getMonth() + 1)).slice(-2) + '/' + ('0' + $date.getDate()).slice(-2) + '/' + $date.getFullYear()
  },

  // to timestamp
  toTimestamp: ($date) => {
    $date = helper.normalizeDate($date)
    // console.log($date)
    // console.log(new Date().getTime())
    return $date.getTime() / 1000
  },

  // ensures $date is a Date object
  normalizeDate: ($date) => {
    if (!$date) return null
    if (!($date instanceof Date)) {
      $date = new Date($date * 1000)
      if (!($date instanceof Date)) {
        throw new Error('Could not create a Date instance.')
      }
    }
    return $date
  },

  isoToHuman: (isoStr) => {
    if (!isoStr) {
      return ''
    }
    const dateParts = isoStr.split('-')
    const date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substr(0, 2))
    return helper.toHumanStr(date)
  },

  timestampToHuman (timestamp) {
    const date = new Date(timestamp * 1000)
    return helper.toHumanStr(date)
  }
}

export default helper
