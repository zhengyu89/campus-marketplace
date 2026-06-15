<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.user?.role === 'admin')
const primaryActionRoute = computed(() => (isAuthenticated.value ? { name: 'account' } : { name: 'register' }))
const searchQuery = ref('')

watch(
  () => [route.name, route.query.q, route.query.search],
  ([routeName, queryValue, pendingSearch]) => {
    const nextValue =
      typeof queryValue === 'string'
        ? queryValue
        : typeof pendingSearch === 'string' && routeName === 'login'
          ? pendingSearch
          : ''

    searchQuery.value = nextValue
  },
  { immediate: true }
)

async function handleLogout() {
  authStore.logout()
  await router.push({ name: 'login' })
}

async function handleSearch() {
  const trimmedQuery = searchQuery.value.trim()
  const marketRoute = {
    name: 'market',
    query: trimmedQuery ? { q: trimmedQuery } : {},
  }

  if (isAuthenticated.value) {
    await router.push(marketRoute)
    return
  }

  const loginQuery = {
    redirect: router.resolve(marketRoute).fullPath,
  }

  if (trimmedQuery !== '') {
    loginQuery.search = trimmedQuery
  }

  await router.push({
    name: 'login',
    query: loginQuery,
  })
}
</script>

<template>
  <div class="app-shell min-vh-100">
    <header class="app-header">
      <nav class="container-xl d-flex flex-wrap align-items-center gap-3 py-3 py-lg-4">
        <RouterLink class="app-brand mb-0" :to="{ name: 'home' }">
          UTM MarketHub
        </RouterLink>

        <form class="app-search d-none d-lg-flex align-items-center flex-grow-1" @submit.prevent="handleSearch">
          <span class="app-search-icon" aria-hidden="true">⌕</span>
          <input
            v-model="searchQuery"
            class="app-search-input"
            type="text"
            placeholder="Search textbooks, gadgets, dorm essentials"
          />
          <button class="app-search-button" type="submit">
            Search
          </button>
        </form>

        <div class="app-links d-none d-md-flex align-items-center">
          <RouterLink class="app-link" :to="{ name: 'home', hash: '#recently-added' }">
            Browse
          </RouterLink>
          <RouterLink class="app-link" :to="{ name: 'home', hash: '#market-categories' }">
            Categories
          </RouterLink>
          <RouterLink class="app-link" :to="{ name: 'home', hash: '#why-markethub' }">
            Why UTM MarketHub
          </RouterLink>
          <RouterLink v-if="isAdmin" class="app-link" :to="{ name: 'admin-categories' }">
            Admin Categories
          </RouterLink>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 ms-md-auto">
          <template v-if="isAuthenticated">
            <span class="app-user-pill">
              {{ authStore.user?.name }} · {{ authStore.user?.role }}
            </span>
            <RouterLink class="btn btn-outline-light app-outline-button btn-sm" :to="{ name: 'account' }">
              Account
            </RouterLink>
            <RouterLink
              v-if="isAdmin"
              class="btn btn-outline-light app-outline-button btn-sm"
              :to="{ name: 'admin-categories' }"
            >
              Categories
            </RouterLink>
            <button class="btn app-primary-button btn-sm" type="button" @click="handleLogout">
              Logout
            </button>
          </template>
          <template v-else>
            <RouterLink class="btn btn-link app-text-button btn-sm" :to="{ name: 'login' }">
              Login
            </RouterLink>
            <RouterLink class="btn app-primary-button btn-sm" :to="primaryActionRoute">
              Register
            </RouterLink>
          </template>
        </div>
      </nav>
    </header>

    <router-view />
  </div>
</template>

<style scoped>
.app-shell {
  background:
    radial-gradient(circle at top left, rgba(35, 107, 246, 0.18), transparent 28%),
    radial-gradient(circle at top right, rgba(255, 158, 27, 0.14), transparent 24%),
    #f4f8ff;
  color: #0f2744;
}

.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  backdrop-filter: blur(18px);
  background: rgba(248, 251, 255, 0.84);
  border-bottom: 1px solid rgba(113, 132, 168, 0.18);
}

.app-brand {
  color: #0d5bd7;
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  text-decoration: none;
}

.app-search {
  min-width: 18rem;
  max-width: 30rem;
  gap: 0.75rem;
  padding: 0.7rem 0.95rem;
  border: 1px solid rgba(123, 143, 178, 0.25);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 14px 40px rgba(10, 37, 64, 0.08);
}

.app-search-icon {
  color: #6b7d96;
  font-size: 0.95rem;
}

.app-search-input {
  width: 100%;
  border: 0;
  background: transparent;
  color: #18314d;
  outline: none;
}

.app-search-input::placeholder {
  color: #7787a1;
}

.app-search-button {
  border: 0;
  border-radius: 999px;
  padding: 0.45rem 0.9rem;
  background: #0d5bd7;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
}

.app-links {
  gap: 1.25rem;
}

.app-link {
  color: #47617f;
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}

.app-link:hover,
.app-link.router-link-active {
  color: #0d5bd7;
}

.app-user-pill {
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: rgba(13, 91, 215, 0.1);
  color: #36516f;
  font-size: 0.82rem;
  font-weight: 600;
}

.app-primary-button {
  border: 0;
  border-radius: 999px;
  padding-inline: 1rem;
  background: linear-gradient(135deg, #0d5bd7, #2f83ff);
  color: #fff;
  font-weight: 700;
  box-shadow: 0 14px 30px rgba(13, 91, 215, 0.22);
}

.app-outline-button {
  border-radius: 999px;
  border-color: rgba(13, 91, 215, 0.4);
  color: #0d5bd7;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.92);
}

.app-outline-button:hover {
  color: #0d5bd7;
  border-color: #0d5bd7;
  background: rgba(255, 255, 255, 1);
}

.app-text-button {
  color: #0d5bd7;
  font-weight: 700;
  text-decoration: none;
}

.app-text-button:hover {
  color: #0846a8;
}

@media (max-width: 991.98px) {
  .app-brand {
    width: 100%;
  }
}
</style>
