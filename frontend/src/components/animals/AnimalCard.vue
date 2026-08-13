<template>
  <article
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
  >
    <div class="h-48 overflow-hidden bg-slate-100">
      <img
        v-if="animal.image_url"
        :src="getImageUrl(animal.image_url)"
        :alt="animal.name"
        class="h-full w-full object-cover transition duration-300 hover:scale-105"
        loading="lazy"
      />

      <div
        v-else
        class="flex h-full items-center justify-center text-sm text-slate-400"
      >
        Aucune image
      </div>
    </div>

    <div class="space-y-3 p-6">
      <div>
        <p
          v-if="animal.category?.name || animal.category_name"
          class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-blue"
        >
          {{ animal.category?.name || animal.category_name }}
        </p>

        <h3 class="text-xl font-semibold text-black">
          {{ animal.name }}
        </h3>

        <p
          v-if="animal.scientific_name"
          class="text-sm italic text-slate-600"
        >
          {{ animal.scientific_name }}
        </p>
      </div>

      <p
        v-if="animal.short_description"
        class="text-sm leading-7 text-slate-700"
      >
        {{ animal.short_description }}
      </p>

      <p
        v-else
        class="text-sm text-slate-400"
      >
        Aucune description disponible.
      </p>

      <div class="flex items-center justify-between pt-2">
        <router-link
          :to="`/animals/${animal.id}`"
          class="rounded-full bg-brand-blue px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
        >
          Voir détails
        </router-link>

        <button
          type="button"
          :disabled="loading"
          :aria-label="
            isFavorite
              ? 'Retirer des favoris'
              : 'Ajouter aux favoris'
          "
          class="rounded-full border p-2 transition disabled:cursor-not-allowed disabled:opacity-50"
          :class="
            isFavorite
              ? 'border-red-200 bg-red-50 text-red-500'
              : 'border-slate-200 text-slate-600 hover:bg-slate-100'
          "
          @click="handleFavorite"
        >
          <Heart
            class="h-5 w-5"
            :fill="isFavorite ? 'currentColor' : 'none'"
          />
        </button>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Heart } from 'lucide-vue-next'

import { useAuthStore } from '../../stores/auth'
import { toggleFavorite } from '../../services/favoriteService'

import type { Animal } from '../../types'

const props = defineProps<{
  animal: Animal
}>()

const auth = useAuthStore()

const isFavorite = ref(false)
const loading = ref(false)

function getImageUrl(path: string): string {
  if (
    path.startsWith('http://') ||
    path.startsWith('https://')
  ) {
    return path
  }

  return `http://localhost:8000${
    path.startsWith('/') ? path : `/${path}`
  }`
}

async function handleFavorite(): Promise<void> {
  if (!auth.isAuthenticated) {
    return
  }

  if (loading.value) {
    return
  }

  loading.value = true

  try {
    const response = await toggleFavorite(
      props.animal.id,
      isFavorite.value
    )

    if (response.success) {
      isFavorite.value = !isFavorite.value
    }
  } catch (error) {
    console.error(
      'Erreur lors de la modification du favori :',
      error
    )
  } finally {
    loading.value = false
  }
}
</script>