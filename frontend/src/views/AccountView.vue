<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const user = computed(() => authStore.user)
const roleDescription = computed(() => {
  if (user.value?.role === 'admin') {
    return 'Admins can manage categories and view all marketplace listings.'
  }

  return 'Registered users can create listings, manage their own listings, and send offers.'
})
</script>

<template>
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <section class="rounded-4 border bg-white p-4 p-lg-5 shadow-sm">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
              <span class="badge text-bg-success mb-2">Protected Route</span>
              <h1 class="h3 mb-1">Your Account</h1>
              <p class="text-muted mb-0">
                This page confirms the JWT session and router guard are working.
              </p>
            </div>

            <span class="badge rounded-pill text-bg-dark px-3 py-2 text-uppercase">
              {{ user?.role }}
            </span>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">User ID</div>
                <div class="fw-semibold">{{ user?.user_id }}</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Name</div>
                <div class="fw-semibold">{{ user?.name }}</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Email</div>
                <div class="fw-semibold">{{ user?.email }}</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small">Role</div>
                <div class="fw-semibold text-capitalize">{{ user?.role }}</div>
              </div>
            </div>
          </div>

          <div class="alert alert-secondary mb-0">
            {{ roleDescription }}
          </div>
        </section>
      </div>
    </div>
  </main>
</template>
