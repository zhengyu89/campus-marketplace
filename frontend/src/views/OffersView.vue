<script setup>
import { computed, onMounted, ref } from 'vue'
import ListingImage from '../components/ListingImage.vue'
import { useOffersStore } from '../stores/offers'

const offersStore = useOffersStore()
const activeTab = ref('received')
const actionError = ref('')
const busyOfferId = ref(null)

const sentOffers = computed(() => offersStore.sentOffers)
const receivedOffers = computed(() => offersStore.receivedOffers)
const isLoading = computed(() => offersStore.isLoadingSent || offersStore.isLoadingReceived)

const formatPrice = (price) =>
  new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(Number(price) || 0)

const formatDate = (value) =>
  new Intl.DateTimeFormat('en-MY', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))

function statusClass(status) {
  return `offer-status offer-status-${String(status).toLowerCase()}`
}

async function loadOffers() {
  actionError.value = ''

  try {
    await Promise.all([
      offersStore.fetchSentOffers(),
      offersStore.fetchReceivedOffers(),
    ])
  } catch {
    // The store exposes the request error to the page.
  }
}

async function runOfferAction(offer, action) {
  busyOfferId.value = offer.offer_id
  actionError.value = ''

  try {
    if (action === 'accept') {
      await offersStore.acceptOffer(offer.offer_id)
      await offersStore.fetchReceivedOffers()
    } else if (action === 'reject') {
      await offersStore.rejectOffer(offer.offer_id)
    } else if (action === 'delete') {
      await offersStore.deleteOffer(offer.offer_id)
    }
  } catch (error) {
    actionError.value = error.response?.data?.message ?? 'Unable to update this offer.'
  } finally {
    busyOfferId.value = null
  }
}

onMounted(loadOffers)
</script>

<template>
  <main class="offers-page">
    <div class="container-xl py-5">
      <div class="page-heading">
        <div>
          <span class="market-kicker">Offer management</span>
          <h1>Offers</h1>
          <p>Track offers you sent and respond to buyers interested in your listings.</p>
        </div>
      </div>

      <div class="offer-tabs" role="tablist" aria-label="Offer folders">
        <button
          class="offer-tab"
          :class="{ active: activeTab === 'received' }"
          type="button"
          @click="activeTab = 'received'"
        >
          Received
          <span>{{ receivedOffers.length }}</span>
        </button>
        <button
          class="offer-tab"
          :class="{ active: activeTab === 'sent' }"
          type="button"
          @click="activeTab = 'sent'"
        >
          Sent
          <span>{{ sentOffers.length }}</span>
        </button>
      </div>

      <div v-if="actionError" class="alert alert-danger">{{ actionError }}</div>
      <div v-else-if="offersStore.error" class="alert alert-danger">{{ offersStore.error }}</div>

      <div v-if="isLoading" class="offer-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading offers</span>
        </div>
      </div>

      <section v-else-if="activeTab === 'received'" class="d-grid gap-3">
        <article v-for="offer in receivedOffers" :key="offer.offer_id" class="offer-card">
          <ListingImage
            class="offer-image"
            :src="offer.listing.image_url"
            :alt="offer.listing.title"
            ratio="4 / 3"
          />

          <div class="offer-copy">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span :class="statusClass(offer.offer_status)">{{ offer.offer_status }}</span>
              <span class="offer-meta">{{ formatDate(offer.created_at) }}</span>
            </div>
            <h2>{{ offer.listing.title }}</h2>
            <p>
              {{ offer.buyer.name }} offered
              <strong>{{ formatPrice(offer.offer_price) }}</strong>
              for your listed price of {{ formatPrice(offer.listing.price) }}.
            </p>
            <blockquote v-if="offer.message">{{ offer.message }}</blockquote>
          </div>

          <div class="offer-actions">
            <RouterLink
              class="btn btn-outline-primary"
              :to="{ name: 'listing-detail', params: { id: offer.listing_id } }"
            >
              View listing
            </RouterLink>
            <button
              class="btn btn-primary"
              type="button"
              :disabled="offer.offer_status !== 'Pending' || busyOfferId === offer.offer_id"
              @click="runOfferAction(offer, 'accept')"
            >
              Accept
            </button>
            <button
              class="btn btn-outline-danger"
              type="button"
              :disabled="offer.offer_status !== 'Pending' || busyOfferId === offer.offer_id"
              @click="runOfferAction(offer, 'reject')"
            >
              Reject
            </button>
          </div>
        </article>

        <div v-if="receivedOffers.length === 0" class="offer-state">
          <h2>No received offers yet</h2>
          <p>When buyers make offers on your listings, they will appear here.</p>
          <RouterLink class="btn btn-primary" :to="{ name: 'my-listings' }">
            Manage listings
          </RouterLink>
        </div>
      </section>

      <section v-else class="d-grid gap-3">
        <article v-for="offer in sentOffers" :key="offer.offer_id" class="offer-card">
          <ListingImage
            class="offer-image"
            :src="offer.listing.image_url"
            :alt="offer.listing.title"
            ratio="4 / 3"
          />

          <div class="offer-copy">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span :class="statusClass(offer.offer_status)">{{ offer.offer_status }}</span>
              <span class="offer-meta">{{ formatDate(offer.created_at) }}</span>
            </div>
            <h2>{{ offer.listing.title }}</h2>
            <p>
              You offered <strong>{{ formatPrice(offer.offer_price) }}</strong> to
              {{ offer.seller.name }}.
            </p>
            <blockquote v-if="offer.message">{{ offer.message }}</blockquote>
          </div>

          <div class="offer-actions">
            <RouterLink
              class="btn btn-outline-primary"
              :to="{ name: 'listing-detail', params: { id: offer.listing_id } }"
            >
              View listing
            </RouterLink>
            <button
              class="btn btn-outline-danger"
              type="button"
              :disabled="offer.offer_status === 'Accepted' || busyOfferId === offer.offer_id"
              @click="runOfferAction(offer, 'delete')"
            >
              Delete
            </button>
          </div>
        </article>

        <div v-if="sentOffers.length === 0" class="offer-state">
          <h2>No sent offers yet</h2>
          <p>Browse the marketplace and make an offer on an item you like.</p>
          <RouterLink class="btn btn-primary" :to="{ name: 'market' }">
            Browse marketplace
          </RouterLink>
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.offers-page {
  min-height: 75vh;
}

.page-heading {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 1.4rem;
}

.page-heading h1 {
  margin: 0.7rem 0 0.4rem;
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.page-heading p,
.offer-state p,
.offer-copy p,
.offer-meta {
  color: var(--market-muted);
}

.offer-tabs {
  display: inline-flex;
  gap: 0.35rem;
  margin-bottom: 1.5rem;
  padding: 0.3rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.8rem;
  background: #fff;
}

.offer-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: 0;
  border-radius: 0.55rem;
  padding: 0.55rem 0.8rem;
  background: transparent;
  color: var(--market-muted);
  font-weight: 750;
}

.offer-tab.active {
  background: var(--market-primary);
  color: #fff;
}

.offer-tab span {
  min-width: 1.6rem;
  border-radius: 999px;
  padding: 0.1rem 0.45rem;
  background: rgba(95, 107, 125, 0.12);
  font-size: 0.78rem;
}

.offer-tab.active span {
  background: rgba(255, 255, 255, 0.22);
}

.offer-card {
  display: grid;
  grid-template-columns: 10rem minmax(0, 1fr) 11rem;
  gap: 1.25rem;
  padding: 1rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow);
}

.offer-image {
  border-radius: 0.75rem;
}

.offer-copy {
  align-self: center;
  min-width: 0;
}

.offer-copy h2 {
  margin: 0.55rem 0 0.25rem;
  font-size: 1.25rem;
  font-weight: 750;
}

.offer-copy p {
  margin: 0;
  line-height: 1.55;
}

.offer-copy strong {
  color: var(--market-primary);
}

.offer-copy blockquote {
  margin: 0.7rem 0 0;
  padding-left: 0.8rem;
  border-left: 3px solid var(--market-primary-fixed);
  color: var(--market-text);
}

.offer-status {
  display: inline-flex;
  border-radius: 999px;
  padding: 0.28rem 0.6rem;
  font-size: 0.72rem;
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

.offer-actions {
  display: flex;
  align-self: center;
  flex-direction: column;
  gap: 0.65rem;
}

.offer-state {
  display: grid;
  min-height: 20rem;
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

@media (max-width: 991.98px) {
  .offer-card {
    grid-template-columns: 8rem minmax(0, 1fr);
  }

  .offer-actions {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 575.98px) {
  .offer-card {
    grid-template-columns: 1fr;
  }

  .offer-actions {
    grid-column: auto;
    grid-template-columns: 1fr;
  }
}
</style>
