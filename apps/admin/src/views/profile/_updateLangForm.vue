<template>
  <form @submit="onSubmit($event)">
    <form-select
        name="lang"
        :label="$t('user', 'Язык')"
        :value="form.model.lang"
        :options="languages"
        @change="onChange('lang', $event)"
        @input="onInput('lang', $event)"></form-select>

    <button type="submit" class="btn btn-primary">{{ $t('service', 'Сохранить') }}</button>
  </form>
</template>

<script>
import { FormMixin } from '@/mixins'
import { required } from '@/validators'
import { FormSelect } from '@/components'

export default {
  name: 'lang-form',
  mixins: [FormMixin],
  components: {
    FormSelect
  },
  data () {
    return {
      form: {
        model: {
          lang: this.$t('project', this.$store.state.user.lang)
        },
        service: 'profile/lang',
        serviceMethod: 'patch',
        afterSave: (data) => {
          this.afterSaved(data)
        }
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          lang: {
            required
          }
        }
      }
    }
  },
  computed: {
    languages () {
      const result = {}
      Object.entries(this.$params.user.languages).forEach(([key, value]) => {
        result[key] = this.$t('project', value)
      })
      return result
    }
  },
  methods: {
    afterSaved (data) {
      this.$store.commit('user/updateDetails', ['lang', data.lang])
      this.$notifySuccess(this.$t('user', 'Язык успешно сохранен.'))
      window.location.reload()
    }
  }
}
</script>
