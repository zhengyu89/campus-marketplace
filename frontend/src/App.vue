<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const searchQuery = ref('')
const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.user?.role === 'admin')

watch(
  () => [route.name, route.query.q],
  ([routeName, query]) => {
    searchQuery.value = routeName === 'market' && typeof query === 'string' ? query : ''
  },
  { immediate: true }
)

async function search() {
  const q = searchQuery.value.trim()
  await router.push({
    name: 'market',
    query: q ? { q } : {},
  })
}

async function logout() {
  authStore.logout()
  await router.push({ name: 'home' })
}
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <nav class="navbar navbar-expand-xl container-xl py-3">
        <RouterLink class="navbar-brand" :to="{ name: 'home' }">UTM MarketHub</RouterLink>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#marketNavigation"
          aria-controls="marketNavigation"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon" />
        </button>

        <div id="marketNavigation" class="collapse navbar-collapse gap-xl-4">
          <form class="app-search order-xl-1" role="search" @submit.prevent="search">
            <span aria-hidden="true">⌕</span>
            <input
              v-model="searchQuery"
              type="search"
              aria-label="Search marketplace"
              placeholder="Search for items..."
            />
            <button type="submit">Search</button>
          </form>

          <ul class="navbar-nav align-items-xl-center gap-xl-2 order-xl-0">
            <li class="nav-item">
              <RouterLink class="nav-link" :to="{ name: 'market' }">Browse</RouterLink>
            </li>
            <li v-if="isAuthenticated" class="nav-item">
              <RouterLink class="nav-link" :to="{ name: 'my-listings' }">My listings</RouterLink>
            </li>
            <li v-if="isAuthenticated" class="nav-item">
              <RouterLink class="nav-link" :to="{ name: 'listing-create' }">Sell</RouterLink>
            </li>
            <li v-if="isAdmin" class="nav-item">
              <RouterLink class="nav-link" :to="{ name: 'admin-categories' }">
                Categories
              </RouterLink>
            </li>
          </ul>

          <div class="account-actions order-xl-2 ms-xl-auto">
            <template v-if="isAuthenticated">
              <RouterLink class="user-link" :to="{ name: 'account' }">
                {{ authStore.user?.name }}
              </RouterLink>
              <button class="btn btn-outline-primary btn-sm" type="button" @click="logout">
                Logout
              </button>
            </template>
            <template v-else>
              <RouterLink class="btn btn-link btn-sm" :to="{ name: 'login' }">Login</RouterLink>
              <RouterLink class="btn btn-primary btn-sm" :to="{ name: 'register' }">
                Register
              </RouterLink>
            </template>
          </div>
        </div>
      </nav>
    </header>

    <RouterView />
  </div>
</template>

<style scoped>
.app-shell {
  min-height: 100vh;
  background: var(--market-surface);
}

.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid rgba(194, 198, 216, 0.75);
  background: rgba(248, 249, 255, 0.94);
  box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
  backdrop-filter: blur(18px);
}

.navbar-brand {
  color: var(--market-primary);
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.025em;
}

.navbar-brand:hover {
  color: var(--market-primary-dark);
}

.navbar-collapse {
  min-width: 0;
}

.app-search {
  display: flex;
  flex: 1 1 22rem;
  align-items: center;
  max-width: 31rem;
  gap: 0.6rem;
  padding: 0.45rem 0.5rem 0.45rem 0.8rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.7rem;
  background: var(--market-surface-low);
}

.app-search:focus-within {
  border-color: var(--market-primary);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}

.app-search input {
  min-width: 0;
  flex: 1;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--market-text);
}

.app-search button {
  border: 0;
  border-radius: 0.5rem;
  padding: 0.45rem 0.8rem;
  background: var(--market-primary);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 750;
}

.nav-link {
  color: var(--market-muted);
  font-size: 0.9rem;
  font-weight: 650;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--market-primary);
}

.account-actions {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.user-link {
  max-width: 10rem;
  overflow: hidden;
  color: var(--market-text);
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 1199.98px) {
  .navbar-collapse {
    padding-top: 1rem;
  }

  .app-search {
    max-width: none;
    margin-bottom: 1rem;
  }

  .account-actions {
    margin-top: 1rem;
  }
}
</style>
