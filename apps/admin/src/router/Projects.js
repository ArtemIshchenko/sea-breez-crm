import Projects from '@/views/Projects'
import Index from '@/views/projects/Index'
import Details from '@/views/projects/Details'
import View from '@/views/projects/details/View'
import History from '@/views/projects/details/History'
import Edit from '@/views/projects/details/Edit'
import Location from '@/views/projects/details/Location'
import Files from '@/views/projects/details/Files'
import Gear from '@/views/projects/details/Gear'

export default {
  path: 'projects',
  component: Projects,
  meta: {
    label: 'Проекты'
  },
  children: [
    {
      path: '',
      name: 'projects',
      component: Index,
      meta: {
        hideBreadcrumb: true
      }
    },
    {
      path: ':id',
      component: Details,
      meta: {
        label: 'Проект'
      },
      children: [
        {
          path: '',
          name: 'project/details',
          component: View,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'history',
          name: 'project/history',
          component: History,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'edit',
          name: 'project/edit',
          component: Edit,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'location',
          name: 'project/location',
          component: Location,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'files',
          name: 'project/files',
          component: Files,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'gear',
          name: 'project/gear',
          component: Gear,
          meta: {
            hideBreadcrumb: true
          }
        }
      ]
    }
  ]
}
