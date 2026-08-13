<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <main
      class="mx-auto max-w-7xl px-6 py-16 lg:px-8"
    >
      <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
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
            Explore the animals available in our database, ainsi que les
            annonces publiées par la communauté.
          </p>
        </div>

        <router-link
          v-if="auth.isAuthenticated"
          to="/dashboard"
          class="rounded-full bg-brand-blue px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-700"
        >
          + Publier une annonce
        </router-link>
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
        v-else-if="items.length"
        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
      >
        <template
          v-for="item in items"
          :key="`${item.type}-${item.data.id}`"
        >
          <AnimalCard
            v-if="item.type === 'animal'"
            :animal="item.data"
          />

          <PublicationAdCard
            v-else
            :publication="item.data"
            @edit="goToEditPublication"
            @deleted="handlePublicationDeleted"
          />
        </template>
      </div>

      <div
        v-else
        class="rounded-3xl bg-white p-12 text-center text-slate-500"
      >
        Aucun animal ni annonce disponible pour le moment.
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import AnimalCard from '../components/animals/AnimalCard.vue'
import PublicationAdCard from '../components/dashboard/PublicationCard.vue'

import { useAuthStore } from '../stores/auth'

import {
  fetchAnimals
} from '../services/animalService'

import {
  fetchPublications
} from '../services/publicationService'

import type { Animal, Publication } from '../types'

const auth = useAuthStore()
const router = useRouter()

const animals = ref<Animal[]>([])
const publications = ref<Publication[]>([])

const loading = ref(true)
const error = ref('')

/*
 * Les animaux "officiels" de la base et les annonces publiées par les
 * utilisateurs sont fusionnées dans une seule liste affichée dans la
 * même grille : les annonces les plus récentes en premier, suivies
 * des animaux existants.
 */
const items = computed(() => {
  const publicationItems = publications.value.map(
    (publication) => ({
      type: 'publication' as const,
      data: publication
    })
  )

  const animalItems = animals.value.map((animal) => ({
    type: 'animal' as const,
    data: animal
  }))

  return [...publicationItems, ...animalItems]
})

async function loadAnimals() {
  try {
    const response = await fetchAnimals()

    if (response.success) {
      animals.value = response.data?.animals ?? []
    } else {
      error.value =
        response.message ||
        'Impossible de charger les animaux.'
    }
  } catch (err) {
    console.error(err)

    error.value =
      'Impossible de contacter le serveur.'
  }
}

async function loadPublications() {
  try {
    const response = await fetchPublications()

    if (response.success) {
      publications.value =
        response.data?.publications ?? []
    }
  } catch (err) {
    console.error(
      'Erreur chargement des annonces :',
      err
    )
  }
}

/*
 * "Modifier" depuis la page Animaux : on renvoie l'utilisateur vers le
 * dashboard, qui lira le paramètre "edit" dans l'URL au montage et
 * ouvrira automatiquement le formulaire pré-rempli avec cette annonce.
 */
function goToEditPublication(publication: Publication) {
  router.push({
    path: '/dashboard',
    query: { edit: publication.id }
  })
}

/*
 * La confirmation, l'appel API et le message de succes/erreur sont
 * geres directement dans PublicationCard.vue via SweetAlert2. Ici on
 * ne fait plus que retirer la publication de la liste locale une fois
 * que la suppression cote serveur a reussi.
 */
function handlePublicationDeleted(id: number) {
  publications.value = publications.value.filter(
    (publication) => publication.id !== id
  )
}

async function loadAll() {
  loading.value = true
  error.value = ''

  await Promise.all([
    loadAnimals(),
    loadPublications()
  ])

  loading.value = false
}

onMounted(() => {
  loadAll()
})
</script>