<template>
  <form @submit="onSubmit">
    <p class="text-muted">
      {{ $t('user', 'Комментарии доступны только администраторам и менеджерам клиента.') }}
    </p>
    <form-textarea
          name="comment"
          :label="$t('project', 'Текст комментария')"
          :error-message="form.errors.comment"
          :rows="3"
          @change="onChange('comment', $event)"
          @input="onInput('comment', $event)"></form-textarea>

    <b-button type="submit" variant="primary">{{ $t('service', 'Сохранить') }}</b-button>
  </form>
</template>

<script>

import { FormTextarea } from '@/components'
import { required } from '@/validators'
import { FormMixin } from '@/mixins'
export default {
  name: 'comment-form',
  mixins: [ FormMixin ],
  components: { FormTextarea },
  props: [ 'userId' ],
  data () {
    return {
      form: {
        model: {
          comment: null
        },
        service: 'admin/users/' + this.userId + '/comment',
        afterSave: this.afterSave
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          comment: {
            required
          }
        }
      }
    }
  },
  methods: {
    afterSave (data) {
      this.$emit('commented', data)
      this.$notifySuccess(this.$t('user', 'Ваш комментарий пользователю успешно сохранен.'))
      this.clearForm()
    }
  }
}
</script>
