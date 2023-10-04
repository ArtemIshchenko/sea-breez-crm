// Standard form mixin
// Form fields should be specified in this.form.model
// If you need more than one form in component pack another form inside different component

import FormValidationMixin from './FormValidationMixin'
import LoadingRequestMixin from './LoadingRequestMixin'

const mixins = [FormValidationMixin, LoadingRequestMixin]

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
      afterSave: null // function(responseData)
    },
    hasForm: true
  }
}

const methods = {
  setFormBags () {
    const errors = {}
    for (let key in this.form.model) {
      errors[key] = null
    }
    this.$set(this.form, 'errors', errors)
  },

  onInput (attr, val) {
    this.form.model[attr] = val
    this.resetAttributeVaidation(attr)
  },

  onChange (attr, val) {
    this.form.model[attr] = val
    this.$v.form.model[attr] && this.$v.form.model[attr].$touch()
  },

  clearForm () {
    for (var key in this.form.model) {
      this.form.model[key] = null
    }
  },

  onSubmit (e) {
    e.preventDefault()
    if (this.isLoadingRequest(this.formSlug)) {
      return
    }

    if (!this.validateForm()) {
      return false
    }

    const data = Object.assign({}, this.form.model)
    /* if (data.phone) {
      data.phone = data.phone.replace(/\D/g, '')
    }
    if (data.mobile_phone) {
      data.mobile_phone = data.mobile_phone.replace(/\D/g, '')
    } */

    // beforeSave callback
    if (typeof this.form.beforeSave === 'function') {
      this.form.beforeSave(data)
    }
    // save data
    this.startLoadingRequest(this.formSlug)
    this.$http.request({
      url: this.form.service,
      method: this.form.serviceMethod || 'post',
      data
    }).then(response => {
      this.stopLoadingRequest(this.formSlug)
      // afterSave callback
      if (typeof this.form.afterSave === 'function') {
        this.form.afterSave(response.data)
      } else {
        this.$notifySuccess('Данные успешно сохранены.')
      }
    }).catch(e => {
      this.stopLoadingRequest(this.formSlug)
      if (e.response && e.response.status === 422 && Array.isArray(e.response.data)) {
        e.response.data.forEach(({ field, message }) => {
          if (field && message) {
            this.addError(field, message)
          }
        }, this)
      } else {
        this.$notifyError('Данные не были сохранены. ' + e.message)
      }
    })
  }
}

const created = function () {
  this.setFormBags()
}

const computed = {
  formSlug () {
    return 'formUpdate.' + Object.keys(this.form.model).join('-')
  }
}

export default {
  data,
  created,
  methods,
  mixins,
  computed
}
