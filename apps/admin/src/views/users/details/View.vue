<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-xl-3 col-lg-4 col-md-5 order-md-last">
        <!-- user actions -->
        <div class="p-4">
          <!-- suspend action -->
          <b-button
                 v-show="model.status === 'active'"
                 variant="danger"
                 block
                 @click="updateStatus('suspended')">{{ $t('project', 'Заблокировать') }}</b-button>

          <!-- unsuspend action -->
          <b-button
                v-show="model.status === 'suspended'"
                variant="primary"
                block
                @click="updateStatus('active')">{{ $t('project', 'Разблокировать') }}</b-button>

          <!-- comment user action -->
          <b-button
                variant="primary"
                block
                @click="showCommentForm = true">{{ $t('project', 'Комментировать') }}</b-button>

          <!-- set manager to customer action -->
          <b-button
                v-if="model.role === 'customer'"
                variant="primary"
                block
                @click="showManagerForm = true">{{ $t('project', 'Назначить менеджера') }}</b-button>

          <!-- comment modal -->
          <b-modal
                :title="$t('project', 'Оставить комментарий пользователю')"
                class="modal-primary"
                hide-footer
                v-model="showCommentForm">
            <comment-form :user-id="model.id" @commented="afterComment"></comment-form>
          </b-modal>

          <!-- set manager modal -->
          <b-modal
                v-if="model.role === 'customer'"
                :title="$t('project', 'Назначить менеджера')"
                class="modal-primary"
                hide-footer
                v-model="showManagerForm">
            <manager-form :user="model" :managers="managersList" @manager-assigned="afterManagerAssigned"></manager-form>
          </b-modal>

          <!-- reset password action -->
          <b-button
              v-if="(model.temporary_pass === '' || model.temporary_pass === null)"
              variant="primary"
              block
              @click="showResetPasswordForm = true">{{ $t('project', 'Сбросить пароль') }}</b-button>
          <!-- send new password action -->
          <b-button
              v-else-if="model.temporary_pass !== '' && model.temporary_pass !== null"
              variant="primary"
              block
              @click="sendNewPassword">{{ $t('project', 'Отправить новый пароль') }}</b-button>

          <!-- reset password modal -->
          <b-modal
              :title="$t('project', 'Сбросить пароль')"
              class="modal-primary"
              hide-footer
              v-model="showResetPasswordForm">
            <reset-password-form :user-id="model.id" @assigned-new-password="afterNewPasswordAssigned"></reset-password-form>
          </b-modal>
        </div>
      </div>

      <!-- user details -->
      <div class="col-xl-9 col-lg-8 col-md-7 order-md-first">
        <dl class="row">
          <dt class="col-xl-3">{{ $t('user', 'Роль') }}</dt>
          <dd class="col-xl-9">{{ $t('user', $params.user.roles[model.role]) }}</dd>

          <dt class="col-xl-3">{{ $t('user', 'Email адрес') }}</dt>
          <dd class="col-xl-9"><a :href="'mailto:' + model.email">{{ model.email }}</a></dd>

          <template  v-if="model.role === 'customer'">
            <dt class="col-xl-3">{{ $t('project', 'Проекты') }}</dt>
            <dd class="col-xl-9">
              <router-link :to="{ name: 'projects', query: { filter: { author: model.id } } }">
                {{ model.projects_number }}
              </router-link>
            </dd>
          </template>

          <dt class="col-xl-3">{{ $t('user', 'Компания') }}</dt>
          <dd class="col-xl-9">{{ model.company }}</dd>

          <dt class="col-xl-3">{{ $t('user', 'Рабочий телефон') }}</dt>
          <dd class="col-xl-9">{{ model.phone }}</dd>

          <dt class="col-xl-3">{{ $t('user', 'Мобильный телефон') }}</dt>
          <dd class="col-xl-9">{{ model.mobile_phone }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата регистрации') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.created_at) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата последнего изменения учетной записи') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.updated_at) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Дата последней авторизации') }}</dt>
          <dd class="col-xl-9">{{ toDate(model.entered_at) }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Менеджер') }}</dt>
          <dd class="col-xl-9">
            <router-link v-if="model.manager" :to="{ name: 'user/details', params: { id: model.manager.id } }">
              {{ (model.manager.first_name + ' ' + model.manager.last_name).trim() }}
            </router-link>
            <template v-else>
              {{ $t('project', 'Менеджер не назначен') }}
            </template>
          </dd>

          <dt class="col-xl-3">{{ $t('project', 'Количество активных проектов') }}</dt>
          <dd class="col-xl-9">{{ model.active_projects_number }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Количество завершенных проектов') }}</dt>
          <dd class="col-xl-9">{{ model.finished_projects_number }}</dd>

          <dt class="col-xl-3">{{ $t('project', 'Комментарии') }}</dt>
          <dd class="col-xl-9">
            <p v-for="(comment, i) in model.comments" :key="i">
              {{ toDate(comment.created_at) }}
              <br>
              {{ (comment.author.first_name + ' ' + comment.author.last_name).trim() }}
              <br>
              <multi-line :text="comment.body"></multi-line>
            </p>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script>
import { DateHelper } from '@/helpers'
import { MultiLine } from '@/components'
import CommentForm from './view/CommentForm'
import ManagerForm from './view/ManagerForm'
import ResetPasswordForm from './view/ResetPasswordForm'
import { LoadingRequestMixin } from '@/mixins'

export default {
  name: 'user-view',
  mixins: [LoadingRequestMixin],
  components: {
    MultiLine, CommentForm, ManagerForm, ResetPasswordForm
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      showCommentForm: false,
      showManagerForm: false,
      showResetPasswordForm: false,
      managersList: {}
    }
  },
  created () {
    if (this.model.role === 'customer') {
      this.getManagersList()
    }
  },
  methods: {
    toDate (timestamp) {
      return DateHelper.toHumanStr(timestamp, true)
    },
    async updateStatus (status) {
      const requestName = 'updateStatus.' + status
      if (this.isLoadingRequest(requestName)) {
        return
      }
      try {
        this.startLoadingRequest(requestName)
        const response = await this.$http.patch('admin/users/' + this.model.id + '/status', {
          status
        })
        this.stopLoadingRequest(requestName)
        if (response.data) {
          this.$emit('updated', [{ attribute: 'status', value: response.data.status }])
          this.$notifySuccess(response.data.status === 'suspended'
            ? this.$t('user', 'Участник успешно заблокирован.')
            : this.$t('user', 'Участник успешно разблокирован.'))
        }
      } catch (e) {
        this.stopLoadingRequest(requestName)
        this.$notifyError(this.$t('user', 'Не удалось изменить статус участника. ') + e.message)
      }
    },
    afterComment (data) {
      this.showCommentForm = false
      this.$emit('updated', [{ attribute: 'comments', value: data.comments }])
    },
    async getManagersList () {
      try {
        const response = await this.$http.get('admin/users/list', {
          params: {
            role: 'manager',
            active: 1
          }
        })
        if (response.data) {
          this.managersList = response.data
        }
      } catch (e) {
        this.$notifyError(this.$t('user', 'Не удалось получить список менеджеров. ') + e.message)
      }
    },
    afterManagerAssigned (data) {
      this.showManagerForm = false
      this.$emit('updated', [{ attribute: 'manager', value: data.manager }])
    },
    afterNewPasswordAssigned (data) {
      this.showResetPasswordForm = false
      this.$emit('updated', [{ attribute: 'temporary_pass', value: data.temporary_pass }])
    },
    async sendNewPassword () {
      const requestName = 'sendNewPassword.'
      if (this.isLoadingRequest(requestName)) {
        return
      }
      try {
        this.startLoadingRequest(requestName)
        const response = await this.$http.post('admin/users/' + this.model.id + '/send-new-password')
        this.stopLoadingRequest(requestName)
        if (response.data) {
          this.$emit('updated', [{ attribute: 'temporary_pass', value: response.data.temporary_pass }])
          this.$notifySuccess(this.$t('user', 'Новый пароль отправлен пользователю.'))
        }
      } catch (e) {
        this.stopLoadingRequest(requestName)
        this.$notifyError(this.$t('user', 'Не удалось отправить пароль пользователю. ') + e.message)
      }
    }
  },
  watch: {
    model: {
      deep: true,
      handler: function (newModel) {
        if (newModel.role === 'customer' && Object.keys(this.managersList).length === 0) {
          this.getManagersList()
        }
      }
    }
  }
}
</script>
