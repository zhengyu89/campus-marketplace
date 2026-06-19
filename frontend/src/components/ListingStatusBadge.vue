<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
})

const statusClass = computed(() => `status-${props.status.toLowerCase().replace(/\s+/g, '-')}`)
</script>

<template>
  <span class="listing-status" :class="statusClass">{{ status }}</span>
</template>

<style scoped>
.listing-status {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  border-radius: 999px;
  padding: 0.35rem 0.65rem;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.status-available {
  background: #dcfce7;
  color: #166534;
}

.status-reserved {
  background: #fef3c7;
  color: #92400e;
}

.status-sold {
  background: #e2e8f0;
  color: #475569;
}
</style>
