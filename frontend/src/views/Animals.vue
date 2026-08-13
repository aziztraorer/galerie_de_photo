<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <main
      class="mx-auto max-w-7xl px-6 py-16 lg:px-8"
    >
      <div class="mb-10">
        <p
          class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue"
        >
          Animals
        </p>

        <h1
          class="mt-3 text-4xl font-bold text-black"
        >
          Discover animals
        </h1>

        <p
          class="mt-4 max-w-2xl text-slate-600"
        >
          Explore the animals available in our database.
        </p>
      </div>

      <div
        v-if="loading"
        class="py-20 text-center text-slate-500"
      >
        Chargement des animaux...
      </div>

      <div
        v-else-if="error"
        class="rounded-2xl bg-red-50 p-6 text-red-600"
      >
        {{ error }}
      </div>

      <div
        v-else-if="animals.length"
        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
      >
        <AnimalCard
          v-for="animal in animals"
          :key="animal.id"
          :animal="animal"
        />
      </div>

      <div
        v-else
        class="rounded-3xl bg-white p-12 text-center text-slate-500"
      >
        Aucun animal disponible.
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import AnimalCard from '../components/animals/AnimalCard.vue'

import {
  fetchAnimals
} from '../services/animalService'

import type { Animal } from '../types'

const animals = ref<Animal[]>([])
const loading = ref(true)
const error = ref('')

async function loadAnimals() {
  loading.value = true
  error.value = ''

  try {
    const response =
      await fetchAnimals()

    if (response.success) {
      animals.value =
        response.data?.animals ?? []
    } else {
      error.value =
        response.message ||
        'Impossible de charger les animaux.'
    }
  } catch (err) {
    console.error(err)

    error.value =
      'Impossible de contacter le serveur.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAnimals()
})
</script>