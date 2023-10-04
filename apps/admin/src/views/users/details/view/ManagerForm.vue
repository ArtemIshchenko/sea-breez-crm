<template>
  <form @submit="onSubmit">
    <form-simple-select
          name="manager_id"
          :label="$t('project', 'Менеджер')"
          :value="form.model.manager_id"
          :error-message="form.errors.manager_id"
          :options="managers"
          allowEmpty
          @change="onChange('manager_id', $event)"
          @input="onInput('manager_id', $event)"></form-simple-select>

    <b-button type="submit" variant="primary">{{ $t('service', 'Сохранить') }}</b-button>
  </form>
</template>

<script>

import { FormSimpleSelect } from '@/components'
import { FormMixin } from '@/mixins'
import { required } from '@/validators'

export default {
  name: 'comment-form',
  mixins: [ FormMixin ],
  components: { FormSimpleSelect },
  props: [ 'user', 'managers' ],
  data () {
    return {
      form: {
        model: {
          manager_id: this.user.manager ? this.user.manager.id : null
        },
        service: 'admin/users/' + this.user.id + '/manager',
        serviceMethod: 'patch',
        afterSave: this.afterSave
      }
    }
  },
  validations () {
    return {
      form: {
        model: {
          manager_id: {
            required
          }
        }
      }
    }
  },
  methods: {
    afterSave (data) {
      this.$emit('manager-assigned', data)
      this.$notifySuccess(this.$t('user', 'Пользователю успешно назначен новый менеджер.'))
    }
  }
}
</script>
