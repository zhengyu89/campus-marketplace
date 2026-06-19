<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ListingImage from '../components/ListingImage.vue'
import ListingStatusBadge from '../components/ListingStatusBadge.vue'
import { useListingsStore } from '../stores/listings'

const router = useRouter()
const listingsStore = useListingsStore()
const actionError = ref('')
const busyListingId = ref(null)

const formatPrice = (price) =>
  new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(Number(price) || 0)

async function loadListings() {
  try {
    await listingsStore.fetchMyListings()
  } catch {
    // The store exposes the request error to the page.
  }
}

async function updateStatus(listing, listingStatus) {
  busyListingId.value = listing.listing_id
  actionError.value = ''

  try {
    await listingsStore.updateListing(listing.listing_id, {
      listing_status: listingStatus,
    })
  } catch (error) {
    actionError.value = error.response?.data?.message ?? 'Unable to update the listing status.'
  } finally {
    busyListingId.value = null
  }
}

async function deleteListing(listing) {
  const confirmed = window.confirm(
    `Delete “${listing.title}”? This cannot be undone.`
  )

  if (!confirmed) {
    return
  }

  busyListingId.value = listing.listing_id
  actionError.value = ''

  try {
    await listingsStore.deleteListing(listing.listing_id)
  } catch (error) {
    actionError.value = error.response?.data?.message ?? 'Unable to delete this listing.'
  } finally {
    busyListingId.value = null
  }
}

onMounted(loadListings)
</script>

<template>
  <main class="my-listings-page">
    <div class="container-xl py-5">
      <div class="page-heading">
        <div>
          <span class="market-kicker">Seller dashboard</span>
          <h1>My listings</h1>
          <p>Review your items, update availability, or edit listing details.</p>
        </div>
        <RouterLink class="btn btn-primary btn-lg" :to="{ name: 'listing-create' }">
          Create listing
        </RouterLink>
      </div>

      <div v-if="actionError" class="alert alert-danger">{{ actionError }}</div>

      <div v-if="listingsStore.isLoadingMine" class="list-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading your listings</span>
        </div>
      </div>

      <div v-else-if="listingsStore.error" class="alert alert-danger">
        {{ listingsStore.error }}
      </div>

      <div v-else-if="listingsStore.myListings.length" class="d-grid gap-3">
        <article
          v-for="listing in listingsStore.myListings"
          :key="listing.listing_id"
          class="management-card"
        >
          <ListingImage
            class="management-image"
            :src="listing.image_url"
            :alt="listing.title"
            ratio="4 / 3"
          />

          <div class="management-copy">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <ListingStatusBadge :status="listing.listing_status" />
              <span class="condition-label">{{ listing.condition_status }}</span>
              <span class="category-label">{{ listing.category_name }}</span>
            </div>
            <h2>{{ listing.title }}</h2>
            <strong>{{ formatPrice(listing.price) }}</strong>
            <p>{{ listing.description }}</p>
          </div>

          <div class="management-actions">
            <label class="form-label" :for="`status-${listing.listing_id}`">Status</label>
            <select
              :id="`status-${listing.listing_id}`"
              class="form-select"
              :value="listing.listing_status"
              :disabled="busyListingId === listing.listing_id"
              @change="updateStatus(listing, $event.target.value)"
            >
              <option>Available</option>
              <option>Reserved</option>
              <option>Sold</option>
            </select>
            <button
              class="btn btn-outline-primary"
              type="button"
              @click="router.push({ name: 'listing-edit', params: { id: listing.listing_id } })"
            >
              Edit details
            </button>
            <button
              class="btn btn-outline-danger"
              type="button"
              :disabled="busyListingId === listing.listing_id"
              @click="deleteListing(listing)"
            >
              Delete
            </button>
          </div>
        </article>
      </div>

      <div v-else class="list-state">
        <span class="empty-icon" aria-hidden="true">＋</span>
        <h2>You have not listed anything yet</h2>
        <p>Create your first listing and make it visible to the UTM community.</p>
        <RouterLink class="btn btn-primary" :to="{ name: 'listing-create' }">
          Create my first listing
        </RouterLink>
      </div>
    </div>
  </main>
</template>

<style scoped>
.my-listings-page {
  min-height: 75vh;
}

.page-heading {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 2rem;
}

.page-heading h1 {
  margin: 0.7rem 0 0.4rem;
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.page-heading p,
.list-state p {
  color: var(--market-muted);
}

.management-card {
  display: grid;
  grid-template-columns: 13rem minmax(0, 1fr) 12rem;
  gap: 1.25rem;
  overflow: hidden;
  padding: 1rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow);
}

.management-image {
  border-radius: 0.8rem;
}

.management-copy {
  align-self: center;
}

.management-copy h2 {
  margin: 0.65rem 0 0.2rem;
  font-size: 1.3rem;
  font-weight: 750;
}

.management-copy strong {
  color: var(--market-primary);
  font-size: 1.25rem;
}

.management-copy p {
  display: -webkit-box;
  overflow: hidden;
  margin: 0.55rem 0 0;
  color: var(--market-muted);
  line-height: 1.6;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.condition-label,
.category-label {
  color: var(--market-muted);
  font-size: 0.78rem;
  font-weight: 700;
}

.management-actions {
  display: flex;
  align-self: center;
  flex-direction: column;
  gap: 0.65rem;
}

.management-actions .form-label {
  margin: 0;
  font-size: 0.78rem;
  font-weight: 750;
}

.list-state {
  display: grid;
  min-height: 22rem;
  place-items: center;
  align-content: center;
  gap: 0.9rem;
  padding: 2rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  text-align: center;
  box-shadow: var(--market-shadow);
}

.empty-icon {
  color: var(--market-primary);
  font-size: 3rem;
}

@media (max-width: 991.98px) {
  .management-card {
    grid-template-columns: 10rem minmax(0, 1fr);
  }

  .management-actions {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
  }
}

@media (max-width: 575.98px) {
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .management-card {
    grid-template-columns: 1fr;
  }

  .management-actions {
    grid-column: auto;
    grid-template-columns: 1fr;
  }
}
</style>
