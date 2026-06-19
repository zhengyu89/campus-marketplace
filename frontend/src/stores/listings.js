import { defineStore } from 'pinia'
import { api } from '../api/http'

function createListingFormData(fields, imageFile) {
  const formData = new FormData()

  Object.entries(fields).forEach(([key, value]) => {
    formData.append(key, String(value))
  })

  if (imageFile) {
    formData.append('image', imageFile)
  }

  return formData
}

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
    syncListing(listing) {
      if (!listing) {
        return
      }

      this.currentListing =
        this.currentListing?.listing_id === listing.listing_id ? listing : this.currentListing
      this.myListings = this.myListings.map((item) =>
        item.listing_id === listing.listing_id ? listing : item
      )
      this.listings = this.listings.map((item) =>
        item.listing_id === listing.listing_id ? listing : item
      )
    },
    async createListing({ fields, imageFile }) {
      const response = await api.post('/listings', createListingFormData(fields, imageFile))
      const listing = response.data?.data?.listing

      if (listing) {
        this.myListings.unshift(listing)
      }

      return listing
    },
    async updateListing(listingId, payload) {
      const response = await api.put(`/listings/${listingId}`, payload)
      const listing = response.data?.data?.listing

      this.syncListing(listing)

      return listing
    },
    async updateListingWithImage(listingId, { fields, imageFile, removeImage }) {
      let listing = await this.updateListing(listingId, fields)

      if (imageFile) {
        const imageData = new FormData()
        imageData.append('image', imageFile)
        const response = await api.post(`/listings/${listingId}/image`, imageData)
        listing = response.data?.data?.listing
        this.syncListing(listing)
      } else if (removeImage && listing?.image_url) {
        const response = await api.delete(`/listings/${listingId}/image`)
        listing = response.data?.data?.listing
        this.syncListing(listing)
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
