import { defineStore } from 'pinia'
import { api } from '../api/http'
import {
  clearStoredSession,
  getStoredToken,
  getStoredUser,
  persistSession,
} from '../auth/storage'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: getStoredToken(),
    user: getStoredUser(),
    isBootstrapped: false,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
  },
  actions: {
    async bootstrap() {
      if (this.isBootstrapped) {
        return
      }

      if (!this.token) {
        this.user = null
        this.isBootstrapped = true
        return
      }

      try {
        const response = await api.get('/auth/me')
        const user = response.data?.data?.user ?? null

        if (!user) {
          throw new Error('Missing authenticated user payload')
        }

        this.user = user
        persistSession(this.token, user)
      } catch {
        this.clearSession()
      } finally {
        this.isBootstrapped = true
      }
    },
    async register(payload) {
      return api.post('/auth/register', payload)
    },
    async login(payload) {
      const response = await api.post('/auth/login', payload)
      const { token, user } = response.data.data

      this.token = token
      this.user = user
      this.isBootstrapped = true
      persistSession(token, user)

      return response.data.data
    },
    logout() {
      this.clearSession()
    },
    clearSession() {
      this.token = null
      this.user = null
      this.isBootstrapped = true
      clearStoredSession()
    },
  },
})
