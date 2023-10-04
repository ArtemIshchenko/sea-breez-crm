<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-lg-7">
        <!-- Main form -->
        <div class="card">
          <div class="card-header">
            {{ $t('user', 'Ваш профиль') }}
          </div>
          <div class="card-body">
            <form @submit="onSubmit($event)">
              <form-input
                name="first_name"
                :label="$t('user', 'Имя')"
                :value="form.model.first_name"
                :required="!!$v.form.model.first_name && !!$v.form.model.first_name.$params.required"
                :isValid="form.saved.first_name"
                :error-message="form.errors.first_name"
                @change="onChange('first_name', $event)"
                @input="onInput('first_name', $event)"></form-input>

              <form-input
                name="middle_name"
                :label="$t('user', 'Отчество')"
                :value="form.model.middle_name"
                :required="!!$v.form.model.middle_name && !!$v.form.model.middle_name.$params.required"
                :isValid="form.saved.middle_name"
                :error-message="form.errors.middle_name"
                @change="onChange('middle_name', $event)"
                @input="onInput('middle_name', $event)"></form-input>

              <form-input
                name="last_name"
                :label="$t('user', 'Фамилия')"
                :required="!!$v.form.model.last_name && !!$v.form.model.last_name.$params.required"
                :value="form.model.last_name"
                :isValid="form.saved.last_name"
                :error-message="form.errors.last_name"
                @change="onChange('last_name', $event)"
                @input="onInput('last_name', $event)"></form-input>
              <template>
                <form-radio
                  name="provider"
                  :label="$t('user', 'Пользователь компании')"
                  :value="form.model.provider"
                  :required="!!$v.form.model.provider && !!$v.form.model.provider.$params.required"
                  :isValid="form.saved.provider"
                  :error-message="form.errors.provider"
                  :options="form.provider_options"
                  @change="onChange('provider', $event)"
                  @input="onInput('provider', $event)"/>
              </template>
              <form-input
                name="company"
                :label="$t('user', 'Компания')"
                :required="!!$v.form.model.company && !!$v.form.model.company.$params.required"
                :value="form.model.company"
                :isValid="form.saved.company"
                :error-message="form.errors.company"
                @change="onChange('company', $event)"
                @input="onInput('company', $event)"></form-input>

              <form-masked
                name="phone"
                :label="$t('user', 'Номер рабочего телефона')"
                :value="form.model.phone"
                :required="!!$v.form.model.phone && !!$v.form.model.phone.$params.required"
                placeholder="+38 (067) 888-88-88"
                placeholder-char="+"
                type="tel"
                :isValid="form.saved.phone"
                :error-message="form.errors.phone"
                @change="onChange('phone', $event)"
                @input="onInput('phone', $event)"
              ></form-masked>

              <form-masked
                name="mobile_phone"
                :label="$t('user', 'Номер мобильного телефона')"
                :value="form.model.mobile_phone"
                :required="!!$v.form.model.mobile_phone && !!$v.form.model.mobile_phone.$params.required"
                :isValid="form.saved.mobile_phone"
                :error-message="form.errors.mobile_phone"
                @change="onChange('mobile_phone', $event)"
                @input="onInput('mobile_phone', $event)"></form-masked>

              <button type="submit" class="btn btn-primary">{{ $t('service', 'Сохранить') }}</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <!-- Email update -->
        <div class="card">
          <div class="card-header">
            {{ $t('user', 'Изменение пароля') }}
          </div>
          <div class="card-body">
            <update-password-form></update-password-form>
          </div>
        </div>

        <!-- Password update -->
        <div class="card">
          <div class="card-header">
            {{ $t('user', 'Изменение электронного адреса') }}
          </div>
          <div class="card-body">
            <update-email-form></update-email-form>
          </div>
        </div>

        <!-- Lang update -->
        <div class="card">
          <div class="card-header">
            {{ $t('user', 'Изменение языка') }}
          </div>
          <div class="card-body">
            <update-lang-form></update-lang-form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { FormInput, FormRadio, FormMasked } from '@/components'
import UpdatePasswordForm from './profile/_updatePasswordForm'
import UpdateEmailForm from './profile/_updateEmailForm'
import UpdateLangForm from './profile/_updateLangForm'
import { required, maxLength } from '@/validators'
import { FormMixin } from '@/mixins'

export default {
  name: 'profile',
  mixins: [ FormMixin ],
  components: {
    FormMasked,
    FormInput,
    FormRadio,
    UpdatePasswordForm,
    UpdateEmailForm,
    UpdateLangForm
  },
  data () {
    return {
      form: {
        model: {
          first_name: this.$store.state.user.first_name,
          middle_name: this.$store.state.user.middle_name,
          last_name: this.$store.state.user.last_name,
          company: this.$store.state.user.company,
          phone: this.$store.state.user.phone,
          mobile_phone: this.$store.state.user.mobile_phone,
          provider: this.$store.state.user.provider
        },
        provider_options: [
        ],
        service: 'profile',
        serviceMethod: 'patch',
        afterSave: (data) => {
          this.profileUpdated(data)
        }
      }
    }
  },
  validations () {
    // let mobileRegMin = /^[0-9]{10}$/
    let mobileRegFull = /^\+38 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}$/
    const mustBeReg = (param) => (value) => !value || param.test(value)
    return {
      form: {
        model: {
          first_name: {
            maxLength: maxLength(255)
          },
          provider: {
            required
          },
          middle_name: {
            maxLength: maxLength(255)
          },
          last_name: {
            required,
            maxLength: maxLength(255)
          },
          company: {
            required,
            maxLength: maxLength(255)
          },
          phone: {
            phone: mustBeReg(mobileRegFull),
            maxLength: maxLength(255)
          },
          mobile_phone: {
            phone: mustBeReg(mobileRegFull),
            required,
            maxLength: maxLength(255)
          }
        }
      }
    }
  },
  computed: {
    user () {
      return this.$store.state.user
    }
  },
  methods: {
    profileUpdated (data) {
      this.$notifySuccess(this.$t('user', 'Данные вашего профиля успешно сохранены.'))
      for (let attr in this.form.model) {
        this.$store.commit('user/updateDetails', [attr, data[attr]])
      }
    }
  }
}
</script>
