import { defineStore } from 'pinia'
import { api } from '../api/http'

export const useListingsStore = defineStore('listings', {
  state: () => ({
    listings: [],
    currentListing: null,
    myListings: [],
    categories: [],
    isLoading: false,
    isLoadingCurrent: false,
    isLoadingMine: false,
    error: '',
  }),
  actions: {
    async fetchCategories(force = false) {
      if (!force && this.categories.length > 0) {
        return this.categories
      }

      const response = await api.get('/categories')
      this.categories = response.data?.data?.categories ?? []

      return this.categories
    },
    async fetchListings(filters = {}) {
      this.isLoading = true
      this.error = ''

      try {
        const response = await api.get('/listings', { params: filters })
        this.listings = response.data?.data?.listings ?? []
        return this.listings
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Unable to load marketplace listings.'
        throw error
      } finally {
        this.isLoading = false
      }
    },
    async fetchListing(listingId) {
      this.isLoadingCurrent = true
      this.currentListing = null
      this.error = ''

      try {
        const response = await api.get(`/listings/${listingId}`)
        this.currentListing = response.data?.data?.listing ?? null
        return this.currentListing
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Unable to load this listing.'
        throw error
      } finally {
        this.isLoadingCurrent = false
      }
    },
    async fetchMyListings() {
      this.isLoadingMine = true
      this.error = ''

      try {
        const response = await api.get('/listings/mine')
        this.myListings = response.data?.data?.listings ?? []
        return this.myListings
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Unable to load your listings.'
        throw error
      } finally {
        this.isLoadingMine = false
      }
    },
    async createListing(payload) {
      const response = await api.post('/listings', payload)
      const listing = response.data?.data?.listing

      if (listing) {
        this.myListings.unshift(listing)
      }

      return listing
    },
    async updateListing(listingId, payload) {
      const response = await api.put(`/listings/${listingId}`, payload)
      const listing = response.data?.data?.listing

      if (listing) {
        this.currentListing =
          this.currentListing?.listing_id === listing.listing_id ? listing : this.currentListing
        this.myListings = this.myListings.map((item) =>
          item.listing_id === listing.listing_id ? listing : item
        )
        this.listings = this.listings.map((item) =>
          item.listing_id === listing.listing_id ? listing : item
        )
      }

      return listing
    },
    async deleteListing(listingId) {
      const response = await api.delete(`/listings/${listingId}`)
      this.myListings = this.myListings.filter((item) => item.listing_id !== listingId)
      this.listings = this.listings.filter((item) => item.listing_id !== listingId)

      if (this.currentListing?.listing_id === listingId) {
        this.currentListing = null
      }

      return response.data?.data
    },
  },
})
