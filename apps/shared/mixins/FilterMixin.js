// Filtering methods for DataGrid component

const data = () => {
  return {
    filter: {
      model: {},
      append: {},
      conditions: {},
      isOpen: false
    }
  }
}

const methods = {
  toggleFilter () {
    if (this.filter.isOpen === true && !this.isFilterModelEmpty()) {
      this.clearFilter()
    }
    this.filter.isOpen = !this.filter.isOpen
  },
  isFilterModelEmpty () {
    for (let key in this.filter.model) {
      if (this.filter.model[key]) {
        return false
      }
    }
    return true
  },
  clearFilter () {
    for (let key in this.filter.model) {
      this.filter.model[key] = null
    }
    this.pushFilter()
  },
  filterChanged (name, value) {
    this.filter.model[name] = value
    this.pushFilter()
  },
  readFilterParams () {
    const queryFilter = this.$route.query.filter
    if (queryFilter && typeof queryFilter === 'object') {
      for (let key in queryFilter) {
        if (key in this.filter.model) {
          if (this.filter.isOpen === false) {
            this.filter.isOpen = true
          }
          const condition = this.filter.conditions[key]
          if (condition) {
            if (Array.isArray(condition)) {
              if (condition.length === Object.keys(queryFilter[key]).length) {
                this.filter.model[key] = []
                for (let i = 0; i < condition.length; i++) {
                  this.filter.model[key].push(queryFilter[key][condition[i]])
                }
              }
            } else {
              this.filter.model[key] = queryFilter[key][condition]
            }
          } else {
            this.filter.model[key] = queryFilter[key]
          }
        }
      }
    }
  },
  prepareFilterParams () {
    let query = {}
    let model = Object.assign(this.filter.model, this.filter.append)
    for (let key in model) {
      let value = model[key]
      if (value) {
        const condition = this.filter.conditions[key]
        if (condition) {
          if (!query[key]) {
            query[key] = {}
          }
          if (Array.isArray(condition)) {
            // multiple conditions
            if (!Array.isArray(value)) {
              throw new Error('Filter value has to be an array if filter condition is an array.')
            }
            if (value.length === 0) {
              continue
            }
            if (condition.length !== value.length) {
              throw new Error('Filter value and filter condition must have same number of items.')
            }
            for (let i = 0; i < condition.length; i++) {
              query[key][condition[i]] = value[i]
            }
          } else {
            // single condition
            query[key][condition] = value
          }
        } else {
          // no condition
          query[key] = value
        }
      }
    }
    return query
  },
  pushFilter () {
    this.$router.push({
      path: this.$route.path,
      query: { filter: this.prepareFilterParams() }
    })
  }
}

const created = function () {
  this.readFilterParams()
}

export default {
  data,
  methods,
  created
}
