<template>
  <form @submit="onSubmit">
    <form-textarea
          name="message"
          :label="$t('project', 'Сообщение')"
          :error-message="form.errors.message"
          :rows="3"
          @change="onChange('message', $event)"
          @input="onInput('message', $event)"></form-textarea>

    <b-button type="submit" variant="danger">{{ $t('project', 'Отклонить проект') }}</b-button>
  </form>
</template>

<script>
import { FormTextarea } from '@/components'
import { FormMixin } from '@/mixins'

export default {
  name: 'return-form',
  mixins: [ FormMixin ],
  components: { FormTextarea },
  props: ['model'],
  data () {
    return {
      form: {
        model: {
          message: null,
          status: 'rejected'
        },
        service: 'admin/projects/' + this.model.id + '/status',
        serviceMethod: 'patch',
        afterSave: (data) => {
          this.$notifySuccess(this.$t('project', 'Проект успешно отклонен.'))
          this.$emit('updated', [
            { attribute: 'status', value: data.status },
            { attribute: 'status_message', value: data.status_message }
          ])
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          message: {
          }
        }
      }
    }
  }
}
</script>
