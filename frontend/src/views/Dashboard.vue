<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <main class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
      <div class="mb-10">
        <p
          class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue"
        >
          Dashboard
        </p>

        <h1 class="mt-3 text-4xl font-bold text-black">
          Welcome to your dashboard
        </h1>

        <p class="mt-4 max-w-2xl text-slate-600">
          Manage your publications, favorites and account.
        </p>
      </div>

      <div
        v-if="loading"
        class="py-20 text-center text-slate-500"
      >
        Chargement...
      </div>

      <template v-else>
        <div class="grid gap-6 md:grid-cols-3">
          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
          >
            <h2 class="text-lg font-semibold text-black">
              Publications
            </h2>

            <p class="mt-3 text-3xl font-bold text-brand-blue">
              {{ publications.length }}
            </p>
          </div>

          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
          >
            <h2 class="text-lg font-semibold text-black">
              Favorites
            </h2>

            <p class="mt-3 text-3xl font-bold text-brand-blue">
              {{ favoritesCount }}
            </p>

            <router-link
              to="/favorites"
              class="mt-5 inline-block text-sm font-medium text-brand-blue hover:underline"
            >
              View favorites
            </router-link>
          </div>

          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
          >
            <h2 class="text-lg font-semibold text-black">
              Account
            </h2>

            <p
              v-if="auth.user"
              class="mt-3 text-slate-600"
            >
              {{ auth.user.name }}
            </p>

            <router-link
              to="/profile"
              class="mt-5 inline-block text-sm font-medium text-brand-blue hover:underline"
            >
              View profile
            </router-link>
          </div>
        </div>

        <section class="mt-10">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-black">
                Recent publications
              </h2>

              <p class="mt-1 text-sm text-slate-500">
                Your latest publications.
              </p>
            </div>
          </div>

          <div
            v-if="publications.length > 0"
            class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
          >
            <PublicationCard
              v-for="publication in publications"
              :key="publication.id"
              :publication="publication"
            />
          </div>

          <div
            v-else
            class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500"
          >
            Aucune publication disponible.
          </div>
        </section>
      </template>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import PublicationCard from '../components/dashboard/PublicationCard.vue'

import { useAuthStore } from '../stores/auth'
import { fetchPublications } from '../services/publicationService'

import type { Publication } from '../types'

const auth = useAuthStore()

const publications = ref<Publication[]>([])
const loading = ref(true)
const favoritesCount = ref(0)

async function loadDashboard(): Promise<void> {
  loading.value = true

  try {
    const response = await fetchPublications()

    if (response.success) {
      publications.value = (
        response.data?.publications ?? []
      ).map((publication) => ({
        ...publication,
        description:
          publication.description ?? undefined,
        image_url:
          publication.image_url ?? undefined
      }))
    }
  } catch (error) {
    console.error(
      'Erreur lors du chargement du dashboard :',
      error
    )
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (!auth.user) {
    await auth.hydrate()
  }

  await loadDashboard()
})
</script>