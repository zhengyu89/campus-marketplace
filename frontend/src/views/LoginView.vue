<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const form = reactive({
  email: '',
  password: '',
})
const errors = ref({})
const apiError = ref('')
const isSubmitting = ref(false)
const successMessage = computed(() =>
  route.query.registered === '1' ? 'Account created successfully. Please log in.' : ''
)
const pendingSearch = computed(() =>
  typeof route.query.search === 'string' ? route.query.search.trim() : ''
)

function validate() {
  const nextErrors = {}

  if (!form.email.trim()) {
    nextErrors.email = 'Email is required.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    nextErrors.email = 'Enter a valid email address.'
  }

  if (!form.password) {
    nextErrors.password = 'Password is required.'
  }

  errors.value = nextErrors

  return Object.keys(nextErrors).length === 0
}

async function submit() {
  apiError.value = ''

  if (!validate()) {
    return
  }

  isSubmitting.value = true

  try {
    await authStore.login({
      email: form.email.trim(),
      password: form.password,
    })

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : ''
    await router.push(redirect.startsWith('/') ? redirect : { name: 'account' })
  } catch (error) {
    errors.value = error.response?.data?.errors ?? {}
    apiError.value = error.response?.data?.message ?? 'Unable to log in right now.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <section class="rounded-4 border bg-white p-4 shadow-sm">
          <h1 class="h3 mb-3">Log In</h1>
          <p class="text-muted">
            Use your campus marketplace account to access protected features.
          </p>

          <div v-if="successMessage" class="alert alert-success">
            {{ successMessage }}
          </div>

          <div v-if="pendingSearch" class="alert alert-info">
            Log in to view results for "<strong>{{ pendingSearch }}</strong>".
          </div>

          <div v-if="apiError" class="alert alert-danger">
            {{ apiError }}
          </div>

          <form class="d-grid gap-3" @submit.prevent="submit">
            <div>
              <label class="form-label" for="email">Email</label>
              <input
                id="email"
                v-model="form.email"
                class="form-control"
                type="email"
                autocomplete="email"
              />
              <div v-if="errors.email" class="text-danger small mt-1">
                {{ errors.email }}
              </div>
            </div>

            <div>
              <label class="form-label" for="password">Password</label>
              <input
                id="password"
                v-model="form.password"
                class="form-control"
                type="password"
                autocomplete="current-password"
              />
              <div v-if="errors.password" class="text-danger small mt-1">
                {{ errors.password }}
              </div>
            </div>

            <button class="btn btn-dark" type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'Logging in...' : 'Log In' }}
            </button>
          </form>

          <p class="text-muted mt-3 mb-0">
            Need an account?
            <RouterLink :to="{ name: 'register' }">Register here</RouterLink>
          </p>
        </section>
      </div>
    </div>
  </main>
</template>
