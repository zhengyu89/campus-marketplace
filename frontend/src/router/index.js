import { createRouter, createWebHistory } from 'vue-router'
import pinia from '../stores/pinia'
import { useAuthStore } from '../stores/auth'
import AccountView from '../views/AccountView.vue'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import MarketResultsView from '../views/MarketResultsView.vue'
import RegisterView from '../views/RegisterView.vue'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    if (to.hash) {
      return {
        el: to.hash,
        top: 96,
        behavior: 'smooth',
      }
    }

    return {
      top: 0,
      behavior: 'smooth',
    }
  },
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: {
        guestOnly: true,
      },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: {
        guestOnly: true,
      },
    },
    {
      path: '/account',
      name: 'account',
      component: AccountView,
      meta: {
        requiresAuth: true,
        roles: ['user', 'admin'],
      },
    },
    {
      path: '/market',
      name: 'market',
      component: MarketResultsView,
      meta: {
        requiresAuth: true,
        roles: ['user', 'admin'],
      },
    },
  ],
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore(pinia)

  if (!authStore.isBootstrapped) {
    await authStore.bootstrap()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return {
      name: 'login',
      query: {
        redirect: to.fullPath,
      },
    }
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    const redirect = typeof to.query.redirect === 'string' ? to.query.redirect : ''

    if (redirect.startsWith('/')) {
      return redirect
    }

    return {
      name: 'account',
    }
  }

  const allowedRoles = Array.isArray(to.meta.roles) ? to.meta.roles : []

  if (allowedRoles.length > 0 && !allowedRoles.includes(authStore.user?.role)) {
    return {
      name: 'home',
    }
  }

  return true
})

export default router
