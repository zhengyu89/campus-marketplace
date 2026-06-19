<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ListingCard from '../components/ListingCard.vue'
import ListingFilters from '../components/ListingFilters.vue'
import { useListingsStore } from '../stores/listings'

const route = useRoute()
const router = useRouter()
const listingsStore = useListingsStore()

const filters = computed(() => ({
  q: typeof route.query.q === 'string' ? route.query.q : '',
  category_id: typeof route.query.category_id === 'string' ? route.query.category_id : '',
  condition: typeof route.query.condition === 'string' ? route.query.condition : '',
  status: typeof route.query.status === 'string' ? route.query.status : '',
  sort: typeof route.query.sort === 'string' ? route.query.sort : 'newest',
}))

const apiFilters = computed(() =>
  Object.fromEntries(
    Object.entries(filters.value).filter(([, value]) => value !== '' && value !== 'newest')
  )
)

const pageTitle = computed(() =>
  filters.value.q ? `Results for “${filters.value.q}”` : 'Browse campus listings'
)

async function loadListings() {
  try {
    await listingsStore.fetchListings(apiFilters.value)
  } catch {
    // The store exposes the request error to the page.
  }
}

function updateRoute(nextFilters) {
  const query = Object.fromEntries(
    Object.entries(nextFilters).filter(([, value]) => value !== '' && value !== 'newest')
  )
  router.push({ name: 'market', query })
}

function resetFilters() {
  router.push({ name: 'market' })
}

onMounted(() => {
  listingsStore.fetchCategories()
})

watch(() => route.fullPath, loadListings, { immediate: true })
</script>

<template>
  <main class="market-page">
    <section class="market-hero">
      <div class="container-xl">
        <span class="market-kicker">UTM student marketplace</span>
        <div class="hero-row">
          <div>
            <h1>{{ pageTitle }}</h1>
            <p>Search, filter, and compare second-hand items listed by the UTM community.</p>
          </div>
          <RouterLink class="btn btn-primary btn-lg" :to="{ name: 'listing-create' }">
            Sell an item
          </RouterLink>
        </div>
      </div>
    </section>

    <section class="pb-5">
      <div class="container-xl">
        <ListingFilters
          :categories="listingsStore.categories"
          :filters="filters"
          @apply="updateRoute"
          @reset="resetFilters"
        />

        <div class="results-heading">
          <div>
            <h2>Marketplace results</h2>
            <p v-if="!listingsStore.isLoading">
              {{ listingsStore.listings.length }}
              {{ listingsStore.listings.length === 1 ? 'listing' : 'listings' }} found
            </p>
          </div>
        </div>

        <div v-if="listingsStore.isLoading" class="market-state">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading listings</span>
          </div>
          <p>Loading marketplace listings...</p>
        </div>

        <div v-else-if="listingsStore.error" class="alert alert-danger">
          {{ listingsStore.error }}
        </div>

        <div v-else-if="listingsStore.listings.length" class="row g-4">
          <div
            v-for="listing in listingsStore.listings"
            :key="listing.listing_id"
            class="col-sm-6 col-lg-4 col-xl-3"
          >
            <ListingCard :listing="listing" />
          </div>
        </div>

        <div v-else class="market-state empty-state">
          <span class="empty-icon" aria-hidden="true">⌕</span>
          <h2>No listings matched these filters</h2>
          <p>Try a broader keyword or clear one of the selected filters.</p>
          <button class="btn btn-outline-primary" type="button" @click="resetFilters">
            Clear all filters
          </button>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.market-page {
  min-height: 75vh;
}

.market-hero {
  padding: 4rem 0 2rem;
}

.hero-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 2rem;
}

.market-hero h1 {
  margin: 0.7rem 0 0.55rem;
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.market-hero p,
.results-heading p,
.market-state p {
  color: var(--market-muted);
}

.results-heading {
  display: flex;
  align-items: end;
  justify-content: space-between;
  margin: 2.5rem 0 1.25rem;
}

.results-heading h2 {
  margin: 0;
  font-size: 1.65rem;
  font-weight: 750;
}

.market-state {
  display: grid;
  min-height: 18rem;
  place-items: center;
  align-content: center;
  gap: 0.9rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  text-align: center;
  box-shadow: var(--market-shadow);
}

.empty-state {
  padding: 2rem;
}

.empty-state h2 {
  margin: 0;
  font-size: 1.45rem;
  font-weight: 750;
}

.empty-icon {
  color: var(--market-primary);
  font-size: 3rem;
}

@media (max-width: 767.98px) {
  .market-hero {
    padding-top: 2.5rem;
  }

  .hero-row {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
