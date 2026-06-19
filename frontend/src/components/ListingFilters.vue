<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['apply', 'reset'])

const form = reactive({
  q: '',
  category_id: '',
  condition: '',
  status: '',
  sort: 'newest',
})

watch(
  () => props.filters,
  (filters) => {
    form.q = filters.q ?? ''
    form.category_id = filters.category_id ?? ''
    form.condition = filters.condition ?? ''
    form.status = filters.status ?? ''
    form.sort = filters.sort ?? 'newest'
  },
  { immediate: true, deep: true }
)

function apply() {
  emit('apply', { ...form })
}

function reset() {
  Object.assign(form, {
    q: '',
    category_id: '',
    condition: '',
    status: '',
    sort: 'newest',
  })
  emit('reset')
}
</script>

<template>
  <form class="filter-panel" @submit.prevent="apply">
    <div class="filter-grid">
      <div class="filter-search">
        <label class="form-label" for="listing-search">Keyword</label>
        <input
          id="listing-search"
          v-model="form.q"
          class="form-control"
          type="search"
          maxlength="150"
          placeholder="Search items, sellers, or categories"
        />
      </div>

      <div>
        <label class="form-label" for="listing-category">Category</label>
        <select id="listing-category" v-model="form.category_id" class="form-select">
          <option value="">All categories</option>
          <option
            v-for="category in categories"
            :key="category.category_id"
            :value="String(category.category_id)"
          >
            {{ category.category_name }}
          </option>
        </select>
      </div>

      <div>
        <label class="form-label" for="listing-condition">Condition</label>
        <select id="listing-condition" v-model="form.condition" class="form-select">
          <option value="">Any condition</option>
          <option>New</option>
          <option>Like New</option>
          <option>Used</option>
        </select>
      </div>

      <div>
        <label class="form-label" for="listing-status">Status</label>
        <select id="listing-status" v-model="form.status" class="form-select">
          <option value="">Any status</option>
          <option>Available</option>
          <option>Reserved</option>
          <option>Sold</option>
        </select>
      </div>

      <div>
        <label class="form-label" for="listing-sort">Sort</label>
        <select id="listing-sort" v-model="form.sort" class="form-select">
          <option value="newest">Newest first</option>
          <option value="oldest">Oldest first</option>
          <option value="price_asc">Price: low to high</option>
          <option value="price_desc">Price: high to low</option>
        </select>
      </div>
    </div>

    <div class="filter-actions">
      <button class="btn btn-outline-primary" type="button" @click="reset">Clear</button>
      <button class="btn btn-primary" type="submit">Apply filters</button>
    </div>
  </form>
</template>

<style scoped>
.filter-panel {
  padding: 1.25rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow);
}

.filter-grid {
  display: grid;
  grid-template-columns: minmax(13rem, 1.6fr) repeat(4, minmax(9rem, 1fr));
  gap: 1rem;
}

.form-label {
  color: var(--market-text);
  font-size: 0.78rem;
  font-weight: 750;
}

.form-control,
.form-select {
  min-height: 2.75rem;
  border-color: var(--market-outline);
  border-radius: 0.65rem;
  color: var(--market-text);
}

.form-control:focus,
.form-select:focus {
  border-color: var(--market-primary);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
}

.filter-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
}

@media (max-width: 1199.98px) {
  .filter-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .filter-search {
    grid-column: 1 / -1;
  }
}

@media (max-width: 575.98px) {
  .filter-grid {
    grid-template-columns: 1fr;
  }

  .filter-search {
    grid-column: auto;
  }

  .filter-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
}
</style>
