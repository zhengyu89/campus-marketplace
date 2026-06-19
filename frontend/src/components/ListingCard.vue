<script setup>
import ListingImage from './ListingImage.vue'
import ListingStatusBadge from './ListingStatusBadge.vue'

defineProps({
  listing: {
    type: Object,
    required: true,
  },
  showStatus: {
    type: Boolean,
    default: true,
  },
})

const formatPrice = (price) =>
  new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(Number(price) || 0)

const sellerInitials = (name = '') =>
  name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0].toUpperCase())
    .join('')

const formatDate = (value) =>
  new Intl.DateTimeFormat('en-MY', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
</script>

<template>
  <RouterLink
    class="listing-card text-decoration-none"
    :to="{ name: 'listing-detail', params: { id: listing.listing_id } }"
  >
    <ListingImage :src="listing.image_url" :alt="listing.title">
      <div class="card-badges">
        <span class="condition-badge">{{ listing.condition_status }}</span>
        <ListingStatusBadge v-if="showStatus" :status="listing.listing_status" />
      </div>
    </ListingImage>

    <div class="listing-card-body">
      <div class="listing-card-meta">
        <span>{{ listing.category_name }}</span>
        <span>{{ formatDate(listing.created_at) }}</span>
      </div>
      <strong class="listing-price">{{ formatPrice(listing.price) }}</strong>
      <h3>{{ listing.title }}</h3>
      <p>{{ listing.description }}</p>

      <div class="seller-row">
        <span class="seller-avatar">{{ sellerInitials(listing.seller?.name) }}</span>
        <span>Posted by {{ listing.seller?.name }}</span>
      </div>
    </div>
  </RouterLink>
</template>

<style scoped>
.listing-card {
  display: flex;
  height: 100%;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  color: var(--market-text);
  box-shadow: var(--market-shadow);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    border-color 0.22s ease;
}

.listing-card:hover {
  transform: translateY(-5px);
  border-color: rgba(13, 110, 253, 0.35);
  color: var(--market-text);
  box-shadow: var(--market-shadow-hover);
}

.listing-card:hover :deep(.listing-image-element) {
  transform: scale(1.04);
}

.card-badges {
  position: absolute;
  top: 0.85rem;
  left: 0.85rem;
  right: 0.85rem;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
}

.condition-badge {
  border-radius: 0.45rem;
  padding: 0.35rem 0.6rem;
  background: rgba(255, 255, 255, 0.92);
  color: #0f172a;
  font-size: 0.72rem;
  font-weight: 800;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.1);
}

.listing-card-body {
  display: flex;
  flex: 1;
  flex-direction: column;
  padding: 1rem;
}

.listing-card-meta {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.45rem;
  color: var(--market-muted);
  font-size: 0.74rem;
  font-weight: 600;
}

.listing-price {
  color: var(--market-primary);
  font-size: 1.45rem;
  line-height: 1.2;
}

h3 {
  margin: 0.45rem 0;
  color: var(--market-text);
  font-size: 1rem;
  font-weight: 750;
  line-height: 1.4;
}

p {
  display: -webkit-box;
  overflow: hidden;
  margin: 0 0 1rem;
  color: var(--market-muted);
  font-size: 0.88rem;
  line-height: 1.55;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.seller-row {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid var(--market-outline);
  color: var(--market-muted);
  font-size: 0.8rem;
}

.seller-avatar {
  display: inline-grid;
  flex: 0 0 auto;
  width: 1.8rem;
  height: 1.8rem;
  place-items: center;
  border-radius: 999px;
  background: var(--market-primary-fixed);
  color: var(--market-primary-dark);
  font-size: 0.65rem;
  font-weight: 800;
}
</style>
