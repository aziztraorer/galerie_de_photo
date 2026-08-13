<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
      <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">Categories</p>
        <h1 class="text-3xl font-semibold text-black">Explore animal categories</h1>
      </div>

      <LoadingSpinner v-if="loading" />
      <div v-else-if="error" class="rounded-3xl border border-red-200 bg-red-50 p-6 text-red-700">{{ error }}</div>
      <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <CategoryCard v-for="category in categories" :key="category.id" :category="category" />
      </div>
    </div>
    <Footer />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import LoadingSpinner from '../components/common/LoadingSpinner.vue'
import CategoryCard from '../components/categories/CategoryCard.vue'
import { fetchCategories } from '../services/animalService'
import type { Category } from '../types'

const categories = ref<Category[]>([])
const loading = ref(false)
const error = ref('')

async function loadCategories() {
  loading.value = true
  error.value = ''
  try {
    const response = await fetchCategories()
    categories.value = response.data ?? []
  } catch {
    error.value = 'Unable to load categories. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadCategories()
})
</script>
