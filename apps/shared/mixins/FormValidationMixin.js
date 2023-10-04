// Form validation methods
// Should be used with FormMixin or FormAgileMixin
import ValidationMessages from '../validators/ValidationMessages'

export default {
  methods: {
    getValidationError (attr) {
      const validationObject = this.$v.form.model[attr]

      // client validation error
      let errorKey
      let validationParams

      // find the validator which didn't pass and it's params
      for (let key in validationObject.$params) {
        if (validationObject[key] === false) {
          errorKey = key
          validationParams = Object.assign({}, validationObject.$params[key])
          delete validationParams.type
          break
        }
      }
      // get raw validation message
      let messages = ValidationMessages
      if (this.validationMessages !== null && typeof this.validationMessages === 'object') {
        messages = Object.assign(ValidationMessages, this.validationMessages)
      }
      let message = messages[errorKey]
      if (!message) {
        throw new Error('Validation message was not found for key ' + errorKey)
      }

      // insert params into message if any
      if (validationParams !== null) {
        for (let key in validationParams) {
          message = message.replace('{' + key + '}', validationParams[key])
        }
      }
      return message
    },

    validateAttribute (attr) {
      // return true if no validation rules for the attribute
      if (!this.$v || !this.$v.form.model.hasOwnProperty(attr)) return true

      this.$v.form.model[attr].$touch()
      if (this.$v.form.model[attr].$invalid) {
        this.form.errors[attr] = this.getValidationError(attr)
        return false
      } else {
        this.form.errors[attr] = null
        return true
      }
    },

    validateForm () {
      if (!this.$v) return true

      this.$v.form.$touch()
      if (this.$v.form.$invalid) {
        for (let attr in this.form.errors) {
          if (this.$v.form.model[attr] && this.$v.form.model[attr].$invalid) {
            this.form.errors[attr] = this.getValidationError(attr)
          }
        }
        return false
      } else {
        for (let attr in this.form.errors) {
          this.form.errors[attr] = null
        }
        return true
      }
    },

    addError (attr, message) {
      this.form.errors[attr] = message
    },

    resetAttributeVaidation (attr) {
      this.form.errors[attr] = null
      if (this.$v && this.$v.form.model.hasOwnProperty(attr)) {
        this.$v.form.model[attr].$reset()
      }
    },

    resetValidation () {
      for (let attr in this.form.errors) {
        this.resetAttributeVaidation(attr)
      }
    }
  }
}
