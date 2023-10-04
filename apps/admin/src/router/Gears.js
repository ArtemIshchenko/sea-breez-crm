import Gears from '@/views/Gears'
import Index from '@/views/gears/Index'
import Details from '@/views/gears/Details'
import View from '@/views/gears/details/View'
import Edit from '@/views/gears/details/Edit'

export default {
  path: 'gears',
  component: Gears,
  meta: {
    label: 'Оборудование'
  },
  children: [
    {
      path: '',
      name: 'gears',
      component: Index,
      meta: {
        hideBreadcrumb: true
      }
    },
    {
      path: ':id',
      component: Details,
      meta: {
        label: 'Модель'
      },
      children: [
        {
          path: '',
          name: 'gear/details',
          component: View,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'edit',
          name: 'gear/edit',
          component: Edit,
          meta: {
            hideBreadcrumb: true
          }
        }
      ]
    }
  ]
}
