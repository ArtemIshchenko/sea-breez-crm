import Users from '@/views/Users'
import Index from '@/views/users/Index'
import Administrators from '@/views/users/index/Administrators'
import Customers from '@/views/users/index/Customers'
import Managers from '@/views/users/index/Managers'
import Designers from '@/views/users/index/Designers'
import Details from '@/views/users/Details'
import View from '@/views/users/details/View'
import Edit from '@/views/users/details/Edit'
import History from '@/views/users/details/History'
// import History from '@/views/users/details/History'

export default {
  path: 'users',
  name: 'users',
  component: Users,
  redirect: 'users/customers',
  meta: {
    label: 'Пользователи'
  },
  children: [
    {
      path: '',
      redirect: '/users/customers',
      component: Index,
      meta: {
        hideBreadcrumb: true
      },
      children: [
        {
          path: 'customers',
          name: 'users/customers',
          component: Customers,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'managers',
          name: 'users/managers',
          component: Managers,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'designers',
          name: 'users/designers',
          component: Designers,
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'administrators',
          name: 'users/administrators',
          component: Administrators,
          meta: {
            hideBreadcrumb: true
          }
        }
      ]
    },
    {
      path: ':id',
      component: Details,
      meta: {
        label: 'Пользователь'
      },
      children: [
        {
          path: '',
          component: View,
          name: 'user/details',
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'history',
          component: History,
          name: 'user/history',
          meta: {
            hideBreadcrumb: true
          }
        },
        {
          path: 'edit',
          component: Edit,
          name: 'user/edit',
          meta: {
            hideBreadcrumb: true
          }
        }
      ]
    }
  ]
}
