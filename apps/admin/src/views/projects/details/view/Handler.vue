<template>
  <div class="animated fadeIn">
    <!-- assign designer actions -->
    <b-button
          v-if="model.status === 'sent'"
          variant="primary"
          block
          @click="showAssignDesignerForm = true">{{ $t('project', 'Назначить проектировщика') }}</b-button>
    <b-button
          v-if="model.status === 'designing'"
          variant="outline-primary"
          block
          @click="showAssignDesignerForm = true">{{ $t('project', 'Изменить проектировщика') }}</b-button>

    <!-- reject action -->
    <b-button
          v-if="canReject()"
          variant="danger"
          block
          @click="showRejectForm = true">{{ $t('project', 'Отклонить') }}</b-button>

    <!-- return action -->
    <b-button
          v-if="model.status === 'sent'"
          variant="outline-primary"
          block
          @click="showReturnForm = true">{{ $t('project', 'Вернуть') }}</b-button>

    <!-- set specification action -->
    <b-button
          v-if="model.status === 'sent' || model.status === 'designing'"
          variant="outline-primary"
          block
          @click="showSpecificationForm = true">{{ $t('project', 'Подать спецификацию') }}</b-button>

    <b-button
        v-if="model.status === 'sent'"
        variant="primary"
        block
        @click="updateProjectIn1C()">{{ $t('project', 'Обновить Проект') }}</b-button>
    <!-- finish action -->
    <b-button
          v-if="canFinish()"
          :variant="model.status === 'specification_accepted' ? 'primary' : 'outline-primary'"
          block
          @click="showFinishForm = true">{{ $t('project', 'Завершить проект') }}</b-button>

    <!-- comment action -->
    <b-button
          variant="outline-primary"
          block
          @click="showCommentForm = true">{{ $t('project', 'Добавить комментарий') }}</b-button>

    <!-- assign designer modal -->
    <b-modal
          v-if="model.status === 'sent' || model.status === 'designing'"
          :title="$t('project', (model.status === 'designing' ? 'Изменить' : 'Назначить') + ' проектировщика')"
          class="modal-primary"
          hide-footer
          v-model="showAssignDesignerForm">
      <assign-designer-form :model="model" @designer-assigned="afterDesignerAssigned"></assign-designer-form>
    </b-modal>

    <!-- return to author modal -->
    <b-modal
          v-if="model.status === 'sent'"
          :title="$t('project', 'Вернуть проект на доработку')"
          class="modal-primary"
          hide-footer
          v-model="showReturnForm">
      <return-form :model="model" @updated="afterReturned"></return-form>
    </b-modal>

    <!-- reject modal -->
    <b-modal
          v-if="canReject()"
          :title="$t('project', 'Отклонить проект')"
          class="modal-danger"
          hide-footer
          v-model="showRejectForm">
      <reject-form :model="model" @updated="afterRejected"></reject-form>
    </b-modal>

    <!-- set specification modal -->
    <b-modal
        v-if="model.status === 'sent' || model.status === 'designing'"
        :title="$t('project', 'Подать спецификацию')"
        class="modal-primary"
        hide-footer
        v-model="showSpecificationForm">
    <specification-form :model="model" @updated="afterSpecificationSet"></specification-form>
    </b-modal>

    <!-- finish modal -->
    <b-modal
          v-if="canFinish()"
          :title="$t('project', 'Завершить проект')"
          class="modal-primary"
          hide-footer
          v-model="showFinishForm">
      <finish-form :model="model" @updated="afterFinished"></finish-form>
    </b-modal>

    <!-- comment modal -->
    <b-modal
          :title="$t('project', 'Добавить комментарий')"
          class="modal-primary"
          hide-footer
          v-model="showCommentForm">
      <comment-form :model="model" @commented="afterCommented"></comment-form>
    </b-modal>
  </div>
</template>

<script>
import CommentForm from './handler/CommentForm'
import AssignDesignerForm from './handler/AssignDesignerForm'
import ReturnForm from './handler/ReturnForm'
import RejectForm from './handler/RejectForm'
import FinishForm from './handler/FinishForm'
import SpecificationForm from './handler/SpecificationForm'
import { LoadingRequestMixin } from '@/mixins'

export default {
  name: 'project-handler',
  mixins: [LoadingRequestMixin],
  components: {
    CommentForm, AssignDesignerForm, ReturnForm, RejectForm, FinishForm, SpecificationForm
  },
  props: ['model'],
  data () {
    return {
      showCommentForm: false,
      showAssignDesignerForm: false,
      showReturnForm: false,
      showRejectForm: false,
      showFinishForm: false,
      showSpecificationForm: false
    }
  },
  methods: {
    canFinish () {
      return ['specification_accepted', 'sent', 'designing', 'specification_granted'].includes(this.model.status)
    },
    canReject () {
      if (['sent', 'designing', 'specification_granted', 'specification_accepted'].includes(this.model.status)) {
        return true
      }
      const currentTime = new Date().getTime() / 1000
      if (this.model.status === 'created') {
        if (this.model.date && this.model.date < currentTime) {
          return true
        }
        if (!this.model.date && this.model.updated_at < currentTime - 60 * 60 * 24 * 30) {
          return true
        }
      }
      return false
    },
    afterCommented (e) {
      this.$emit('updated', e)
      this.showCommentForm = false
    },
    afterDesignerAssigned (e) {
      this.$emit('updated', e)
      this.showAssignDesignerForm = false
    },
    afterReturned (e) {
      this.showReturnForm = false
      this.$emit('updated', e)
    },
    afterRejected (e) {
      this.showRejectForm = false
      this.$emit('updated', e)
    },
    afterFinished (e) {
      this.showFinishForm = false
      this.$emit('updated', e)
    },
    afterSpecificationSet (e) {
      this.$emit('updated', e)
      this.showSpecificationForm = false
    },
    async updateProjectIn1C () {
      const successMessage = this.$t('project', 'Проект был успешно отправлен в 1с.')
      const requestName = 'updateProject'
      if (this.isLoadingRequest(requestName)) {
        return
      }
      try {
        this.startLoadingRequest(requestName)
        const response = await this.$http.patch('admin/projects/' + this.model.id + '/update-onec')
        this.stopLoadingRequest(requestName)
        if (response.data) {
          if (response.data.status === 'success') {
            this.$notifySuccess(successMessage)
          } else {
            this.$notifyError(this.$t('project', 'Не удалось обновить проект в 1с.Error code ') + response.data.error)
          }
        }
      } catch (e) {
        this.stopLoadingRequest(requestName)
        this.$notifyError(this.$t('project', 'Не удалось обновить проект в 1с.') + e.message)
      }
    }
  }
}
</script>
