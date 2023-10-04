<template>
  <form @submit="onSubmit">
    <form-input
        name="new_password"
        type="password"
        :label="$t('project', 'Введите новый пароль')"
        :value="form.model.new_password"
        :error-message="form.errors.new_password"
        @change="onChange('new_password', $event)"
        @input="onInput('new_password', $event)">
    </form-input>

    <form-input
        name="new_password_repeat"
        type="password"
        :label="$t('project', 'Повторите пароль')"
        :value="form.model.new_password_repeat"
        :error-message="form.errors.new_password_repeat"
        @change="onChange('new_password_repeat', $event)"
        @input="onInput('new_password_repeat', $event)">
    </form-input>

    <b-button type="submit" variant="primary">{{ $t('service', 'Сохранить') }}</b-button>
  </form>
</template>

<script>

import { FormInput } from '@/components'
import { FormMixin } from '@/mixins'
import { required, sameAs } from '@/validators'

export default {
  name: 'reset-password-form',
  mixins: [ FormMixin ],
  components: { FormInput },
  props: [ 'userId' ],
  data () {
    return {
      form: {
        model: {
          new_password: null,
          new_password_repeat: null
        },
        service: 'admin/users/' + this.userId + '/assign-new-password',
        afterSave: this.afterSave
      },
      validationMessages: {
        passwordSameAs: this.$t('user', 'Значение нового пароля и его повтора должны совпадать.')
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          new_password: {
            required
          },
          new_password_repeat: {
            required,
            passwordSameAs: sameAs('new_password')
          }
        }
      }
    }
  },
  methods: {
    afterSave (data) {
      this.$emit('assigned-new-password', data)
      this.$notifySuccess(this.$t('user', 'Пользователю успешно изменен пароль.'))
      this.clearForm()
    }
  }
}
</script>
