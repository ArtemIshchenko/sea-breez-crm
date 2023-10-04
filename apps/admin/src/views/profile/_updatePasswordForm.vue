<template>
  <form @submit="onSubmit($event)">
    <form-input
          name="password"
          type="password"
          :label="$t('user', 'Пароль')"
          :value="form.model.password"
          :error-message="form.errors.password"
          @change="onChange('password', $event)"
          @input="onInput('password', $event)"></form-input>

    <form-input
          name="new_password"
          type="password"
          :label="$t('user', 'Новый пароль')"
          :value="form.model.new_password"
          :error-message="form.errors.new_password"
          @change="onChange('new_password', $event)"
          @input="onInput('new_password', $event)"></form-input>

    <form-input
          name="repeat_new_password"
          type="password"
          :label="$t('user', 'Повтор нового пароля')"
          :value="form.model.repeat_new_password"
          :error-message="form.errors.repeat_new_password"
          @change="onChange('repeat_new_password', $event)"
          @input="onInput('repeat_new_password', $event)"></form-input>
    <button type="submit" class="btn btn-primary">{{ $t('service', 'Сохранить') }}</button>
  </form>
</template>

<script>
import { FormMixin } from '@/mixins'
import { required, sameAs } from '@/validators'
import { FormInput } from '@/components'

export default {
  name: 'password-form',
  mixins: [FormMixin],
  data () {
    return {
      form: {
        model: {
          password: null,
          new_password: null,
          repeat_new_password: null
        },
        service: 'profile/password',
        serviceMethod: 'patch',
        afterSave: () => {
          this.aferPasswordSaved()
        }
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
          password: {
            required
          },
          new_password: {
            required
          },
          repeat_new_password: {
            required,
            passwordSameAs: sameAs('new_password')
          }
        }
      }
    }
  },
  methods: {
    aferPasswordSaved () {
      this.clearForm()
      this.$notifySuccess(this.$t('user', 'Ваш новый пароль успешно сохранен.'))
    }
  },
  components: {
    FormInput
  }
}
</script>
