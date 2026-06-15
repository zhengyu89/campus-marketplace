<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../api/http'

const categories = ref([])
const newCategoryName = ref('')
const editingCategoryId = ref(null)
const editingCategoryName = ref('')
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const sortedCategories = computed(() =>
  [...categories.value].sort((a, b) => a.category_name.localeCompare(b.category_name))
)

function getApiError(error, fallbackMessage) {
  return error.response?.data?.message || fallbackMessage
}

function clearMessages() {
  errorMessage.value = ''
  successMessage.value = ''
}

async function fetchCategories() {
  isLoading.value = true
  clearMessages()

  try {
    const response = await api.get('/categories')
    categories.value = response.data?.data?.categories ?? []
  } catch (error) {
    errorMessage.value = getApiError(error, 'Unable to load categories.')
  } finally {
    isLoading.value = false
  }
}

async function createCategory() {
  const categoryName = newCategoryName.value.trim()

  if (!categoryName) {
    errorMessage.value = 'Category name is required.'
    return
  }

  isSaving.value = true
  clearMessages()

  try {
    await api.post('/categories', {
      category_name: categoryName,
    })
    newCategoryName.value = ''
    await fetchCategories()
    successMessage.value = 'Category created successfully.'
  } catch (error) {
    errorMessage.value = getApiError(error, 'Unable to create category.')
  } finally {
    isSaving.value = false
  }
}

function startEditing(category) {
  editingCategoryId.value = category.category_id
  editingCategoryName.value = category.category_name
  clearMessages()
}

function cancelEditing() {
  editingCategoryId.value = null
  editingCategoryName.value = ''
}

async function updateCategory(category) {
  const categoryName = editingCategoryName.value.trim()

  if (!categoryName) {
    errorMessage.value = 'Category name is required.'
    return
  }

  isSaving.value = true
  clearMessages()

  try {
    await api.put(`/categories/${category.category_id}`, {
      category_name: categoryName,
    })
    cancelEditing()
    await fetchCategories()
    successMessage.value = 'Category updated successfully.'
  } catch (error) {
    errorMessage.value = getApiError(error, 'Unable to update category.')
  } finally {
    isSaving.value = false
  }
}

async function deleteCategory(category) {
  const confirmed = window.confirm(`Delete "${category.category_name}"?`)

  if (!confirmed) {
    return
  }

  isSaving.value = true
  clearMessages()

  try {
    await api.delete(`/categories/${category.category_id}`)
    await fetchCategories()
    successMessage.value = 'Category deleted successfully.'
  } catch (error) {
    errorMessage.value = getApiError(error, 'Unable to delete category.')
  } finally {
    isSaving.value = false
  }
}

onMounted(fetchCategories)
</script>

<template>
  <main class="admin-categories-page">
    <section class="admin-hero">
      <div class="container-xl">
        <span class="admin-badge">Admin module</span>
        <h1>Category Management</h1>
        <p>
          Manage the item categories used to organize UTM MarketHub listings. Category names are
          stored in MySQL through the PHP Slim REST API.
        </p>
      </div>
    </section>

    <section class="container-xl pb-5">
      <div class="admin-panel">
        <div class="panel-heading">
          <div>
            <h2>Marketplace Categories</h2>
            <p>{{ categories.length }} categories available for listings.</p>
          </div>
          <button class="btn btn-outline-primary" type="button" :disabled="isLoading" @click="fetchCategories">
            Refresh
          </button>
        </div>

        <form class="category-form" @submit.prevent="createCategory">
          <label class="form-label" for="category-name">New category name</label>
          <div class="input-group">
            <input
              id="category-name"
              v-model="newCategoryName"
              class="form-control"
              type="text"
              maxlength="100"
              placeholder="Example: Transport"
              :disabled="isSaving"
            />
            <button class="btn btn-primary" type="submit" :disabled="isSaving">
              Add Category
            </button>
          </div>
        </form>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="alert alert-success" role="status">
          {{ successMessage }}
        </div>

        <div v-if="isLoading" class="loading-state">
          Loading categories...
        </div>

        <div v-else-if="sortedCategories.length" class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Category Name</th>
                <th scope="col">Created At</th>
                <th class="text-end" scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in sortedCategories" :key="category.category_id">
                <td class="text-muted">#{{ category.category_id }}</td>
                <td>
                  <input
                    v-if="editingCategoryId === category.category_id"
                    v-model="editingCategoryName"
                    class="form-control"
                    type="text"
                    maxlength="100"
                    :disabled="isSaving"
                    @keyup.enter="updateCategory(category)"
                    @keyup.esc="cancelEditing"
                  />
                  <strong v-else>{{ category.category_name }}</strong>
                </td>
                <td class="text-muted">{{ category.created_at }}</td>
                <td class="text-end">
                  <div v-if="editingCategoryId === category.category_id" class="btn-group btn-group-sm">
                    <button class="btn btn-primary" type="button" :disabled="isSaving" @click="updateCategory(category)">
                      Save
                    </button>
                    <button class="btn btn-outline-secondary" type="button" :disabled="isSaving" @click="cancelEditing">
                      Cancel
                    </button>
                  </div>
                  <div v-else class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" type="button" :disabled="isSaving" @click="startEditing(category)">
                      Edit
                    </button>
                    <button class="btn btn-outline-danger" type="button" :disabled="isSaving" @click="deleteCategory(category)">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="empty-state">
          <h2>No categories yet</h2>
          <p>Add the first marketplace category to prepare the database for listing creation.</p>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.admin-categories-page {
  min-height: calc(100vh - 5rem);
  color: #12304d;
}

.admin-hero {
  padding: 3rem 0 2rem;
}

.admin-badge {
  display: inline-flex;
  border-radius: 999px;
  padding: 0.45rem 0.85rem;
  background: rgba(13, 91, 215, 0.12);
  color: #0d5bd7;
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.admin-hero h1 {
  margin: 1rem 0 0.75rem;
  color: #081d35;
  font-size: clamp(2.3rem, 5vw, 3.8rem);
  font-weight: 800;
  letter-spacing: -0.03em;
}

.admin-hero p {
  max-width: 48rem;
  color: #607894;
  font-size: 1.05rem;
  line-height: 1.8;
}

.admin-panel {
  padding: 1.5rem;
  border: 1px solid rgba(116, 137, 170, 0.18);
  border-radius: 1rem;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 18px 50px rgba(11, 36, 61, 0.08);
  text-align: left;
}

.panel-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.panel-heading h2 {
  margin: 0 0 0.35rem;
  color: #102a45;
  font-size: 1.35rem;
  font-weight: 800;
}

.panel-heading p {
  color: #607894;
}

.category-form {
  margin-bottom: 1.25rem;
}

.loading-state,
.empty-state {
  padding: 2rem;
  border: 1px dashed rgba(116, 137, 170, 0.32);
  border-radius: 0.85rem;
  background: rgba(244, 248, 255, 0.72);
  text-align: center;
}

.empty-state h2 {
  margin-bottom: 0.5rem;
  font-size: 1.35rem;
  font-weight: 800;
}

.table {
  margin-bottom: 0;
}

@media (max-width: 767.98px) {
  .panel-heading {
    flex-direction: column;
  }

  .category-form .input-group {
    display: grid;
    gap: 0.75rem;
  }

  .category-form .btn {
    width: 100%;
  }
}
</style>
