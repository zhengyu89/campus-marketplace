<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ListingImage from '../components/ListingImage.vue'
import ListingStatusBadge from '../components/ListingStatusBadge.vue'
import { useListingsStore } from '../stores/listings'
import { useOffersStore } from '../stores/offers'

const router = useRouter()
const listingsStore = useListingsStore()
const offersStore = useOffersStore()
const actionError = ref('')
const busyListingId = ref(null)
const busyOfferId = ref(null)

const listings = computed(() => listingsStore.myListings)
const receivedOffers = computed(() => offersStore.receivedOffers)
const isLoadingDashboard = computed(
  () => listingsStore.isLoadingMine || offersStore.isLoadingReceived
)
const availableListings = computed(() =>
  listings.value.filter((listing) => listing.listing_status === 'Available')
)
const reservedListings = computed(() =>
  listings.value.filter((listing) => listing.listing_status === 'Reserved')
)
const soldListings = computed(() =>
  listings.value.filter((listing) => listing.listing_status === 'Sold')
)
const pendingOffers = computed(() =>
  receivedOffers.value.filter((offer) => offer.offer_status === 'Pending')
)
const recentOffers = computed(() => receivedOffers.value.slice(0, 4))
const inventoryValue = computed(() =>
  listings.value.reduce((total, listing) => total + Number(listing.price || 0), 0)
)

const formatPrice = (price) =>
  new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(Number(price) || 0)

const formatDate = (value) =>
  new Intl.DateTimeFormat('en-MY', {
    dateStyle: 'medium',
  }).format(new Date(value))

function offerStatusClass(status) {
  return `offer-status offer-status-${String(status).toLowerCase()}`
}

async function loadDashboard() {
  actionError.value = ''

  try {
    await Promise.all([
      listingsStore.fetchMyListings(),
      offersStore.fetchReceivedOffers(),
    ])
  } catch (error) {
    actionError.value =
      error.response?.data?.message ?? 'Unable to load your seller dashboard.'
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
    `Delete "${listing.title}"? This cannot be undone.`
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

async function runOfferAction(offer, action) {
  busyOfferId.value = offer.offer_id
  actionError.value = ''

  try {
    if (action === 'accept') {
      await offersStore.acceptOffer(offer.offer_id)
      await Promise.all([
        listingsStore.fetchMyListings(),
        offersStore.fetchReceivedOffers(),
      ])
    } else {
      await offersStore.rejectOffer(offer.offer_id)
    }
  } catch (error) {
    actionError.value = error.response?.data?.message ?? 'Unable to update this offer.'
  } finally {
    busyOfferId.value = null
  }
}

onMounted(loadDashboard)
</script>

<template>
  <main class="seller-page">
    <div class="container-xl py-5">
      <section class="seller-hero">
        <div>
          <span class="market-kicker">Seller dashboard</span>
          <h1>Manage your marketplace</h1>
          <p>Track listings, review buyer offers, and keep your items moving.</p>
        </div>
        <div class="hero-actions">
          <RouterLink class="btn btn-primary btn-lg" :to="{ name: 'listing-create' }">
            Create listing
          </RouterLink>
          <RouterLink class="btn btn-outline-primary btn-lg" :to="{ name: 'offers' }">
            View offers
          </RouterLink>
        </div>
      </section>

      <div v-if="actionError" class="alert alert-danger">{{ actionError }}</div>

      <section class="metric-grid" aria-label="Seller summary">
        <article class="metric-card">
          <small>Total listings</small>
          <strong>{{ listings.length }}</strong>
          <span>{{ availableListings.length }} available</span>
        </article>
        <article class="metric-card">
          <small>Pending offers</small>
          <strong>{{ pendingOffers.length }}</strong>
          <span>{{ receivedOffers.length }} total received</span>
        </article>
        <article class="metric-card">
          <small>Reserved / sold</small>
          <strong>{{ reservedListings.length }} / {{ soldListings.length }}</strong>
          <span>Items progressing</span>
        </article>
        <article class="metric-card">
          <small>Inventory value</small>
          <strong>{{ formatPrice(inventoryValue) }}</strong>
          <span>Listed asking price</span>
        </article>
      </section>

      <div v-if="isLoadingDashboard" class="dashboard-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading seller dashboard</span>
        </div>
      </div>

      <div v-else class="dashboard-grid">
        <section class="dashboard-panel listings-panel">
          <div class="panel-heading">
            <div>
              <span class="panel-label">Inventory</span>
              <h2>My listings</h2>
            </div>
            <RouterLink class="btn btn-sm btn-outline-primary" :to="{ name: 'listing-create' }">
              Add item
            </RouterLink>
          </div>

          <div v-if="listingsStore.error" class="alert alert-danger">
            {{ listingsStore.error }}
          </div>

          <div v-else-if="listings.length" class="listing-stack">
            <article
              v-for="listing in listings"
              :key="listing.listing_id"
              class="listing-row"
            >
              <ListingImage
                class="listing-image"
                :src="listing.image_url"
                :alt="listing.title"
                ratio="4 / 3"
              />

              <div class="listing-copy">
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <ListingStatusBadge :status="listing.listing_status" />
                  <span class="meta-chip">{{ listing.condition_status }}</span>
                  <span class="meta-chip">{{ listing.category_name }}</span>
                </div>
                <h3>{{ listing.title }}</h3>
                <strong>{{ formatPrice(listing.price) }}</strong>
                <p>{{ listing.description }}</p>
              </div>

              <div class="listing-actions">
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
                  Edit
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

          <div v-else class="dashboard-state compact">
            <span class="empty-icon" aria-hidden="true">+</span>
            <h3>You have not listed anything yet</h3>
            <p>Create your first listing and make it visible to the UTM community.</p>
            <RouterLink class="btn btn-primary" :to="{ name: 'listing-create' }">
              Create my first listing
            </RouterLink>
          </div>
        </section>

        <aside class="dashboard-panel offers-panel">
          <div class="panel-heading">
            <div>
              <span class="panel-label">Buyer activity</span>
              <h2>Recent offers</h2>
            </div>
            <RouterLink class="btn btn-sm btn-outline-primary" :to="{ name: 'offers' }">
              Open all
            </RouterLink>
          </div>

          <div v-if="offersStore.error" class="alert alert-danger">
            {{ offersStore.error }}
          </div>

          <div v-else-if="recentOffers.length" class="offer-stack">
            <article v-for="offer in recentOffers" :key="offer.offer_id" class="offer-row">
              <div class="offer-row-top">
                <span :class="offerStatusClass(offer.offer_status)">
                  {{ offer.offer_status }}
                </span>
                <small>{{ formatDate(offer.created_at) }}</small>
              </div>
              <h3>{{ offer.listing.title }}</h3>
              <p>
                {{ offer.buyer.name }} offered
                <strong>{{ formatPrice(offer.offer_price) }}</strong>
              </p>
              <blockquote v-if="offer.message">{{ offer.message }}</blockquote>
              <div class="offer-actions">
                <RouterLink
                  class="btn btn-sm btn-outline-primary"
                  :to="{ name: 'listing-detail', params: { id: offer.listing_id } }"
                >
                  Listing
                </RouterLink>
                <button
                  class="btn btn-sm btn-primary"
                  type="button"
                  :disabled="offer.offer_status !== 'Pending' || busyOfferId === offer.offer_id"
                  @click="runOfferAction(offer, 'accept')"
                >
                  Accept
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  type="button"
                  :disabled="offer.offer_status !== 'Pending' || busyOfferId === offer.offer_id"
                  @click="runOfferAction(offer, 'reject')"
                >
                  Reject
                </button>
              </div>
            </article>
          </div>

          <div v-else class="empty-offers">
            <h3>No offers yet</h3>
            <p>Offers from buyers will appear here once your listings get interest.</p>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<style scoped>
.seller-page {
  min-height: 75vh;
}

.seller-hero {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 1.4rem;
}

.seller-hero h1 {
  margin: 0.7rem 0 0.4rem;
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.seller-hero p,
.metric-card span,
.dashboard-state p,
.listing-copy p,
.offer-row p,
.empty-offers p,
.offer-row-top small {
  color: var(--market-muted);
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.7rem;
  justify-content: flex-end;
}

.metric-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.metric-card,
.dashboard-panel,
.dashboard-state {
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow);
}

.metric-card {
  padding: 1.15rem;
}

.metric-card small,
.panel-label,
.meta-chip {
  color: var(--market-muted);
  font-size: 0.76rem;
  font-weight: 750;
}

.metric-card strong {
  display: block;
  margin: 0.3rem 0 0.1rem;
  color: var(--market-text);
  font-size: 1.8rem;
  line-height: 1.1;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(20rem, 0.42fr);
  gap: 1.25rem;
  align-items: start;
}

.dashboard-panel {
  padding: 1rem;
}

.panel-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.panel-heading h2 {
  margin: 0.2rem 0 0;
  font-size: 1.25rem;
  font-weight: 800;
}

.listing-stack,
.offer-stack {
  display: grid;
  gap: 0.85rem;
}

.listing-row {
  display: grid;
  grid-template-columns: 9.5rem minmax(0, 1fr) 10.5rem;
  gap: 1rem;
  padding: 0.85rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.85rem;
  background: var(--market-surface);
}

.listing-image {
  border-radius: 0.65rem;
}

.listing-copy {
  align-self: center;
  min-width: 0;
}

.listing-copy h3,
.offer-row h3,
.empty-offers h3,
.dashboard-state h3 {
  margin: 0.55rem 0 0.2rem;
  font-size: 1.05rem;
  font-weight: 800;
}

.listing-copy strong {
  color: var(--market-primary);
  font-size: 1.1rem;
}

.listing-copy p {
  display: -webkit-box;
  overflow: hidden;
  margin: 0.4rem 0 0;
  line-height: 1.55;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.meta-chip {
  display: inline-flex;
}

.listing-actions {
  display: flex;
  align-self: center;
  flex-direction: column;
  gap: 0.55rem;
}

.listing-actions .form-label {
  margin: 0;
  font-size: 0.76rem;
  font-weight: 750;
}

.offers-panel {
  position: sticky;
  top: 6.5rem;
}

.offer-row {
  padding: 0.9rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.85rem;
  background: var(--market-surface);
}

.offer-row-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.offer-status {
  display: inline-flex;
  border-radius: 999px;
  padding: 0.28rem 0.55rem;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
}

.offer-status-pending {
  background: #fff3cd;
  color: #8a5b00;
}

.offer-status-accepted {
  background: #d1e7dd;
  color: #0f5132;
}

.offer-status-rejected,
.offer-status-cancelled {
  background: #f8d7da;
  color: #842029;
}

.offer-row p {
  margin: 0;
  line-height: 1.55;
}

.offer-row strong {
  color: var(--market-primary);
}

.offer-row blockquote {
  margin: 0.65rem 0 0;
  padding-left: 0.75rem;
  border-left: 3px solid var(--market-primary-fixed);
  color: var(--market-text);
}

.offer-actions {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.5rem;
  margin-top: 0.8rem;
}

.dashboard-state {
  display: grid;
  min-height: 20rem;
  place-items: center;
  align-content: center;
  gap: 0.9rem;
  padding: 2rem;
  text-align: center;
}

.dashboard-state.compact {
  min-height: 18rem;
  border-style: dashed;
  background: var(--market-surface);
  box-shadow: none;
}

.empty-icon {
  color: var(--market-primary);
  font-size: 3rem;
  line-height: 1;
}

.empty-offers {
  padding: 1.25rem;
  border: 1px dashed var(--market-outline);
  border-radius: 0.85rem;
  background: var(--market-surface);
}

@media (max-width: 1199.98px) {
  .metric-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .offers-panel {
    position: static;
  }
}

@media (max-width: 767.98px) {
  .seller-hero {
    align-items: flex-start;
    flex-direction: column;
  }

  .hero-actions {
    width: 100%;
  }

  .hero-actions .btn {
    flex: 1 1 12rem;
  }

  .listing-row {
    grid-template-columns: 8rem minmax(0, 1fr);
  }

  .listing-actions {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
  }
}

@media (max-width: 575.98px) {
  .metric-grid,
  .listing-row,
  .listing-actions,
  .offer-actions {
    grid-template-columns: 1fr;
  }

  .listing-actions {
    grid-column: auto;
  }
}
</style>
