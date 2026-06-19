<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import ListingImage from './ListingImage.vue'

const props = defineProps({
  listing: {
    type: Object,
    default: null,
  },
  categories: {
    type: Array,
    default: () => [],
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
  serverErrors: {
    type: Object,
    default: () => ({}),
  },
  submitLabel: {
    type: String,
    default: 'Save listing',
  },
})

const emit = defineEmits(['submit'])
const errors = ref({})
const imageInput = ref(null)
const selectedImage = ref(null)
const previewObjectUrl = ref('')
const removeImage = ref(false)
const form = reactive({
  title: '',
  description: '',
  price: '',
  category_id: '',
  condition_status: 'Used',
  listing_status: 'Available',
})
const previewSource = computed(() => {
  if (previewObjectUrl.value) {
    return previewObjectUrl.value
  }

  if (removeImage.value) {
    return ''
  }

  return props.listing?.image_url ?? ''
})

watch(
  () => props.listing,
  (listing) => {
    Object.assign(form, {
      title: listing?.title ?? '',
      description: listing?.description ?? '',
      price: listing?.price ?? '',
      category_id: listing?.category_id ? String(listing.category_id) : '',
      condition_status: listing?.condition_status ?? 'Used',
      listing_status: listing?.listing_status ?? 'Available',
    })
    clearSelectedImage()
    removeImage.value = false
  },
  { immediate: true }
)

watch(
  () => props.serverErrors,
  (nextErrors) => {
    errors.value = { ...nextErrors }
  },
  { deep: true }
)

function validate() {
  const nextErrors = {}
  const price = Number(form.price)

  if (!form.title.trim()) {
    nextErrors.title = 'Title is required.'
  } else if (form.title.trim().length > 150) {
    nextErrors.title = 'Title must not exceed 150 characters.'
  }

  if (!form.description.trim()) {
    nextErrors.description = 'Description is required.'
  } else if (form.description.trim().length > 5000) {
    nextErrors.description = 'Description must not exceed 5000 characters.'
  }

  if (!Number.isFinite(price) || price <= 0) {
    nextErrors.price = 'Price must be greater than zero.'
  }

  if (!form.category_id) {
    nextErrors.category_id = 'Category is required.'
  }

  if (selectedImage.value && !['image/jpeg', 'image/png', 'image/webp'].includes(selectedImage.value.type)) {
    nextErrors.image = 'Image must be a JPG, PNG, or WebP file.'
  } else if (selectedImage.value && selectedImage.value.size > 5 * 1024 * 1024) {
    nextErrors.image = 'Image size must not exceed 5 MB.'
  }

  errors.value = nextErrors
  return Object.keys(nextErrors).length === 0
}

function submit() {
  if (!validate()) {
    return
  }

  emit('submit', {
    fields: {
      title: form.title.trim(),
      description: form.description.trim(),
      price: Number(form.price),
      category_id: Number(form.category_id),
      condition_status: form.condition_status,
      listing_status: form.listing_status,
    },
    imageFile: selectedImage.value,
    removeImage: removeImage.value,
  })
}

function handleImageSelection(event) {
  const [file] = event.target.files ?? []
  errors.value = {
    ...errors.value,
    image: undefined,
  }

  if (!file) {
    clearSelectedImage()
    return
  }

  clearPreviewObjectUrl()
  selectedImage.value = null

  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    errors.value.image = 'Image must be a JPG, PNG, or WebP file.'
    event.target.value = ''
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    errors.value.image = 'Image size must not exceed 5 MB.'
    event.target.value = ''
    return
  }

  clearPreviewObjectUrl()
  selectedImage.value = file
  previewObjectUrl.value = URL.createObjectURL(file)
  removeImage.value = false
}

function clearSelectedImage() {
  clearPreviewObjectUrl()
  selectedImage.value = null

  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

function clearPreviewObjectUrl() {
  if (previewObjectUrl.value) {
    URL.revokeObjectURL(previewObjectUrl.value)
    previewObjectUrl.value = ''
  }
}

function removeCurrentImage() {
  clearSelectedImage()
  removeImage.value = true
}

function restoreCurrentImage() {
  removeImage.value = false
}

onBeforeUnmount(clearPreviewObjectUrl)
</script>

<template>
  <form class="listing-form" @submit.prevent="submit">
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="form-card">
          <div class="form-heading">
            <span class="market-kicker">Listing information</span>
            <h2>Describe your item clearly</h2>
            <p>Accurate details help other students understand what you are selling.</p>
          </div>

          <div class="d-grid gap-3">
            <div>
              <label class="form-label" for="listing-title">Item title</label>
              <input
                id="listing-title"
                v-model="form.title"
                class="form-control"
                :class="{ 'is-invalid': errors.title }"
                type="text"
                maxlength="150"
                placeholder="e.g. Engineering Mathematics textbook"
              />
              <div v-if="errors.title" class="invalid-feedback">{{ errors.title }}</div>
            </div>

            <div>
              <label class="form-label" for="listing-description">Description</label>
              <textarea
                id="listing-description"
                v-model="form.description"
                class="form-control"
                :class="{ 'is-invalid': errors.description }"
                rows="7"
                maxlength="5000"
                placeholder="Mention condition, included accessories, and useful pickup details."
              />
              <div v-if="errors.description" class="invalid-feedback">
                {{ errors.description }}
              </div>
              <div class="form-text text-end">{{ form.description.length }} / 5000</div>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="listing-price">Price (RM)</label>
                <input
                  id="listing-price"
                  v-model="form.price"
                  class="form-control"
                  :class="{ 'is-invalid': errors.price }"
                  type="number"
                  min="0.01"
                  max="99999999.99"
                  step="0.01"
                  placeholder="0.00"
                />
                <div v-if="errors.price" class="invalid-feedback">{{ errors.price }}</div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="listing-category">Category</label>
                <select
                  id="listing-category"
                  v-model="form.category_id"
                  class="form-select"
                  :class="{ 'is-invalid': errors.category_id }"
                >
                  <option value="" disabled>Select a category</option>
                  <option
                    v-for="category in categories"
                    :key="category.category_id"
                    :value="String(category.category_id)"
                  >
                    {{ category.category_name }}
                  </option>
                </select>
                <div v-if="errors.category_id" class="invalid-feedback">
                  {{ errors.category_id }}
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="listing-condition">Condition</label>
                <select
                  id="listing-condition"
                  v-model="form.condition_status"
                  class="form-select"
                  :class="{ 'is-invalid': errors.condition_status }"
                >
                  <option>New</option>
                  <option>Like New</option>
                  <option>Used</option>
                </select>
                <div v-if="errors.condition_status" class="invalid-feedback">
                  {{ errors.condition_status }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label" for="listing-state">Listing status</label>
                <select
                  id="listing-state"
                  v-model="form.listing_status"
                  class="form-select"
                  :class="{ 'is-invalid': errors.listing_status }"
                >
                  <option>Available</option>
                  <option>Reserved</option>
                  <option>Sold</option>
                </select>
                <div v-if="errors.listing_status" class="invalid-feedback">
                  {{ errors.listing_status }}
                </div>
              </div>
            </div>

            <div>
              <label class="form-label" for="listing-image">Item image (optional)</label>
              <input
                id="listing-image"
                ref="imageInput"
                class="form-control"
                :class="{ 'is-invalid': errors.image }"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="handleImageSelection"
              />
              <div v-if="errors.image" class="invalid-feedback">{{ errors.image }}</div>
              <div class="form-text">JPG, PNG, or WebP. Maximum file size: 5 MB.</div>

              <div v-if="selectedImage" class="image-selection">
                <span>{{ selectedImage.name }}</span>
                <button class="btn btn-sm btn-outline-secondary" type="button" @click="clearSelectedImage">
                  Clear selection
                </button>
              </div>

              <div v-else-if="listing?.image_url && !removeImage" class="image-selection">
                <span>Current listing image will be kept.</span>
                <button class="btn btn-sm btn-outline-danger" type="button" @click="removeCurrentImage">
                  Remove image
                </button>
              </div>

              <div v-else-if="listing?.image_url && removeImage" class="image-selection removal-note">
                <span>The current image will be removed when you save.</span>
                <button class="btn btn-sm btn-outline-secondary" type="button" @click="restoreCurrentImage">
                  Keep current image
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="preview-card">
          <span class="market-kicker">Preview</span>
          <h2>Your listing card</h2>
          <ListingImage
            class="preview-image"
            :src="previewSource"
            :alt="form.title || 'Listing preview'"
            ratio="4 / 3"
          />
          <div class="preview-copy">
            <strong>RM {{ Number(form.price || 0).toFixed(2) }}</strong>
            <h3>{{ form.title || 'Your item title' }}</h3>
            <p>{{ form.description || 'Your item description will appear here.' }}</p>
          </div>

          <button class="btn btn-primary btn-lg w-100" type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving...' : submitLabel }}
          </button>
        </div>
      </div>
    </div>
  </form>
</template>

<style scoped>
.form-card,
.preview-card {
  border: 1px solid var(--market-outline);
  border-radius: 1rem;
  background: #fff;
  box-shadow: var(--market-shadow);
}

.form-card {
  padding: clamp(1.25rem, 3vw, 2rem);
}

.preview-card {
  position: sticky;
  top: 7rem;
  padding: 1.25rem;
}

.form-heading {
  margin-bottom: 1.5rem;
}

.form-heading h2,
.preview-card h2 {
  margin: 0.55rem 0 0.35rem;
  font-size: 1.45rem;
  font-weight: 750;
}

.form-heading p {
  color: var(--market-muted);
}

.form-label {
  color: var(--market-text);
  font-size: 0.85rem;
  font-weight: 750;
}

.form-control,
.form-select {
  min-height: 2.9rem;
  border-color: var(--market-outline);
  border-radius: 0.65rem;
}

textarea.form-control {
  min-height: 10rem;
}

.form-control:focus,
.form-select:focus {
  border-color: var(--market-primary);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
}

.preview-image {
  margin: 1rem 0;
  border-radius: 0.85rem;
}

.image-selection {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 0.75rem;
  padding: 0.75rem;
  border: 1px solid var(--market-outline);
  border-radius: 0.65rem;
  background: var(--market-surface-low);
  color: var(--market-muted);
  font-size: 0.8rem;
}

.removal-note {
  border-color: rgba(186, 26, 26, 0.25);
  background: #fff7f6;
}

@media (max-width: 575.98px) {
  .image-selection {
    align-items: stretch;
    flex-direction: column;
  }
}

.preview-copy strong {
  color: var(--market-primary);
  font-size: 1.45rem;
}

.preview-copy h3 {
  margin: 0.4rem 0;
  font-size: 1.05rem;
  font-weight: 750;
}

.preview-copy p {
  min-height: 3rem;
  color: var(--market-muted);
  font-size: 0.88rem;
  line-height: 1.55;
}
</style>
