<script setup>
import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import ListingImage from '../components/ListingImage.vue'
import ListingStatusBadge from '../components/ListingStatusBadge.vue'
import { useAuthStore } from '../stores/auth'
import { useListingsStore } from '../stores/listings'

const route = useRoute()
const authStore = useAuthStore()
const listingsStore = useListingsStore()

const listing = computed(() => listingsStore.currentListing)
const isOwner = computed(
  () =>
    authStore.isAuthenticated &&
    Number(authStore.user?.user_id) === Number(listing.value?.seller_id)
)

const formatPrice = (price) =>
  new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(Number(price) || 0)

const formatDate = (value) =>
  new Intl.DateTimeFormat('en-MY', {
    dateStyle: 'long',
  }).format(new Date(value))

async function loadListing() {
  try {
    await listingsStore.fetchListing(route.params.id)
  } catch {
    // The store exposes the request error to the page.
  }
}

watch(() => route.params.id, loadListing, { immediate: true })
</script>

<template>
  <main class="detail-page">
    <div class="container-xl py-5">
      <RouterLink class="back-link" :to="{ name: 'market' }">← Back to marketplace</RouterLink>

      <div v-if="listingsStore.isLoadingCurrent" class="detail-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading listing</span>
        </div>
      </div>

      <div v-else-if="listingsStore.error" class="detail-state">
        <h1>Listing unavailable</h1>
        <p>{{ listingsStore.error }}</p>
        <RouterLink class="btn btn-primary" :to="{ name: 'market' }">
          Browse other listings
        </RouterLink>
      </div>

      <div v-else-if="listing" class="row g-4 g-xl-5 mt-1">
        <div class="col-lg-7">
          <div class="image-card">
            <ListingImage
              :src="listing.image_url"
              :alt="listing.title"
              ratio="4 / 3"
              fit="contain"
            />
          </div>
        </div>

        <div class="col-lg-5">
          <section class="detail-card">
            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="condition-chip">{{ listing.condition_status }}</span>
              <ListingStatusBadge :status="listing.listing_status" />
            </div>

            <span class="category-label">{{ listing.category_name }}</span>
            <h1>{{ listing.title }}</h1>
            <strong class="detail-price">{{ formatPrice(listing.price) }}</strong>

            <div class="seller-card">
              <span class="seller-avatar">{{ listing.seller?.name?.charAt(0) }}</span>
              <div>
                <small>Listed by</small>
                <strong>{{ listing.seller?.name }}</strong>
              </div>
            </div>

            <dl class="listing-facts">
              <div>
                <dt>Condition</dt>
                <dd>{{ listing.condition_status }}</dd>
              </div>
              <div>
                <dt>Posted</dt>
                <dd>{{ formatDate(listing.created_at) }}</dd>
              </div>
            </dl>

            <RouterLink
              v-if="isOwner"
              class="btn btn-primary btn-lg w-100"
              :to="{ name: 'listing-edit', params: { id: listing.listing_id } }"
            >
              Edit my listing
            </RouterLink>

            <div v-else class="offer-placeholder">
              <strong>Interested in this item?</strong>
              <p>
                The offer action will be enabled when the team’s Offer Management module is
                integrated.
              </p>
            </div>
          </section>
        </div>

        <div class="col-12">
          <section class="description-card">
            <span class="market-kicker">Item details</span>
            <h2>Description</h2>
            <p>{{ listing.description }}</p>
          </section>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.detail-page {
  min-height: 75vh;
}

.back-link {
  color: var(--market-primary);
  font-weight: 700;
  text-decoration: none;
}

.image-card,
.detail-card,
.description-card,
.detail-state {
  overflow: hidden;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow);
}

.detail-card,
.description-card {
  padding: clamp(1.25rem, 3vw, 2rem);
}

.detail-card {
  position: sticky;
  top: 7rem;
}

.category-label {
  color: var(--market-primary);
  font-size: 0.82rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.detail-card h1 {
  margin: 0.7rem 0;
  font-size: clamp(2rem, 4vw, 2.8rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.detail-price {
  display: block;
  color: var(--market-primary);
  font-size: 2rem;
}

.condition-chip {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.35rem 0.65rem;
  background: var(--market-primary-fixed);
  color: var(--market-primary-dark);
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
}

.seller-card {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin: 1.5rem 0;
  padding: 1rem;
  border-radius: 0.8rem;
  background: var(--market-surface-low);
}

.seller-avatar {
  display: grid;
  width: 2.8rem;
  height: 2.8rem;
  place-items: center;
  border-radius: 999px;
  background: var(--market-primary);
  color: #fff;
  font-weight: 800;
  text-transform: uppercase;
}

.seller-card small,
.seller-card strong {
  display: block;
}

.seller-card small {
  color: var(--market-muted);
}

.listing-facts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin: 0 0 1.5rem;
}

.listing-facts div {
  padding: 0.9rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.7rem;
}

.listing-facts dt {
  color: var(--market-muted);
  font-size: 0.75rem;
}

.listing-facts dd {
  margin: 0.2rem 0 0;
  font-weight: 700;
}

.offer-placeholder {
  padding: 1rem;
  border: 1px dashed rgba(13, 110, 253, 0.35);
  border-radius: 0.8rem;
  background: rgba(13, 110, 253, 0.05);
}

.offer-placeholder p {
  margin: 0.35rem 0 0;
  color: var(--market-muted);
  font-size: 0.88rem;
  line-height: 1.55;
}

.description-card h2 {
  margin: 0.7rem 0;
  font-weight: 750;
}

.description-card p {
  margin: 0;
  color: var(--market-muted);
  line-height: 1.8;
  white-space: pre-wrap;
}

.detail-state {
  display: grid;
  min-height: 24rem;
  margin-top: 1.5rem;
  place-items: center;
  align-content: center;
  gap: 1rem;
  padding: 2rem;
  text-align: center;
}

.detail-state p {
  color: var(--market-muted);
}
</style>
