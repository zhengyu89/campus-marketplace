<script setup>
import { computed, onMounted } from 'vue'
import ListingCard from '../components/ListingCard.vue'
import { useAuthStore } from '../stores/auth'
import { useListingsStore } from '../stores/listings'

const authStore = useAuthStore()
const listingsStore = useListingsStore()

const primaryRoute = computed(() =>
  authStore.isAuthenticated ? { name: 'listing-create' } : { name: 'register' }
)
const primaryLabel = computed(() => (authStore.isAuthenticated ? 'Start selling' : 'Register now'))

onMounted(async () => {
  await Promise.allSettled([
    listingsStore.fetchCategories(),
    listingsStore.fetchListings({ status: 'Available', limit: 4 }),
  ])
})
</script>

<template>
  <main>
    <section class="home-hero">
      <div class="hero-orb hero-orb-left" />
      <div class="hero-orb hero-orb-right" />
      <div class="container-xl position-relative">
        <div class="hero-content">
          <span class="market-kicker">Marketplace for the UTM community</span>
          <h1>Your campus, your marketplace</h1>
          <p>
            The simple way for UTM students to buy and sell textbooks, electronics, furniture,
            stationery, and everyday campus essentials.
          </p>
          <div class="hero-actions">
            <RouterLink class="btn btn-primary btn-lg" :to="primaryRoute">
              {{ primaryLabel }}
            </RouterLink>
            <RouterLink class="btn btn-outline-primary btn-lg" :to="{ name: 'market' }">
              Browse items
            </RouterLink>
          </div>

          <div class="hero-image-card">
            <img
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuAwB-YTare7em5lVM0hMdom7LWoNl0xD0-i4vMvUlYAHNUBCp3ADCTTWC9tp_y_ueEJUdORhpd_049XRFY2sWDsrtTBCylFTzM0UQJeBGJKkLvzGtd1dfh-rkjvSwOna-K5ej4mivlKmrpNcMzV1dCLX0PexGb8DVT7gjo-6LHloQTQm3IiuQGrXU5kDeyFFa0ETf1lrzUovEO5KnzwTr6RSEqG5FEqDEDUuvL1VkcBDhhfb8_py953f2-LDLc4c3qE1e7xITh90pHP"
              alt="Students gathering in a bright university campus square"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="home-section bg-white">
      <div class="container-xl">
        <div class="section-heading">
          <div>
            <span class="market-kicker">Explore by category</span>
            <h2>Find the right campus deal faster</h2>
          </div>
          <RouterLink class="section-link" :to="{ name: 'market' }">View all →</RouterLink>
        </div>

        <div class="category-grid">
          <RouterLink
            v-for="category in listingsStore.categories"
            :key="category.category_id"
            class="category-link"
            :to="{
              name: 'market',
              query: { category_id: String(category.category_id) },
            }"
          >
            <span>{{ category.category_name.charAt(0) }}</span>
            {{ category.category_name }}
          </RouterLink>
        </div>
      </div>
    </section>

    <section class="home-section recent-section">
      <div class="container-xl">
        <div class="section-heading">
          <div>
            <span class="market-kicker">Recently added</span>
            <h2>Fresh listings from the UTM community</h2>
            <p>See what students are selling right now.</p>
          </div>
          <RouterLink class="section-link" :to="{ name: 'market' }">View all listings →</RouterLink>
        </div>

        <div v-if="listingsStore.isLoading" class="home-loading">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading recent listings</span>
          </div>
        </div>

        <div v-else-if="listingsStore.listings.length" class="row g-4">
          <div
            v-for="listing in listingsStore.listings"
            :key="listing.listing_id"
            class="col-sm-6 col-xl-3"
          >
            <ListingCard :listing="listing" :show-status="false" />
          </div>
        </div>

        <div v-else class="home-empty">
          <h3>No available listings yet</h3>
          <p>Be the first student to add something useful to the marketplace.</p>
          <RouterLink class="btn btn-primary" :to="primaryRoute">{{ primaryLabel }}</RouterLink>
        </div>
      </div>
    </section>

    <section class="home-section bg-white">
      <div class="container-xl">
        <div class="section-heading centered">
          <div>
            <span class="market-kicker">Why UTM MarketHub</span>
            <h2>Built for everyday student buying and selling</h2>
            <p>A focused marketplace without complicated payment, shipping, or chat features.</p>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4">
            <article class="benefit-card">
              <span class="benefit-icon">✓</span>
              <h3>Organized campus listings</h3>
              <p>Search and filter items instead of digging through scattered group messages.</p>
            </article>
          </div>
          <div class="col-md-4">
            <article class="benefit-card">
              <span class="benefit-icon">⌖</span>
              <h3>Local student exchange</h3>
              <p>Discover useful items from other students and arrange convenient campus meetups.</p>
            </article>
          </div>
          <div class="col-md-4">
            <article class="benefit-card">
              <span class="benefit-icon">₀</span>
              <h3>No listing fees</h3>
              <p>Create, update, reserve, or mark listings sold without platform charges.</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="home-cta-wrap">
      <div class="container-xl">
        <div class="home-cta">
          <span class="market-kicker market-kicker-light">Ready to declutter?</span>
          <h2>Turn extra items into another student’s next essential.</h2>
          <p>Create a clear listing and reach the UTM community in minutes.</p>
          <RouterLink class="btn btn-light btn-lg" :to="primaryRoute">
            {{ primaryLabel }}
          </RouterLink>
        </div>
      </div>
    </section>

    <footer class="home-footer">
      <div class="container-xl footer-grid">
        <div>
          <strong>UTM MarketHub</strong>
          <p>A student marketplace built with Vue.js and PHP Slim.</p>
        </div>
        <div>
          <h3>Marketplace</h3>
          <RouterLink :to="{ name: 'market' }">Browse listings</RouterLink>
          <RouterLink :to="{ name: 'listing-create' }">Sell an item</RouterLink>
        </div>
        <div>
          <h3>Account</h3>
          <RouterLink :to="{ name: 'login' }">Login</RouterLink>
          <RouterLink :to="{ name: 'register' }">Register</RouterLink>
        </div>
      </div>
    </footer>
  </main>
</template>

<style scoped>
.home-hero {
  position: relative;
  overflow: hidden;
  padding: 5.5rem 0 5rem;
  background: var(--market-surface);
}

.hero-orb {
  position: absolute;
  width: 32rem;
  height: 32rem;
  border-radius: 999px;
  opacity: 0.12;
  filter: blur(100px);
}

.hero-orb-left {
  top: -14rem;
  left: -13rem;
  background: var(--market-primary);
}

.hero-orb-right {
  right: -14rem;
  bottom: -16rem;
  background: #64748b;
}

.hero-content {
  display: flex;
  align-items: center;
  flex-direction: column;
  text-align: center;
}

.hero-content h1 {
  max-width: 56rem;
  margin: 1rem 0 1.2rem;
  color: var(--market-text);
  font-size: clamp(3rem, 7vw, 5.2rem);
  font-weight: 800;
  letter-spacing: -0.055em;
  line-height: 1.02;
}

.hero-content > p {
  max-width: 43rem;
  color: var(--market-muted);
  font-size: 1.1rem;
  line-height: 1.75;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.9rem;
  margin-top: 1.8rem;
}

.hero-image-card {
  width: min(100%, 58rem);
  height: 25rem;
  overflow: hidden;
  margin-top: 4rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow-hover);
}

.hero-image-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.home-section {
  padding: 5rem 0;
}

.recent-section {
  background: var(--market-surface-low);
}

.section-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 2rem;
}

.section-heading.centered {
  justify-content: center;
  text-align: center;
}

.section-heading h2 {
  margin: 0.65rem 0 0;
  font-size: clamp(2rem, 4vw, 2.8rem);
  font-weight: 800;
  letter-spacing: -0.035em;
}

.section-heading p {
  margin-top: 0.45rem;
  color: var(--market-muted);
}

.section-link {
  color: var(--market-primary);
  font-weight: 750;
  text-decoration: none;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 1rem;
}

.category-link {
  display: flex;
  align-items: center;
  flex-direction: column;
  gap: 0.8rem;
  padding: 1.25rem 0.75rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  color: var(--market-text);
  font-size: 0.88rem;
  font-weight: 750;
  text-decoration: none;
  box-shadow: var(--market-shadow);
  transition: 0.2s ease;
}

.category-link span {
  display: grid;
  width: 3rem;
  height: 3rem;
  place-items: center;
  border-radius: 999px;
  background: var(--market-primary-fixed);
  color: var(--market-primary-dark);
  font-size: 1.2rem;
}

.category-link:hover {
  transform: translateY(-4px);
  border-color: rgba(13, 110, 253, 0.35);
  color: var(--market-primary);
  box-shadow: var(--market-shadow-hover);
}

.benefit-card {
  height: 100%;
  padding: 2rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: var(--market-surface-low);
  text-align: center;
}

.benefit-icon {
  display: grid;
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1.25rem;
  place-items: center;
  border-radius: 999px;
  background: var(--market-primary);
  color: #fff;
  font-size: 1.55rem;
  font-weight: 800;
}

.benefit-card h3 {
  font-size: 1.25rem;
  font-weight: 750;
}

.benefit-card p {
  margin: 0;
  color: var(--market-muted);
  line-height: 1.7;
}

.home-loading,
.home-empty {
  display: grid;
  min-height: 16rem;
  place-items: center;
  align-content: center;
  gap: 0.85rem;
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  text-align: center;
}

.home-empty p {
  color: var(--market-muted);
}

.home-cta-wrap {
  padding: 5rem 0;
  background: #fff;
}

.home-cta {
  position: relative;
  overflow: hidden;
  padding: clamp(2.5rem, 6vw, 5rem);
  border-radius: 1.5rem;
  background: var(--market-primary);
  color: #fff;
  text-align: center;
  box-shadow: var(--market-shadow-hover);
}

.home-cta h2 {
  max-width: 50rem;
  margin: 0.8rem auto;
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 800;
  letter-spacing: -0.045em;
}

.home-cta p {
  margin-bottom: 1.5rem;
  opacity: 0.85;
}

.home-footer {
  padding: 3rem 0;
  background: var(--market-surface-high);
}

.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 2rem;
}

.footer-grid strong {
  color: var(--market-text);
  font-size: 1.35rem;
}

.footer-grid p {
  max-width: 24rem;
  margin-top: 0.5rem;
  color: var(--market-muted);
}

.footer-grid h3 {
  font-size: 0.82rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.footer-grid a {
  display: block;
  width: fit-content;
  margin-top: 0.55rem;
  color: var(--market-muted);
  text-decoration: none;
}

@media (max-width: 991.98px) {
  .category-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 575.98px) {
  .home-hero {
    padding-top: 3.5rem;
  }

  .hero-image-card {
    height: 18rem;
    margin-top: 2.5rem;
  }

  .home-section {
    padding: 3.5rem 0;
  }

  .section-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .category-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .footer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
