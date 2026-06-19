<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ListingForm from '../components/ListingForm.vue'
import { useAuthStore } from '../stores/auth'
import { useListingsStore } from '../stores/listings'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const listingsStore = useListingsStore()
const isSubmitting = ref(false)
const apiError = ref('')
const serverErrors = ref({})

const isEditing = computed(() => route.name === 'listing-edit')
const listing = computed(() => (isEditing.value ? listingsStore.currentListing : null))
const canEdit = computed(
  () =>
    !isEditing.value ||
    Number(authStore.user?.user_id) === Number(listingsStore.currentListing?.seller_id)
)

async function preparePage() {
  apiError.value = ''
  serverErrors.value = {}

  try {
    await listingsStore.fetchCategories()

    if (isEditing.value) {
      await listingsStore.fetchListing(route.params.id)
    } else {
      listingsStore.currentListing = null
    }
  } catch (error) {
    apiError.value = error.response?.data?.message ?? 'Unable to prepare the listing form.'
  }
}

async function submit(payload) {
  isSubmitting.value = true
  apiError.value = ''
  serverErrors.value = {}

  try {
    const savedListing = isEditing.value
      ? await listingsStore.updateListingWithImage(route.params.id, payload)
      : await listingsStore.createListing(payload)

    await router.push({
      name: 'listing-detail',
      params: { id: savedListing.listing_id },
      query: { saved: '1' },
    })
  } catch (error) {
    serverErrors.value = error.response?.data?.errors ?? {}
    apiError.value = error.response?.data?.message ?? 'Unable to save this listing.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(preparePage)
watch(() => route.fullPath, preparePage)
</script>

<template>
  <main class="editor-page">
    <div class="container-xl py-5">
      <div class="editor-heading">
        <span class="market-kicker">{{ isEditing ? 'Manage listing' : 'Start selling' }}</span>
        <h1>{{ isEditing ? 'Edit your listing' : 'Create a new listing' }}</h1>
        <p>
          {{
            isEditing
              ? 'Keep the item details and availability up to date.'
              : 'Add an item to the UTM student marketplace in a few simple steps.'
          }}
        </p>
      </div>

      <div v-if="apiError" class="alert alert-danger">{{ apiError }}</div>

      <div
        v-if="isEditing && listingsStore.isLoadingCurrent"
        class="editor-loading"
      >
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading listing form</span>
        </div>
      </div>

      <div v-else-if="isEditing && listing && !canEdit" class="editor-loading">
        <h2>You cannot edit this listing</h2>
        <p>Only the seller who created a listing can modify it.</p>
        <RouterLink class="btn btn-primary" :to="{ name: 'market' }">
          Return to marketplace
        </RouterLink>
      </div>

      <ListingForm
        v-else-if="!isEditing || listing"
        :listing="listing"
        :categories="listingsStore.categories"
        :is-submitting="isSubmitting"
        :server-errors="serverErrors"
        :submit-label="isEditing ? 'Update listing' : 'Publish listing'"
        @submit="submit"
      />
    </div>
  </main>
</template>

<style scoped>
.editor-page {
  min-height: 75vh;
}

.editor-heading {
  max-width: 45rem;
  margin-bottom: 2rem;
}

.editor-heading h1 {
  margin: 0.7rem 0 0.55rem;
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.editor-heading p,
.editor-loading p {
  color: var(--market-muted);
}

.editor-loading {
  display: grid;
  min-height: 22rem;
  place-items: center;
  align-content: center;
  gap: 1rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  text-align: center;
  box-shadow: var(--market-shadow);
}
</style>
