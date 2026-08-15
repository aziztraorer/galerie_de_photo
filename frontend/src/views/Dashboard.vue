<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <main class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
      <div class="mb-10">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">
          Dashboard
        </p>
        <h1 class="mt-3 text-4xl font-bold text-black">
          Welcome to your dashboard
        </h1>
        <p class="mt-4 max-w-2xl text-slate-600">
          Manage your publications, favorites and account.
        </p>
      </div>

      <div v-if="loading" class="py-20 text-center text-slate-500">
        <div class="inline-block h-10 w-10 animate-spin rounded-full border-4 border-brand-blue border-t-transparent"></div>
        <p class="mt-4">Chargement...</p>
      </div>

      <template v-else>
        <!-- Statistiques utilisateur -->
        <div class="grid gap-6 md:grid-cols-3">
          <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-black">Publications</h2>
              <FileText class="h-5 w-5 text-brand-blue" />
            </div>
            <p class="mt-3 text-3xl font-bold text-brand-blue">{{ publications.length }}</p>
          </div>

          <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-black">Favorites</h2>
              <Heart class="h-5 w-5 text-red-500" />
            </div>
            <p class="mt-3 text-3xl font-bold text-brand-blue">{{ favoritesCount }}</p>
            <router-link to="/favorites" class="mt-5 inline-block text-sm font-medium text-brand-blue hover:underline">
              View favorites
            </router-link>
          </div>

          <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-black">Account</h2>
              <User class="h-5 w-5 text-slate-400" />
            </div>
            <p v-if="auth.user" class="mt-3 text-slate-600">{{ auth.user.name }}</p>
            <router-link to="/profile" class="mt-5 inline-block text-sm font-medium text-brand-blue hover:underline">
              View profile
            </router-link>
          </div>
        </div>

        <!-- Section Administration - UNIQUEMENT pour les administrateurs -->
        <template v-if="auth.user?.role === 'admin'">
          <div class="mt-10">
            <div class="mb-6 flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold text-black">Administration</h2>
                <p class="mt-1 text-sm text-slate-500">Gestion des utilisateurs et publications</p>
              </div>
              <router-link
                to="/admin"
                class="rounded-full bg-brand-blue px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
              >
                Voir tout
              </router-link>
            </div>

            <div v-if="adminStore.error" class="mb-4 rounded-lg bg-red-50 p-4 text-red-700 border border-red-200">
              <p class="font-medium">⚠️ {{ adminStore.error }}</p>
            </div>

            <!-- Stats Admin -->
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
              <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-500">Utilisateurs</span>
                  <Users class="h-5 w-5 text-brand-blue" />
                </div>
                <p class="mt-2 text-2xl font-bold text-black">{{ adminStore.totalUsers }}</p>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-500">Connectés</span>
                  <UserCheck class="h-5 w-5 text-green-500" />
                </div>
                <p class="mt-2 text-2xl font-bold text-green-600">{{ adminStore.onlineUsers }}</p>
                <p class="text-xs text-slate-400">Dernières 5 minutes</p>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-500">Publications</span>
                  <FileText class="h-5 w-5 text-brand-blue" />
                </div>
                <p class="mt-2 text-2xl font-bold text-black">{{ adminStore.publications.length }}</p>
              </div>
            </div>

            <!-- Tableau des utilisateurs -->
            <div v-if="!adminStore.error && adminStore.users.length > 0" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Utilisateur</th>
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Email</th>
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Rôle</th>
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Publications</th>
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Dernière activité</th>
                      <th class="px-4 py-3 text-left font-semibold text-slate-700">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="user in adminStore.users.slice(0, 5)"
                      :key="user.id"
                      class="border-b border-slate-100 last:border-0 hover:bg-slate-50"
                    >
                      <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                          <img
                            v-if="user.avatar_url"
                            :src="getImageUrl(user.avatar_url)"
                            :alt="user.name"
                            class="h-8 w-8 rounded-full object-cover"
                          />
                          <div
                            v-else
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600"
                          >
                            {{ adminStore.getUserInitials(user.name) }}
                          </div>
                          <span class="font-medium text-black">{{ user.name }}</span>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-slate-600">{{ user.email }}</td>
                      <td class="px-4 py-3">
                        <span
                          :class="
                            user.role === 'admin'
                              ? 'rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-700'
                              : 'rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700'
                          "
                        >
                          {{ user.role || 'Utilisateur' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-brand-blue">
                          {{ adminStore.getUserPublicationCount(user.id) }}
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <span
                          :class="
                            adminStore.isUserOnline(user.id)
                              ? 'flex items-center gap-1 text-green-600'
                              : 'flex items-center gap-1 text-slate-400'
                          "
                        >
                          <span
                            class="inline-block h-2 w-2 rounded-full"
                            :class="adminStore.isUserOnline(user.id) ? 'bg-green-500 animate-pulse' : 'bg-slate-300'"
                          ></span>
                          {{ adminStore.isUserOnline(user.id) ? 'En ligne' : 'Hors ligne' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-xs text-slate-500">
                        {{ adminStore.getLastActivity(user) }}
                      </td>
                      <td class="px-4 py-3">
                        <button
                          v-if="user.id !== auth.user?.id && user.role !== 'admin'"
                          type="button"
                          :disabled="adminStore.deletingUser === user.id"
                          class="rounded-full bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50"
                          @click="handleDeleteUser(user)"
                        >
                          <Trash2 class="inline h-4 w-4" />
                          {{ adminStore.deletingUser === user.id ? '...' : 'Supprimer' }}
                        </button>
                        <span v-else-if="user.id === auth.user?.id" class="text-xs text-slate-400">
                          (Vous)
                        </span>
                        <span v-else-if="user.role === 'admin'" class="text-xs text-slate-400">
                          Admin
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="adminStore.users.length > 5" class="border-t border-slate-200 p-4 text-center">
                  <router-link to="/admin" class="text-sm font-medium text-brand-blue hover:underline">
                    Voir tous les {{ adminStore.users.length }} utilisateurs
                  </router-link>
                </div>
              </div>
            </div>

            <div v-else-if="adminStore.users.length === 0 && !adminStore.error" class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500">
              Aucun utilisateur trouvé dans la base de données.
            </div>
          </div>
        </template>

        <!-- Formulaire Publication -->
        <section ref="formSection" class="scroll-mt-24 mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-blue">
              <Pencil v-if="editingPublication" class="h-5 w-5" />
              <PlusCircle v-else class="h-5 w-5" />
            </div>
            <div>
              <h2 class="font-semibold text-black">
                {{ editingPublication ? 'Modifier la publication' : 'Publier une nouvelle annonce' }}
              </h2>
              <p class="text-sm text-slate-500">
                {{ editingPublication ? 'Modifiez les informations de votre annonce.' : 'Ajoutez un animal à adopter ou à vendre.' }}
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

        <!-- Mes Publications -->
        <section class="mt-10">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-black">Mes publications</h2>
              <p class="mt-1 text-sm text-slate-500">Gérez les annonces que vous avez publiées.</p>
            </div>
          </div>

          <div v-if="publications.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <PublicationCard
              v-for="publication in publications"
              :key="publication.id"
              :publication="publication"
              @edit="editPublication"
              @deleted="removePublicationFromList"
            />
          </div>

          <div v-else class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500">
            Vous n'avez pas encore publié d'annonce.
          </div>
        </section>

        <!-- Mes Favoris -->
        <section class="mt-10">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-black">Mes favoris</h2>
              <p class="mt-1 text-sm text-slate-500">Les animaux que vous avez ajoutés à vos favoris.</p>
            </div>
          </div>

          <div v-if="favorites.length > 0" class="flex flex-wrap gap-3">
            <router-link
              v-for="animal in favorites"
              :key="animal.id"
              :to="`/animals/${animal.id}`"
              class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-blue hover:text-brand-blue"
            >
              {{ animal.name }}
            </router-link>
          </div>

          <div v-else class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500">
            Vous n'avez pas encore ajouté de favoris.
          </div>
        </section>
      </template>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, ref, onUnmounted, onActivated, onDeactivated } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { PlusCircle, Pencil, Users, UserCheck, FileText, Heart, User, Trash2 } from 'lucide-vue-next'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import PublicationCard from '../components/dashboard/PublicationCard.vue'
import PublicationForm from '../components/dashboard/PublicationForm.vue'

import { useAuthStore } from '../stores/auth'
import { useAdminStore } from '../stores/admin'
import { fetchPublications } from '../services/publicationService'
import { fetchFavorites } from '../services/favoriteService'
import { getImageUrl } from '../services/imageService'

import type { Publication, Animal } from '../types'

const auth = useAuthStore()
const adminStore = useAdminStore()
const router = useRouter()
const route = useRoute()

const publications = ref<Publication[]>([])
const loading = ref(true)
const favorites = ref<Animal[]>([])
const favoritesCount = ref(0)

const editingPublication = ref<Publication | null>(null)
const formSection = ref<HTMLElement | null>(null)

// Variables pour le rafraîchissement automatique
let refreshInterval: ReturnType<typeof setInterval> | null = null
let adminRefreshInterval: ReturnType<typeof setInterval> | null = null
const REFRESH_INTERVAL_MS = 20000 // 20 secondes

async function loadDashboard(): Promise<void> {
  loading.value = true

  try {
    const [pubResponse, favResponse] = await Promise.all([
      fetchPublications(),
      fetchFavorites()
    ])

    if (pubResponse.success) {
      const allPublications = (pubResponse.data?.publications ?? []).map((publication) => ({
        ...publication,
        description: publication.description ?? undefined,
        image_url: publication.image_url ?? undefined
      }))

      publications.value = auth.user
        ? allPublications.filter((p) => Number(p.user_id) === Number(auth.user!.id))
        : []
    }

    if (favResponse.success) {
      favorites.value = Array.isArray(favResponse.data) ? (favResponse.data as Animal[]) : []
      favoritesCount.value = favorites.value.length
    }
  } catch (error) {
    // Erreur silencieuse
  } finally {
    loading.value = false
  }
}

// Fonction pour rafraîchir les données sans recharger la page
async function refreshData(): Promise<void> {
  // Sauvegarder la position de scroll
  const scrollY = window.scrollY
  
  try {
    // Rafraîchir les données utilisateur
    const [pubResponse, favResponse] = await Promise.all([
      fetchPublications(),
      fetchFavorites()
    ])

    if (pubResponse.success) {
      const allPublications = (pubResponse.data?.publications ?? []).map((publication) => ({
        ...publication,
        description: publication.description ?? undefined,
        image_url: publication.image_url ?? undefined
      }))

      publications.value = auth.user
        ? allPublications.filter((p) => Number(p.user_id) === Number(auth.user!.id))
        : []
    }

    if (favResponse.success) {
      favorites.value = Array.isArray(favResponse.data) ? (favResponse.data as Animal[]) : []
      favoritesCount.value = favorites.value.length
    }

    // Rafraîchir les données admin si l'utilisateur est admin
    if (auth.user?.role === 'admin') {
      await Promise.all([
        adminStore.loadUsers(),
        adminStore.loadOnlineUsers(),
        adminStore.loadPublications()
      ])
    }
  } catch (error) {
    // Erreur silencieuse
  } finally {
    // Restaurer la position de scroll
    window.scrollTo(0, scrollY)
  }
}

async function editPublication(publication: Publication) {
  editingPublication.value = { ...publication }
  await nextTick()
  formSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function cancelEdit() {
  editingPublication.value = null
}

async function handlePublished() {
  editingPublication.value = null
  await loadDashboard()
  if (auth.user?.role === 'admin') {
    await adminStore.loadPublications()
  }
}

async function handleUpdated() {
  editingPublication.value = null
  await loadDashboard()
  if (auth.user?.role === 'admin') {
    await adminStore.loadPublications()
  }
}

function removePublicationFromList(id: number) {
  publications.value = publications.value.filter((p) => p.id !== id)
}

async function handleDeleteUser(user: any) {
  await adminStore.confirmDeleteUser(user)
  if (auth.user?.role === 'admin') {
    await adminStore.loadUsers()
    await adminStore.loadOnlineUsers()
  }
}

// Démarrer le rafraîchissement automatique
function startAutoRefresh(): void {
  // Arrêter les intervalles existants
  stopAutoRefresh()

  // Rafraîchir toutes les 20 secondes
  refreshInterval = setInterval(() => {
    refreshData()
  }, REFRESH_INTERVAL_MS)

  // Rafraîchir les données admin toutes les 20 secondes également
  if (auth.user?.role === 'admin') {
    adminRefreshInterval = setInterval(() => {
      adminStore.loadOnlineUsers()
    }, REFRESH_INTERVAL_MS)
  }
}

// Arrêter le rafraîchissement automatique
function stopAutoRefresh(): void {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
  if (adminRefreshInterval) {
    clearInterval(adminRefreshInterval)
    adminRefreshInterval = null
  }
}

onMounted(async () => {
  if (!auth.user) {
    await auth.hydrate()
  }
  
  if (!auth.user) {
    router.push('/login')
    return
  }
  
  await loadDashboard()

  if (auth.user?.role === 'admin') {
    await adminStore.loadAll()
  }

  const editId = route.query.edit
  if (editId) {
    const publicationToEdit = publications.value.find(
      (p) => String(p.id) === String(editId)
    )
    if (publicationToEdit) {
      await editPublication(publicationToEdit)
    }
  }

  // Démarrer le rafraîchissement automatique après le chargement initial
  startAutoRefresh()
})

// Gérer le cas où le composant est activé (keep-alive)
onActivated(() => {
  startAutoRefresh()
})

// Gérer le cas où le composant est désactivé (keep-alive)
onDeactivated(() => {
  stopAutoRefresh()
})

onUnmounted(() => {
  stopAutoRefresh()
})
</script>