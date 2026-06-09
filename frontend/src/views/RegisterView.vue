<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})
const errors = ref({})
const apiError = ref('')
const isSubmitting = ref(false)

function validate() {
  const nextErrors = {}

  if (!form.name.trim()) {
    nextErrors.name = 'Name is required.'
  }

  if (!form.email.trim()) {
    nextErrors.email = 'Email is required.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    nextErrors.email = 'Enter a valid email address.'
  }

  if (!form.password) {
    nextErrors.password = 'Password is required.'
  } else if (form.password.length < 8) {
    nextErrors.password = 'Password must be at least 8 characters.'
  }

  if (!form.passwordConfirmation) {
    nextErrors.passwordConfirmation = 'Please confirm your password.'
  } else if (form.passwordConfirmation !== form.password) {
    nextErrors.passwordConfirmation = 'Passwords do not match.'
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
    await authStore.register({
      name: form.name.trim(),
      email: form.email.trim(),
      password: form.password,
    })

    await router.push({
      name: 'login',
      query: {
        registered: '1',
      },
    })
  } catch (error) {
    errors.value = error.response?.data?.errors ?? {}
    apiError.value = error.response?.data?.message ?? 'Unable to create your account right now.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-6">
        <section class="rounded-4 border bg-white p-4 shadow-sm">
          <h1 class="h3 mb-3">Create Account</h1>
          <p class="text-muted">
            Register as a marketplace user to create listings and send offers later.
          </p>

          <div v-if="apiError" class="alert alert-danger">
            {{ apiError }}
          </div>

          <form class="d-grid gap-3" @submit.prevent="submit">
            <div>
              <label class="form-label" for="name">Full name</label>
              <input
                id="name"
                v-model="form.name"
                class="form-control"
                type="text"
                autocomplete="name"
              />
              <div v-if="errors.name" class="text-danger small mt-1">
                {{ errors.name }}
              </div>
            </div>

            <div>
              <label class="form-label" for="register-email">Email</label>
              <input
                id="register-email"
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
              <label class="form-label" for="register-password">Password</label>
              <input
                id="register-password"
                v-model="form.password"
                class="form-control"
                type="password"
                autocomplete="new-password"
              />
              <div v-if="errors.password" class="text-danger small mt-1">
                {{ errors.password }}
              </div>
            </div>

            <div>
              <label class="form-label" for="register-password-confirmation">
                Confirm password
              </label>
              <input
                id="register-password-confirmation"
                v-model="form.passwordConfirmation"
                class="form-control"
                type="password"
                autocomplete="new-password"
              />
              <div v-if="errors.passwordConfirmation" class="text-danger small mt-1">
                {{ errors.passwordConfirmation }}
              </div>
            </div>

            <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'Creating account...' : 'Register' }}
            </button>
          </form>

          <p class="text-muted mt-3 mb-0">
            Already have an account?
            <RouterLink :to="{ name: 'login' }">Log in here</RouterLink>
          </p>
        </section>
      </div>
    </div>
  </main>
</template>
