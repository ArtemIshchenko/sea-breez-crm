<template>
  <form @submit="onSubmit">
    <form-dropzone
          name="specification"
          :label="$t('project', 'Спецификация')"
          :error-message="form.errors.specification"
          @input="onChange('specification', $event)"></form-dropzone>

    <list-of-files
          hide-download
          :files="filesToShow"
          @delete-file="form.model.specification = null"></list-of-files>

    <b-button type="submit" variant="primary">{{ $t('project', 'Подать спецификацию') }}</b-button>
  </form>
</template>

<script>
import { FormDropzone, ListOfFiles } from '@/components'
import { FormMixin } from '@/mixins'
import { required, filesNumber } from '@/validators'

export default {
  name: 'specification-form',
  mixins: [ FormMixin ],
  components: { FormDropzone, ListOfFiles },
  props: [ 'model' ],
  data () {
    return {
      form: {
        model: {
          specification: null
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          specification: {
            required,
            filesNumber: filesNumber(1)
          }
        }
      }
    }
  },
  computed: {
    filesToShow () {
      return this.form.model.specification ? [this.form.model.specification.name] : []
    }
  },
  methods: {
    onChange (attr, val) {
      if ((val instanceof FileList || Array.isArray(val)) && val.length > 0) {
        val = val[0]
      }
      this.form.model[attr] = val
      this.$v.form.model[attr].$touch()
    },
    onSubmit (e) {
      e.preventDefault()
      this.resetValidation()

      if (!this.validateForm()) {
        return false
      }

      // prepare form data
      let data = new FormData()
      data.append('specification', this.form.model.specification)
      data.append('status', 'specification_granted')

      // save data
      this.$http.post('admin/projects/' + this.model.id + '/status', data)
        .then(response => {
          this.$notifySuccess(this.$t('project', 'Спецификация подана'))
          this.$emit('updated', [
            { attribute: 'status', value: response.data.status },
            { attribute: 'specification', value: response.data.specification },
            { attribute: 'status_message', value: response.data.status_message }
          ])
        }).catch(e => {
          if (e.response && e.response.status === 422 && Array.isArray(e.response.data)) {
            e.response.data.forEach(({ field, message }) => {
              if (field && message) {
                this.addError(field, message)
              }
            }, this)
          } else {
            this.$notifyError(this.$t('project', 'Данные не были сохранены.') + ' ' + e.message)
          }
        })
    }
  }
}
</script>
