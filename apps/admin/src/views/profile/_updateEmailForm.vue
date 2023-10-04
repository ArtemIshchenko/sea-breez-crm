<template>
  <form @submit="onSubmit($event)">
    <p class="text-muted">{{ $t('user', 'Текущий email адрес') }} {{userEmail}}</p>

    <form-input
          name="email"
          :label="$t('user', 'Новый email')"
          :value="form.model.email"
          :error-message="form.errors.email"
          @change="onChange('email', $event)"
          @input="onInput('email', $event)"></form-input>

    <button type="submit" class="btn btn-primary">{{ $t('service', 'Сохранить') }}</button>
  </form>
</template>

<script>
import { FormMixin } from '@/mixins'
import { required, email } from '@/validators'
import { FormInput } from '@/components'

export default {
  name: 'email-form',
  mixins: [FormMixin],
  components: {
    FormInput
  },
  data () {
    return {
      form: {
        model: {
          email: null
        },
        service: 'profile/email',
        serviceMethod: 'patch',
        afterSave: (data) => {
          this.aferSaved(data)
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          email: {
            required,
            email
          }
        }
      }
    }
  },
  computed: {
    userEmail () {
      return this.$store.state.user.email
    }
  },
  methods: {
    async logout () {
      try {
        await this.$store.dispatch('user/logout')
      } catch (e) {
        this.$notifyError(this.$t('user', 'Выход из программы не удался.') + ' ' + e.message)
      }
    },
    aferSaved (data) {
      this.$store.commit('user/updateDetails', ['email', data.email])
      this.clearForm()
      this.$notifySuccess(this.$t('user', 'Ваш новый email адрес успешно сохранен.'))
      this.logout()
    }
  }
}
</script>
