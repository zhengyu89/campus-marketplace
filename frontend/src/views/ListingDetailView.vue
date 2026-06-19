<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ListingImage from '../components/ListingImage.vue'
import ListingStatusBadge from '../components/ListingStatusBadge.vue'
import { useAuthStore } from '../stores/auth'
import { useListingsStore } from '../stores/listings'
import { useOffersStore } from '../stores/offers'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const listingsStore = useListingsStore()
const offersStore = useOffersStore()
const offerForm = reactive({
  offer_price: '',
  message: '',
})
const offerError = ref('')
const offerSuccess = ref('')
const isSubmittingOffer = ref(false)
const isEditingOffer = ref(false)

const listing = computed(() => listingsStore.currentListing)
const existingOffer = computed(() =>
  offersStore.sentOffers.find(
    (offer) => Number(offer.listing_id) === Number(listing.value?.listing_id)
  )
)
const isOwner = computed(
  () =>
    authStore.isAuthenticated &&
    Number(authStore.user?.user_id) === Number(listing.value?.seller_id)
)
const canOffer = computed(
  () =>
    authStore.isAuthenticated &&
    !isOwner.value &&
    !existingOffer.value &&
    listing.value?.listing_status === 'Available'
)
const canEditExistingOffer = computed(() => existingOffer.value?.offer_status === 'Pending')
const canDeleteExistingOffer = computed(() =>
  existingOffer.value && existingOffer.value.offer_status !== 'Accepted'
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
    if (authStore.isAuthenticated) {
      await offersStore.fetchSentOffers()
    }
    resetOfferForm()
    offerError.value = ''
    offerSuccess.value = ''
    isEditingOffer.value = false
  } catch {
    // The store exposes the request error to the page.
  }
}

function resetOfferForm() {
  if (existingOffer.value) {
    offerForm.offer_price = String(existingOffer.value.offer_price)
    offerForm.message = existingOffer.value.message ?? ''
    return
  }

  offerForm.offer_price = listing.value?.price ? String(listing.value.price) : ''
  offerForm.message = ''
}

async function submitOffer() {
  if (!listing.value) {
    return
  }

  offerError.value = ''
  offerSuccess.value = ''
  isSubmittingOffer.value = true

  try {
    if (existingOffer.value) {
      await offersStore.updateOffer(existingOffer.value.offer_id, {
        offer_price: offerForm.offer_price,
        message: offerForm.message,
      })
      offerSuccess.value = 'Offer updated.'
      isEditingOffer.value = false
    } else {
      await offersStore.createOffer(listing.value.listing_id, {
        offer_price: offerForm.offer_price,
        message: offerForm.message,
      })
      offerSuccess.value = 'Offer sent. You can track it from your Offers page.'
    }

    resetOfferForm()
  } catch (error) {
    const errors = error.response?.data?.errors ?? {}
    offerError.value =
      errors.offer_price ??
      errors.message ??
      error.response?.data?.message ??
      (existingOffer.value ? 'Unable to update this offer.' : 'Unable to send this offer.')
  } finally {
    isSubmittingOffer.value = false
  }
}

async function deleteOffer() {
  if (!existingOffer.value) {
    return
  }

  const confirmed = window.confirm('Delete your offer for this listing?')

  if (!confirmed) {
    return
  }

  offerError.value = ''
  offerSuccess.value = ''
  isSubmittingOffer.value = true

  try {
    await offersStore.deleteOffer(existingOffer.value.offer_id)
    offerSuccess.value = 'Offer deleted.'
    isEditingOffer.value = false
    resetOfferForm()
  } catch (error) {
    const message = error.response?.data?.message
    offerError.value = message ?? 'Unable to delete this offer.'
  } finally {
    isSubmittingOffer.value = false
  }
}

function startEditingOffer() {
  resetOfferForm()
  offerError.value = ''
  offerSuccess.value = ''
  isEditingOffer.value = true
}

function cancelEditingOffer() {
  resetOfferForm()
  offerError.value = ''
  isEditingOffer.value = false
}

function goToLogin() {
  router.push({
    name: 'login',
    query: {
      redirect: route.fullPath,
    },
  })
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

            <div v-else class="offer-panel">
              <template v-if="!authStore.isAuthenticated">
                <strong>Interested in this item?</strong>
                <p>Log in to make an offer to the seller.</p>
                <button class="btn btn-primary w-100" type="button" @click="goToLogin">
                  Login to make offer
                </button>
              </template>

              <template v-else-if="existingOffer && !isEditingOffer">
                <strong>Your offer</strong>
                <div class="existing-offer">
                  <span :class="`offer-status offer-status-${existingOffer.offer_status.toLowerCase()}`">
                    {{ existingOffer.offer_status }}
                  </span>
                  <strong>{{ formatPrice(existingOffer.offer_price) }}</strong>
                  <p v-if="existingOffer.message">{{ existingOffer.message }}</p>
                  <p v-else>No message added.</p>
                </div>

                <div v-if="offerSuccess" class="alert alert-success py-2 mt-3">
                  {{ offerSuccess }}
                </div>
                <div v-if="offerError" class="alert alert-danger py-2 mt-3">
                  {{ offerError }}
                </div>

                <div class="offer-action-grid">
                  <button
                    class="btn btn-outline-primary"
                    type="button"
                    :disabled="!canEditExistingOffer || isSubmittingOffer"
                    @click="startEditingOffer"
                  >
                    Edit offer
                  </button>
                  <button
                    class="btn btn-outline-danger"
                    type="button"
                    :disabled="!canDeleteExistingOffer || isSubmittingOffer"
                    @click="deleteOffer"
                  >
                    Delete offer
                  </button>
                </div>
              </template>

              <template v-else-if="listing.listing_status !== 'Available'">
                <strong>Offers unavailable</strong>
                <p>This listing is currently {{ listing.listing_status.toLowerCase() }}.</p>
              </template>

              <form v-else @submit.prevent="submitOffer">
                <strong>{{ existingOffer ? 'Edit your offer' : 'Make an offer' }}</strong>
                <p>
                  {{
                    existingOffer
                      ? 'Update the price or message before the seller responds.'
                      : `Send your price and an optional note to ${listing.seller?.name}.`
                  }}
                </p>

                <div v-if="offerSuccess" class="alert alert-success py-2">
                  {{ offerSuccess }}
                </div>
                <div v-if="offerError" class="alert alert-danger py-2">
                  {{ offerError }}
                </div>

                <label class="form-label" for="offer-price">Offer price</label>
                <div class="input-group mb-3">
                  <span class="input-group-text">RM</span>
                  <input
                    id="offer-price"
                    v-model="offerForm.offer_price"
                    class="form-control"
                    type="number"
                    min="0.01"
                    step="0.01"
                    required
                  />
                </div>

                <label class="form-label" for="offer-message">Message</label>
                <textarea
                  id="offer-message"
                  v-model.trim="offerForm.message"
                  class="form-control"
                  maxlength="255"
                  rows="3"
                  placeholder="Optional note for the seller"
                />
                <div class="form-text text-end">{{ offerForm.message.length }}/255</div>

                <button
                  class="btn btn-primary btn-lg w-100 mt-3"
                  type="submit"
                  :disabled="existingOffer ? !canEditExistingOffer || isSubmittingOffer : !canOffer || isSubmittingOffer"
                >
                  {{
                    isSubmittingOffer
                      ? 'Saving...'
                      : existingOffer
                        ? 'Save offer'
                        : 'Send offer'
                  }}
                </button>
                <button
                  v-if="existingOffer"
                  class="btn btn-link w-100 mt-2"
                  type="button"
                  :disabled="isSubmittingOffer"
                  @click="cancelEditingOffer"
                >
                  Cancel edit
                </button>
              </form>
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

.offer-panel {
  padding: 1rem;
  border: 1px solid rgba(13, 110, 253, 0.22);
  border-radius: 0.8rem;
  background: rgba(13, 110, 253, 0.05);
}

.offer-panel p {
  margin: 0.35rem 0 0;
  color: var(--market-muted);
  font-size: 0.88rem;
  line-height: 1.55;
}

.offer-panel form > p,
.offer-panel form > strong {
  display: block;
}

.offer-panel .form-label {
  margin-top: 1rem;
  font-size: 0.82rem;
  font-weight: 750;
}

.existing-offer {
  margin-top: 0.8rem;
  padding: 0.9rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.7rem;
  background: #fff;
}

.existing-offer > strong {
  display: block;
  margin-top: 0.65rem;
  color: var(--market-primary);
  font-size: 1.35rem;
}

.offer-status {
  display: inline-flex;
  border-radius: 999px;
  padding: 0.28rem 0.6rem;
  font-size: 0.7rem;
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

.offer-action-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
  margin-top: 0.9rem;
}

@media (max-width: 575.98px) {
  .offer-action-grid {
    grid-template-columns: 1fr;
  }
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
