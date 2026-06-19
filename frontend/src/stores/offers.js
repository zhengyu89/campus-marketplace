import { defineStore } from 'pinia'
import { api } from '../api/http'

function replaceOffer(collection, offer) {
  if (!offer) {
    return collection
  }

  return collection.map((item) => (item.offer_id === offer.offer_id ? offer : item))
}

export const useOffersStore = defineStore('offers', {
  state: () => ({
    sentOffers: [],
    receivedOffers: [],
    isLoadingSent: false,
    isLoadingReceived: false,
    error: '',
  }),
  actions: {
    async createOffer(listingId, payload) {
      const response = await api.post(`/listings/${listingId}/offers`, payload)
      const offer = response.data?.data?.offer

      if (offer) {
        this.sentOffers.unshift(offer)
      }

      return offer
    },
    async fetchSentOffers() {
      this.isLoadingSent = true
      this.error = ''

      try {
        const response = await api.get('/offers/mine')
        this.sentOffers = response.data?.data?.offers ?? []
        return this.sentOffers
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Unable to load your sent offers.'
        throw error
      } finally {
        this.isLoadingSent = false
      }
    },
    async fetchReceivedOffers() {
      this.isLoadingReceived = true
      this.error = ''

      try {
        const response = await api.get('/offers/received')
        this.receivedOffers = response.data?.data?.offers ?? []
        return this.receivedOffers
      } catch (error) {
        this.error = error.response?.data?.message ?? 'Unable to load received offers.'
        throw error
      } finally {
        this.isLoadingReceived = false
      }
    },
    syncOffer(offer) {
      this.sentOffers = replaceOffer(this.sentOffers, offer)
      this.receivedOffers = replaceOffer(this.receivedOffers, offer)
    },
    removeOffer(offerId) {
      this.sentOffers = this.sentOffers.filter((offer) => offer.offer_id !== offerId)
      this.receivedOffers = this.receivedOffers.filter((offer) => offer.offer_id !== offerId)
    },
    async updateOffer(offerId, payload) {
      const response = await api.put(`/offers/${offerId}`, payload)
      const offer = response.data?.data?.offer
      this.syncOffer(offer)

      return offer
    },
    async deleteOffer(offerId) {
      const response = await api.delete(`/offers/${offerId}`)
      this.removeOffer(offerId)

      return response.data?.data
    },
    async acceptOffer(offerId) {
      const response = await api.post(`/offers/${offerId}/accept`)
      const offer = response.data?.data?.offer
      this.syncOffer(offer)

      return offer
    },
    async rejectOffer(offerId) {
      const response = await api.post(`/offers/${offerId}/reject`)
      const offer = response.data?.data?.offer
      this.syncOffer(offer)

      return offer
    },
    async cancelOffer(offerId) {
      const response = await api.post(`/offers/${offerId}/cancel`)
      const offer = response.data?.data?.offer
      this.syncOffer(offer)

      return offer
    },
  },
})
