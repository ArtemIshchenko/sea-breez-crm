// Agile form mixin
// Data is submitted after every input `change` event
// Form fields should be specified in this.form.model
// If you need more than one form in component pack another form inside different component

import FormValidationMixin from './FormValidationMixin'

const mixins = [FormValidationMixin]

const data = () => {
  return {
    form: {
      model: {},
      saved: {},
      errors: {},
      // additional form attributes
      service: null, // string, required
      serviceMethod: null, // string
      beforeSave: null, // function(data) * you can add additional post params here
      afterSave: null // function(attribute, value, response)
    },
    hasForm: true
  }
}
const methods = {
  setFormBags () {
    const saved = {}
    const errors = {}
    for (let key in this.form.model) {
      saved[key] = false
      errors[key] = null
    }
    this.$set(this.form, 'saved', saved)
    this.$set(this.form, 'errors', errors)
  },

  onInput (attr, val) {
    this.form.model[attr] = val
    this.resetAttributeVaidation(attr)
  },

  onChange (attr, val) {
    this.form.model[attr] = val
    if (this.validateAttribute(attr)) {
      let data = {}
      data[attr] = val

      // beforeSave callback
      if (typeof this.form.beforeSave === 'function') {
        this.form.beforeSave(data)
      }

      // save data
      this.$store.state.http.client.request({
        url: this.form.service,
        method: this.serviceMethod || 'patch',
        data
      }).then(response => {
        this.$emit('updated', { attribute: attr, value: val, response: response.data })
        this.form.saved[attr] = true
        setTimeout(() => {
          this.form.saved[attr] = false
        }, 3000)

        // afterSave callback
        if (typeof this.form.afterSave === 'function') this.form.afterSave(attr, val, response.data)
      }).catch(e => {
        if (e.response && e.response.status === 422 && Array.isArray(e.response.data) && e.response.data[0].field === attr) {
          this.addError(attr, e.response.data[0].message)
        } else {
          this.$notifyError('Данные не были сохранены. ' + e.message)
        }
      })
    }
  },

  onSubmit (e) {
    e.preventDefault()
  }
}

const created = function () {
  this.setFormBags()
}

export default {
  data,
  created,
  methods,
  mixins
}
