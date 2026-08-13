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

        <!-- =================================================
             PUBLIER / MODIFIER UNE ANNONCE
        ================================================== -->

        <section
          ref="formSection"
          class="scroll-mt-24 mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-blue"
            >
              <Pencil v-if="editingPublication" class="h-5 w-5" />
              <PlusCircle v-else class="h-5 w-5" />
            </div>

            <div>
              <h2 class="font-semibold text-black">
                {{
                  editingPublication
                    ? 'Modifier la publication'
                    : 'Publier une nouvelle annonce'
                }}
              </h2>

              <p class="text-sm text-slate-500">
                {{
                  editingPublication
                    ? 'Modifiez les informations de votre annonce.'
                    : 'Ajoutez un animal à adopter ou à vendre.'
                }}
              </p>
            </div>
          </div>

          <PublicationForm
            :publication="editingPublication"
            @published="handlePublished"
            @updated="handleUpdated"
            @cancel="cancelEdit"
          />
        </section>

        <!-- =================================================
             MES PUBLICATIONS
        ================================================== -->

        <section class="mt-10">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-black">
                Mes publications
              </h2>

              <p class="mt-1 text-sm text-slate-500">
                Gérez les annonces que vous avez publiées.
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
              @edit="editPublication"
              @delete="removePublication"
            />
          </div>

          <div
            v-else
            class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500"
          >
            Vous n'avez pas encore publié d'annonce.
          </div>
        </section>
      </template>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { PlusCircle, Pencil } from 'lucide-vue-next'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import PublicationCard from '../components/dashboard/PublicationCard.vue'
import PublicationForm from '../components/dashboard/PublicationForm.vue'

import { useAuthStore } from '../stores/auth'
import {
  fetchPublications,
  deletePublication as deletePublicationRequest
} from '../services/publicationService'

import type { Publication } from '../types'

const auth = useAuthStore()
const route = useRoute()

const publications = ref<Publication[]>([])
const loading = ref(true)
const favoritesCount = ref(0)

const editingPublication = ref<Publication | null>(null)
const formSection = ref<HTMLElement | null>(null)

/*
 * L'API /publications renvoie les annonces de TOUS les utilisateurs
 * (elles sont publiques sur la page "Animaux"). Ici, on ne garde que
 * celles de l'utilisateur connecté pour la gestion personnelle.
 */
async function loadDashboard(): Promise<void> {
  loading.value = true

  try {
    const response = await fetchPublications()

    if (response.success) {
      const allPublications = (
        response.data?.publications ?? []
      ).map((publication) => ({
        ...publication,
        description:
          publication.description ?? undefined,
        image_url:
          publication.image_url ?? undefined
      }))

      publications.value = auth.user
        ? allPublications.filter(
            (publication) =>
              Number(publication.user_id) ===
              Number(auth.user!.id)
          )
        : []
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

async function editPublication(publication: Publication) {
  editingPublication.value = { ...publication }

  await nextTick()

  formSection.value?.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  })
}

function cancelEdit() {
  editingPublication.value = null
}

async function handlePublished() {
  editingPublication.value = null
  await loadDashboard()
}

async function handleUpdated() {
  editingPublication.value = null
  await loadDashboard()
}

/*
 * La confirmation est deja geree dans PublicationCard.vue (etat
 * interne, sans window.confirm). Ici on ne fait plus que l'appel
 * API et la mise a jour de la liste.
 */
async function removePublication(id: number) {
  try {
    const response = await deletePublicationRequest(id)

    if (!response.success) {
      window.alert(
        response.message ||
        'Impossible de supprimer la publication.'
      )

      return
    }

    publications.value = publications.value.filter(
      (publication) => publication.id !== id
    )
  } catch (error) {
    console.error('Erreur suppression :', error)

    window.alert(
      'Une erreur est survenue lors de la suppression.'
    )
  }
}

onMounted(async () => {
  if (!auth.user) {
    await auth.hydrate()
  }

  await loadDashboard()

  /*
   * Si on arrive depuis la page "Animaux" via le bouton "Modifier"
   * (lien /dashboard?edit=ID), on ouvre directement le formulaire
   * pré-rempli avec cette annonce.
   */
  const editId = route.query.edit

  if (editId) {
    const publicationToEdit = publications.value.find(
      (publication) => String(publication.id) === String(editId)
    )

    if (publicationToEdit) {
      await editPublication(publicationToEdit)
    }
  }
})
</script>