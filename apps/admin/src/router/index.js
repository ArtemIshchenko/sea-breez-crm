import Vue from 'vue'
import Router from 'vue-router'
import qs from 'qs'

// Containers
import DefaultContainer from '@/containers/DefaultContainer'

// Views
// import Dashboard from '@/views/Dashboard'
import Profile from '@/views/Profile'

// Subroutes
import Users from './Users'
import Projects from './Projects'
import Gears from './Gears'

Vue.use(Router)

export default new Router({
  mode: 'hash',
  linkActiveClass: 'open active',
  scrollBehavior: () => ({ y: 0 }),
  routes: [
    {
      path: '/',
      redirect: '/projects',
      meta: {
        hideBreadcrumb: true
      },
      component: DefaultContainer,
      children: [
        // {
        //   path: 'dashboard',
        //   name: 'Dashboard',
        //   component: Dashboard
        // },
        {
          path: 'profile',
          name: 'profile',
          component: Profile,
          meta: {
            label: 'Профиль'
          }
        },
        Users,
        Projects,
        Gears
      ]
    }
  ],
  // set custom query resolver
  parseQuery (query) {
    return qs.parse(query)
  },
  stringifyQuery (query) {
    var result = qs.stringify(query)
    return result ? ('?' + result) : ''
  }
})
