<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: '',
  },
  alt: {
    type: String,
    default: 'Marketplace listing',
  },
  ratio: {
    type: String,
    default: '1 / 1',
  },
  fit: {
    type: String,
    default: 'cover',
    validator: (value) => ['cover', 'contain'].includes(value),
  },
})

const failed = ref(false)
const resolvedSrc = computed(() => {
  if (!props.src || /^(?:https?:|blob:|data:)/i.test(props.src)) {
    return props.src
  }

  if (props.src.startsWith('/')) {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api'

    try {
      return `${new URL(apiBaseUrl).origin}${props.src}`
    } catch {
      return props.src
    }
  }

  return props.src
})
const canShowImage = computed(() => Boolean(resolvedSrc.value) && !failed.value)

watch(
  () => props.src,
  () => {
    failed.value = false
  }
)
</script>

<template>
  <div class="listing-image-shell" :style="{ aspectRatio: ratio }">
    <img
      v-if="canShowImage"
      class="listing-image-element"
      :src="resolvedSrc"
      :alt="alt"
      :style="{ objectFit: fit }"
      loading="lazy"
      @error="failed = true"
    />
    <div v-else class="listing-image-fallback" role="img" :aria-label="`${alt} image unavailable`">
      <span class="fallback-icon" aria-hidden="true">▧</span>
      <span>Image unavailable</span>
    </div>
    <slot />
  </div>
</template>

<style scoped>
.listing-image-shell {
  position: relative;
  width: 100%;
  overflow: hidden;
  background: linear-gradient(135deg, var(--market-surface-low), var(--market-surface-high));
}

.listing-image-element {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.28s ease;
}

.listing-image-fallback {
  display: grid;
  place-items: center;
  align-content: center;
  gap: 0.5rem;
  width: 100%;
  height: 100%;
  color: var(--market-muted);
  font-size: 0.82rem;
  font-weight: 600;
}

.fallback-icon {
  color: var(--market-primary);
  font-size: 2rem;
}
</style>
