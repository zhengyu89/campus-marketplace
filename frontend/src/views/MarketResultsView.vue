<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { marketCategories, mockMarketListings } from '../data/markethub'

const route = useRoute()

const query = computed(() => (typeof route.query.q === 'string' ? route.query.q.trim() : ''))
const normalizedQuery = computed(() => query.value.toLowerCase())

const filteredListings = computed(() => {
  if (!normalizedQuery.value) {
    return mockMarketListings
  }

  return mockMarketListings.filter((item) =>
    [item.title, item.category, item.description, item.seller].some((value) =>
      value.toLowerCase().includes(normalizedQuery.value)
    )
  )
})

const pageTitle = computed(() =>
  query.value ? `Search results for "${query.value}"` : 'Browse UTM MarketHub listings'
)

const pageDescription = computed(() =>
  query.value
    ? `Showing mock internal results matched against titles, categories, and item descriptions.`
    : 'This is a mock internal marketplace page for authenticated users. Search from the top bar to preview the flow.'
)
</script>

<template>
  <main class="market-results-page">
    <section class="results-hero">
      <div class="container-xl">
        <span class="results-badge">Internal mock page</span>
        <h1>{{ pageTitle }}</h1>
        <p>{{ pageDescription }}</p>

        <div class="results-meta">
          <div class="results-stat">
            <strong>{{ filteredListings.length }}</strong>
            <span>{{ filteredListings.length === 1 ? 'Result' : 'Results' }}</span>
          </div>
          <div class="results-stat">
            <strong>{{ marketCategories.length }}</strong>
            <span>Categories</span>
          </div>
          <div class="results-stat">
            <strong>Mock</strong>
            <span>Data source</span>
          </div>
        </div>
      </div>
    </section>

    <section class="results-section">
      <div class="container-xl">
        <div class="results-toolbar">
          <div>
            <span class="section-kicker">Search scope</span>
            <h2>Category and listing matches</h2>
          </div>
          <div class="results-tags">
            <span v-for="category in marketCategories" :key="category" class="results-tag">
              {{ category }}
            </span>
          </div>
        </div>

        <div v-if="filteredListings.length" class="row g-4">
          <div v-for="item in filteredListings" :key="item.id" class="col-md-6 col-xl-4">
            <article class="result-card">
              <div class="result-image-wrap">
                <img class="result-image" :src="item.image" :alt="item.title" loading="lazy" />
                <span v-if="item.badge" class="result-badge">{{ item.badge }}</span>
              </div>
              <div class="result-body">
                <div class="result-topline">
                  <span class="result-category">{{ item.category }}</span>
                  <strong class="result-price">{{ item.price }}</strong>
                </div>
                <h3>{{ item.title }}</h3>
                <p>{{ item.description }}</p>
                <div class="result-seller">
                  <span class="seller-avatar" :class="`seller-avatar-${item.accent}`">
                    {{ item.initials }}
                  </span>
                  <span>Posted by {{ item.seller }}</span>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div v-else class="empty-state">
          <span class="section-kicker">No direct matches</span>
          <h2>No mock results found for "{{ query }}"</h2>
          <p>
            Try a broader term like <code>books</code>, <code>electronics</code>, or
            <code>dorm</code>.
          </p>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.market-results-page {
  padding: 2.5rem 0 5rem;
  color: #11304d;
}

.results-hero {
  padding: 1rem 0 2rem;
}

.results-badge,
.section-kicker {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.45rem 0.85rem;
  background: rgba(13, 91, 215, 0.12);
  color: #0d5bd7;
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.results-hero h1 {
  margin: 1rem 0 0.75rem;
  color: #081d35;
  font-size: clamp(2.4rem, 5vw, 4rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.results-hero p {
  max-width: 44rem;
  color: #607894;
  font-size: 1.05rem;
  line-height: 1.8;
}

.results-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 2rem;
}

.results-stat {
  padding: 1rem 1.1rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1.15rem;
  background: rgba(255, 255, 255, 0.82);
  box-shadow: 0 18px 40px rgba(13, 37, 63, 0.06);
}

.results-stat strong {
  display: block;
  color: #081d35;
  font-size: 1.25rem;
}

.results-stat span {
  color: #657c96;
  font-size: 0.92rem;
}

.results-section {
  padding-top: 1.5rem;
}

.results-toolbar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.results-toolbar h2,
.empty-state h2 {
  margin: 0.8rem 0 0;
  color: #081d35;
  font-size: clamp(1.9rem, 4vw, 2.6rem);
  font-weight: 800;
  letter-spacing: -0.03em;
}

.results-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
}

.results-tag {
  padding: 0.7rem 1rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.78);
  color: #2f4c68;
  font-weight: 700;
}

.result-card {
  height: 100%;
  overflow: hidden;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1.45rem;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 18px 50px rgba(11, 36, 61, 0.08);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease;
}

.result-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 28px 70px rgba(11, 36, 61, 0.14);
}

.result-image-wrap {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
}

.result-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.result-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  padding: 0.4rem 0.7rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.9);
  color: #0d5bd7;
  font-size: 0.78rem;
  font-weight: 700;
}

.result-body {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  padding: 1.25rem;
}

.result-topline {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.result-category {
  color: #607894;
  font-size: 0.84rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.result-price {
  color: #0d5bd7;
  font-size: 1.25rem;
}

.result-body h3 {
  margin: 0;
  color: #122d48;
  font-size: 1.08rem;
  font-weight: 700;
}

.result-body p {
  margin: 0;
  color: #667d95;
  line-height: 1.7;
}

.result-seller {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(122, 139, 168, 0.15);
  color: #667b93;
  font-size: 0.92rem;
}

.seller-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.2rem;
  height: 2.2rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
}

.seller-avatar-primary {
  background: #dbe7ff;
  color: #0d5bd7;
}

.seller-avatar-secondary {
  background: #e8eefb;
  color: #4b5e80;
}

.seller-avatar-tertiary {
  background: #deedf1;
  color: #3e5f67;
}

.seller-avatar-warm {
  background: #f4ead8;
  color: #7c5a1a;
}

.empty-state {
  padding: 3rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1.6rem;
  background: rgba(255, 255, 255, 0.82);
  text-align: center;
  box-shadow: 0 18px 50px rgba(11, 36, 61, 0.07);
}

.empty-state p {
  margin-top: 1rem;
  color: #667d95;
}

.empty-state code {
  padding: 0.2rem 0.45rem;
  border-radius: 0.45rem;
  background: rgba(13, 91, 215, 0.08);
  color: #0d5bd7;
}

@media (max-width: 767.98px) {
  .results-meta {
    grid-template-columns: 1fr;
  }
}
</style>
