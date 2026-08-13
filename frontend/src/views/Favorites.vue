<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
      <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">Favorites</p>
        <h1 class="mt-3 text-3xl font-semibold text-black">Your favorite animals</h1>
      </div>

      <LoadingSpinner v-if="loading" />
      <div v-else-if="error" class="rounded-3xl border border-red-200 bg-red-50 p-6 text-red-700">{{ error }}</div>
      <div v-else-if="favorites.length === 0" class="rounded-3xl border border-slate-200 bg-white p-8 text-slate-700">You have not saved any favorites yet.</div>
      <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <AnimalCard v-for="animal in favorites" :key="animal.id" :animal="animal" />
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
import AnimalCard from '../components/animals/AnimalCard.vue'
import { fetchFavorites } from '../services/favoriteService'
import type { Animal } from '../types'

const favorites = ref<Animal[]>([])
const loading = ref(false)
const error = ref('')

async function loadFavorites() {
  loading.value = true
  error.value = ''
  try {
    const response = await fetchFavorites()
    favorites.value = Array.isArray(response.data) ? (response.data as Animal[]) : []
  } catch {
    error.value = 'Unable to load favorites.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadFavorites()
})
</script>