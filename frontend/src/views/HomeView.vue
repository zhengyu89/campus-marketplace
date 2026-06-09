<script setup>
import { computed } from 'vue'
import { mockMarketListings, marketCategories, whyReasons } from '../data/markethub'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)
const primaryActionRoute = computed(() => (isAuthenticated.value ? { name: 'account' } : { name: 'register' }))
const primaryActionLabel = computed(() => (isAuthenticated.value ? 'Open My Account' : 'Start Selling'))
const secondaryActionRoute = computed(() => (isAuthenticated.value ? { name: 'market' } : { name: 'login' }))
const secondaryActionLabel = computed(() => (isAuthenticated.value ? 'Browse Listings' : 'Log In'))
const recentItems = computed(() => mockMarketListings.slice(0, 4))
</script>

<template>
  <main class="markethub-page">
    <section class="hero-section">
      <div class="container-xl position-relative">
        <div class="hero-shell row align-items-center g-5">
          <div class="col-xl-6">
            <span class="hero-badge">Marketplace for the UTM community</span>
            <h1 class="hero-title">UTM MarketHub</h1>
            <p class="hero-subtitle">Your campus, your marketplace.</p>
            <p class="hero-copy">
              Discover textbooks, gadgets, dorm essentials, and quick student-to-student deals in
              one polished marketplace made for UTM life.
            </p>

            <div class="d-flex flex-wrap gap-3 mt-4">
              <RouterLink class="hero-button hero-button-primary" :to="primaryActionRoute">
                {{ primaryActionLabel }}
              </RouterLink>
              <RouterLink class="hero-button hero-button-secondary" :to="secondaryActionRoute">
                {{ secondaryActionLabel }}
              </RouterLink>
            </div>

            <div v-if="isAuthenticated" class="signed-in-note">
              Signed in as {{ authStore.user?.name }} · {{ authStore.user?.role }}
            </div>

            <div class="hero-stats row g-3 mt-4">
              <div class="col-sm-4">
                <div class="stat-card">
                  <strong>10+</strong>
                  <span>Seeded student accounts</span>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="stat-card">
                  <strong>6</strong>
                  <span>Starter categories</span>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="stat-card">
                  <strong>24/7</strong>
                  <span>Local browsing anytime</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-6">
            <div class="hero-visual">
              <img
                class="hero-image"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAwB-YTare7em5lVM0hMdom7LWoNl0xD0-i4vMvUlYAHNUBCp3ADCTTWC9tp_y_ueEJUdORhpd_049XRFY2sWDsrtTBCylFTzM0UQJeBGJKkLvzGtd1dfh-rkjvSwOna-K5ej4mivlKmrpNcMzV1dCLX0PexGb8DVT7gjo-6LHloQTQm3IiuQGrXU5kDeyFFa0ETf1lrzUovEO5KnzwTr6RSEqG5FEqDEDUuvL1VkcBDhhfb8_py953f2-LDLc4c3qE1e7xITh90pHP"
                alt="Students gathering in a bright campus square"
              />
              <div class="hero-floating-card">
                <span>Trending this week</span>
                <strong>Calculators, bikes, and dorm furniture</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="market-categories" class="categories-section">
      <div class="container-xl">
        <div class="section-heading">
          <div>
            <span class="section-kicker">Explore by category</span>
            <h2>Find the right UTM deal faster</h2>
          </div>
        </div>
        <div class="category-pills">
          <span v-for="category in marketCategories" :key="category" class="category-pill">
            {{ category }}
          </span>
        </div>
      </div>
    </section>

    <section id="recently-added" class="items-section">
      <div class="container-xl">
        <div class="section-heading">
          <div>
            <span class="section-kicker">Recently added</span>
            <h2>Fresh listings from the UTM community</h2>
            <p>Browse the kinds of student finds the marketplace is built to surface.</p>
          </div>
          <RouterLink class="section-link" :to="{ name: isAuthenticated ? 'market' : 'login' }">
            View all listings
          </RouterLink>
        </div>

        <div class="row g-4">
          <div v-for="item in recentItems" :key="item.title" class="col-sm-6 col-xl-3">
            <article class="listing-card">
              <div class="listing-image-wrap">
                <img class="listing-image" :src="item.image" :alt="item.title" loading="lazy" />
                <span v-if="item.badge" class="listing-badge">
                  {{ item.badge }}
                </span>
              </div>
              <div class="listing-body">
                <div class="listing-price">
                  {{ item.price }}
                </div>
                <h3 class="listing-title">
                  {{ item.title }}
                </h3>
                <div class="listing-seller">
                  <span class="seller-avatar" :class="`seller-avatar-${item.accent}`">
                    {{ item.initials }}
                  </span>
                  <span>{{ item.seller }}</span>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section id="why-markethub" class="benefits-section">
      <div class="container-xl">
        <div class="section-heading centered">
          <div>
            <span class="section-kicker">Why UTM MarketHub</span>
            <h2>Built to solve real campus buying and selling problems</h2>
            <p>
              UTM MarketHub gives students one organized, campus-focused place to discover
              affordable second-hand items without relying on scattered chat groups or social posts.
            </p>
          </div>
        </div>

        <div class="why-intro-card">
          <div class="why-intro-copy">
            <span class="section-kicker">Purpose</span>
            <h3>A more practical marketplace for everyday student needs</h3>
            <p>
              Students often need books, stationery, electronics, furniture, and other daily-use
              items at better prices. UTM MarketHub keeps those listings in one manageable system so
              browsing, searching, and comparing choices feels much easier.
            </p>
          </div>
        </div>

        <div class="row g-4">
          <div v-for="reason in whyReasons" :key="reason.id" class="col-md-4">
            <article class="benefit-card">
              <div class="benefit-icon">{{ reason.id }}</div>
              <h3>{{ reason.title }}</h3>
              <p>{{ reason.description }}</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container-xl">
        <div class="cta-card">
          <span class="section-kicker text-white-50">Ready to list something useful?</span>
          <h2>Turn extra items into someone else’s next essential.</h2>
          <p>
            Join UTM MarketHub and keep student buying and selling focused, local, and easy to
            trust.
          </p>
          <RouterLink class="hero-button hero-button-light" :to="primaryActionRoute">
            {{ isAuthenticated ? 'Go to my account' : 'Register now' }}
          </RouterLink>
        </div>
      </div>
    </section>

    <footer class="page-footer">
      <div class="container-xl">
        <div class="footer-grid">
          <div>
            <div class="footer-brand">UTM MarketHub</div>
            <p>Built for UTM students to buy, sell, and trade with more confidence.</p>
          </div>
          <div>
            <h3>Marketplace</h3>
            <a href="#recently-added">Recently added</a>
            <a href="#market-categories">Categories</a>
          </div>
          <div>
            <h3>Support</h3>
            <RouterLink :to="{ name: 'login' }">Login</RouterLink>
            <RouterLink :to="{ name: 'register' }">Register</RouterLink>
          </div>
          <div>
            <h3>Community</h3>
            <a href="#why-markethub">Why UTM MarketHub</a>
            <RouterLink :to="{ name: 'account' }">Account</RouterLink>
          </div>
        </div>
      </div>
    </footer>
  </main>
</template>

<style scoped>
.markethub-page {
  color: #11304d;
}

.hero-section {
  position: relative;
  overflow: hidden;
  padding: 4rem 0 5rem;
}

.hero-section::before,
.hero-section::after {
  content: '';
  position: absolute;
  border-radius: 999px;
  filter: blur(80px);
  opacity: 0.7;
}

.hero-section::before {
  top: -6rem;
  left: -5rem;
  width: 18rem;
  height: 18rem;
  background: rgba(13, 91, 215, 0.22);
}

.hero-section::after {
  right: -4rem;
  bottom: -6rem;
  width: 16rem;
  height: 16rem;
  background: rgba(255, 177, 64, 0.22);
}

.hero-shell {
  position: relative;
  z-index: 1;
}

.hero-badge,
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

.hero-title {
  margin: 1rem 0 0.6rem;
  font-size: clamp(3rem, 7vw, 5.4rem);
  line-height: 0.94;
  font-weight: 800;
  letter-spacing: -0.05em;
  color: #081d35;
}

.hero-subtitle {
  margin-bottom: 0.75rem;
  color: #0d5bd7;
  font-size: 1.35rem;
  font-weight: 700;
}

.hero-copy {
  max-width: 36rem;
  color: #5a718d;
  font-size: 1.08rem;
  line-height: 1.8;
}

.hero-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 11rem;
  padding: 0.95rem 1.35rem;
  border-radius: 1rem;
  font-weight: 700;
  text-decoration: none;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease,
    color 0.2s ease;
}

.hero-button:hover {
  transform: translateY(-2px);
}

.hero-button-primary {
  background: linear-gradient(135deg, #0d5bd7, #2f83ff);
  color: #fff;
  box-shadow: 0 18px 40px rgba(13, 91, 215, 0.26);
}

.hero-button-secondary {
  border: 1px solid rgba(13, 91, 215, 0.24);
  background: rgba(255, 255, 255, 0.78);
  color: #0d5bd7;
}

.hero-button-light {
  margin-top: 1rem;
  background: #fff;
  color: #0d5bd7;
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16);
}

.signed-in-note {
  margin-top: 1rem;
  color: #607894;
  font-weight: 600;
}

.stat-card {
  height: 100%;
  padding: 1rem 1.1rem;
  border: 1px solid rgba(120, 136, 170, 0.18);
  border-radius: 1.15rem;
  background: rgba(255, 255, 255, 0.72);
  box-shadow: 0 18px 40px rgba(13, 37, 63, 0.06);
}

.stat-card strong {
  display: block;
  color: #081d35;
  font-size: 1.3rem;
}

.stat-card span {
  color: #657c96;
  font-size: 0.92rem;
}

.hero-visual {
  position: relative;
  padding: 1rem;
  border-radius: 2rem;
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.9), rgba(233, 242, 255, 0.92));
  box-shadow: 0 28px 80px rgba(10, 37, 64, 0.14);
}

.hero-image {
  width: 100%;
  min-height: 26rem;
  object-fit: cover;
  border-radius: 1.5rem;
}

.hero-floating-card {
  position: absolute;
  right: 2rem;
  bottom: 2rem;
  max-width: 15rem;
  padding: 1rem 1.1rem;
  border-radius: 1rem;
  background: rgba(8, 29, 53, 0.82);
  color: #f5f9ff;
  box-shadow: 0 20px 60px rgba(8, 29, 53, 0.3);
}

.hero-floating-card span {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: rgba(245, 249, 255, 0.68);
}

.hero-floating-card strong {
  font-size: 1rem;
  line-height: 1.5;
}

.categories-section,
.items-section,
.benefits-section,
.cta-section {
  padding: 2rem 0 5rem;
}

.section-heading {
  display: flex;
  justify-content: space-between;
  align-items: end;
  gap: 1rem;
  margin-bottom: 2rem;
}

.section-heading.centered {
  justify-content: center;
  text-align: center;
}

.section-heading h2 {
  margin: 0.8rem 0 0.55rem;
  color: #081d35;
  font-size: clamp(2rem, 4vw, 2.9rem);
  font-weight: 800;
  letter-spacing: -0.03em;
}

.section-heading p {
  margin: 0;
  color: #637b95;
  font-size: 1rem;
}

.section-link {
  color: #0d5bd7;
  font-weight: 700;
  text-decoration: none;
}

.category-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 0.9rem;
}

.category-pill {
  padding: 0.85rem 1.15rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.78);
  color: #2f4c68;
  font-weight: 700;
  box-shadow: 0 16px 36px rgba(11, 36, 61, 0.05);
}

.listing-card {
  height: 100%;
  overflow: hidden;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1.45rem;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 18px 50px rgba(11, 36, 61, 0.08);
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease;
}

.listing-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 28px 70px rgba(11, 36, 61, 0.14);
}

.listing-image-wrap {
  position: relative;
  overflow: hidden;
  aspect-ratio: 1 / 1;
}

.listing-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.listing-card:hover .listing-image {
  transform: scale(1.05);
}

.listing-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  padding: 0.4rem 0.7rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.88);
  color: #0d5bd7;
  font-size: 0.78rem;
  font-weight: 700;
}

.listing-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.25rem;
}

.listing-price {
  color: #0d5bd7;
  font-size: 1.55rem;
  font-weight: 800;
}

.listing-title {
  margin: 0;
  color: #122d48;
  font-size: 1.03rem;
  font-weight: 700;
}

.listing-seller {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin-top: auto;
  padding-top: 0.9rem;
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

.benefit-card {
  height: 100%;
  padding: 2rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1.6rem;
  background: rgba(255, 255, 255, 0.82);
  text-align: left;
  box-shadow: 0 18px 50px rgba(11, 36, 61, 0.07);
}

.why-intro-card {
  margin-bottom: 1.8rem;
  padding: 2rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1.8rem;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(233, 242, 255, 0.84));
  box-shadow: 0 18px 50px rgba(11, 36, 61, 0.07);
}

.why-intro-copy {
  max-width: 52rem;
}

.why-intro-card h3 {
  margin: 1rem 0 0.8rem;
  color: #102b45;
  font-size: clamp(1.45rem, 3vw, 2.1rem);
  font-weight: 800;
  letter-spacing: -0.03em;
}

.why-intro-card p {
  color: #667d95;
  line-height: 1.8;
  margin: 0;
}

.benefit-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 4rem;
  height: 4rem;
  margin-bottom: 1rem;
  border-radius: 999px;
  background: linear-gradient(135deg, #0d5bd7, #2f83ff);
  color: #fff;
  font-size: 1.6rem;
  font-weight: 800;
}

.benefit-card h3 {
  margin-top: 0;
  margin-bottom: 0.75rem;
  color: #102b45;
  font-size: 1.2rem;
  font-weight: 800;
}

.benefit-card p {
  color: #667d95;
  line-height: 1.7;
}

.cta-card {
  position: relative;
  overflow: hidden;
  padding: 3rem;
  border-radius: 2rem;
  background: linear-gradient(135deg, #0d5bd7, #0a3e93);
  color: #fff;
  box-shadow: 0 30px 80px rgba(13, 91, 215, 0.28);
}

.cta-card::before,
.cta-card::after {
  content: '';
  position: absolute;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.12);
}

.cta-card::before {
  top: -3rem;
  right: -2rem;
  width: 10rem;
  height: 10rem;
}

.cta-card::after {
  bottom: -2rem;
  left: -1rem;
  width: 6rem;
  height: 6rem;
}

.cta-card h2 {
  position: relative;
  z-index: 1;
  max-width: 38rem;
  margin: 1rem 0 0.7rem;
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 800;
  letter-spacing: -0.04em;
}

.cta-card p {
  position: relative;
  z-index: 1;
  max-width: 36rem;
  color: rgba(255, 255, 255, 0.88);
  font-size: 1.06rem;
  line-height: 1.8;
}

.page-footer {
  padding: 1rem 0 4rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 2rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(116, 137, 170, 0.18);
}

.footer-brand {
  margin-bottom: 0.85rem;
  color: #0d5bd7;
  font-size: 1.2rem;
  font-weight: 800;
}

.page-footer h3 {
  margin-bottom: 0.9rem;
  color: #102b45;
  font-size: 0.92rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.page-footer p,
.page-footer a {
  display: block;
  margin-bottom: 0.65rem;
  color: #667d95;
  text-decoration: none;
}

.page-footer a:hover,
.section-link:hover {
  color: #0d5bd7;
}

@media (max-width: 991.98px) {
  .hero-section {
    padding-top: 2.5rem;
  }

  .hero-image {
    min-height: 20rem;
  }

  .section-heading {
    align-items: start;
    flex-direction: column;
  }

  .footer-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 767.98px) {
  .hero-floating-card {
    position: static;
    margin-top: 1rem;
  }

  .cta-card {
    padding: 2rem;
  }

  .footer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
