<template>
  <form @submit="onSubmit">
    <form-textarea
          name="comment"
          :label="$t('project', 'Комментарий')"
          :error-message="form.errors.comment"
          :rows="3"
          @change="onChange('comment', $event)"
          @input="onInput('comment', $event)"></form-textarea>

    <form-checkbox
          name="author_visible"
          :label="$t('project', 'Видимый для автора')"
          :error-message="form.errors.author_visible"
          @change="onChange('author_visible', $event)"></form-checkbox>

    <b-button type="submit" variant="primary">{{ $t('project', 'Отправить комментарий') }}</b-button>
  </form>
</template>

<script>
import { FormTextarea, FormCheckbox } from '@/components'
import { FormMixin } from '@/mixins'
import { required } from '@/validators'

export default {
  name: 'cancel-form',
  mixins: [ FormMixin ],
  components: { FormTextarea, FormCheckbox },
  props: [ 'model' ],
  data () {
    return {
      form: {
        model: {
          comment: null,
          author_visible: 0
        },
        service: 'admin/projects/' + this.model.id + '/comment',
        afterSave: (data) => {
          this.$notifySuccess(this.$t('project', 'Комментарий добавлен.'))
          this.$emit('commented', [{ attribute: 'comments', value: data.comments }])
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          comment: {
            required
          },
          author_visible: {}
        }
      }
    }
  }
}
</script>
