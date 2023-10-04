<template>
  <form @submit="onSubmit($event)" class="has-loading">

    <form-image-input
          :name="name"
          :label="label"
          :multiple="multiple"
          :hint="hint"
          :value="currentImages"
          :preview-class="previewClass"
          :isValid="form.saved.images"
          :error-message="form.errors.images"
          @input="onChange($event)"></form-image-input>
  </form>
</template>

<script>
import { FormAgileMixin } from '@/mixins'
import { fileSize, fileExtension } from '@/validators'
import FormImageInput from './ImageInput'
export default {
  name: 'images-upload-form',
  props: {
    service: {
      type: String,
      required: true
    },
    name: String,
    label: String,
    hint: String,
    multiple: {
      type: Boolean,
      default: false
    },
    currentImages: [String, Array],
    fileSize: Number,
    fileExtension: {
      type: [String, Array],
      default: 'png, jpg, jpeg, gif'
    },
    previewClass: String
  },
  mixins: [FormAgileMixin],
  data () {
    return {
      form: {
        model: {
          images: null
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          images: {
            fileSize: fileSize(this.fileSize),
            fileExtension: fileExtension(this.fileExtension)
          }
        }
      }
    }
  },
  created () {
    if (!this.name) {
      this.name = this.multiple ? 'images' : 'image'
    }
  },
  methods: {
    onChange (images) {
      this.resetValidation()

      // ensure model's not empty
      if (!images) return

      // update model
      this.form.model.images = images

      // validate image
      if (!this.validateAttribute('images')) return

      // prepare form data
      let data = new FormData()
      if (!this.multiple) {
        data.append(this.name, this.form.model.images[0])
      } else {
        for (let file of this.form.model.images) {
          data.append(this.name + '[]', file)
        }
      }

      // submit data
      this.$store.state.http.client.post(this.service, data).then(({ data }) => {
        this.$emit('change', data[this.name])
        this.upolading = false
        this.form.saved.images = true
        setTimeout(() => {
          this.form.saved.images = false
        }, 3000)
      }).catch(e => {
        this.upolading = false
        if (e.response && e.response.status === 422 && Array.isArray(e.response.data) && e.response.data[0].field === this.name) {
          this.addError('images', e.response.data[0].message)
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Data was not saved.<br>' + e.message,
            duration: -1
          })
        }
      })
    }
  },
  components: {
    FormImageInput
  }
}
</script>
