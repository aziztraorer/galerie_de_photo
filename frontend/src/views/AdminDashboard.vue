<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <main class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
      <!-- En-tête -->
      <div class="mb-10">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">
          Administration
        </p>
        <h1 class="mt-3 text-4xl font-bold text-black">
          Tableau de bord administrateur
        </h1>
        <p class="mt-4 max-w-2xl text-slate-600">
          Gérez les utilisateurs et les publications de la plateforme.
        </p>
      </div>

      <!-- Statistiques -->
      <div class="mb-10 grid gap-6 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-slate-500">Utilisateurs</h3>
            <Users class="h-5 w-5 text-brand-blue" />
          </div>
          <p class="mt-3 text-3xl font-bold text-black">{{ adminStore.users.length }}</p>
          <p class="text-sm text-slate-500">Total des utilisateurs</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-slate-500">Connectés</h3>
            <UserCheck class="h-5 w-5 text-green-500" />
          </div>
          <p class="mt-3 text-3xl font-bold text-green-600">{{ adminStore.onlineUsers }}</p>
          <p class="text-sm text-slate-500">Dernières 5 minutes</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-slate-500">Publications</h3>
            <FileText class="h-5 w-5 text-brand-blue" />
          </div>
          <p class="mt-3 text-3xl font-bold text-black">{{ adminStore.publications.length }}</p>
          <p class="text-sm text-slate-500">Total des publications</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-slate-500">Admins</h3>
            <Shield class="h-5 w-5 text-yellow-500" />
          </div>
          <p class="mt-3 text-3xl font-bold text-black">{{ adminStore.adminCount }}</p>
          <p class="text-sm text-slate-500">Administrateurs</p>
        </div>
      </div>

      <!-- Onglets -->
      <div class="mb-6 flex gap-2 border-b border-slate-200">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="px-4 py-2 text-sm font-medium transition"
          :class="
            activeTab === tab.key
              ? 'border-b-2 border-brand-blue text-brand-blue'
              : 'text-slate-500 hover:text-slate-700'
          "
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Contenu -->
      <div v-if="adminStore.loading" class="py-20 text-center text-slate-500">
        <div class="inline-block h-10 w-10 animate-spin rounded-full border-4 border-brand-blue border-t-transparent"></div>
        <p class="mt-4">Chargement...</p>
      </div>

      <template v-else>
        <!-- Utilisateurs -->
        <div v-if="activeTab === 'users'">
          <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
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
                    v-for="user in adminStore.users"
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
            </div>
          </div>
        </div>

        <!-- Publications -->
        <div v-if="activeTab === 'publications'">
          <div v-if="adminStore.publications.length === 0" class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500">
            Aucune publication trouvée.
          </div>
          <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <AdminPublicationCard
              v-for="publication in adminStore.publications"
              :key="publication.id"
              :publication="publication"
              @deleted="handlePublicationDeleted"
            />
          </div>
        </div>

        <!-- Statistiques -->
        <div v-if="activeTab === 'statistics'">
          <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <h3 class="mb-4 text-lg font-semibold text-black">Publications par utilisateur</h3>
              <div class="space-y-3">
                <div
                  v-for="stat in adminStore.userPublicationStats"
                  :key="stat.userId"
                  class="flex items-center justify-between border-b border-slate-100 pb-2 last:border-0"
                >
                  <div class="flex items-center gap-3">
                    <img
                      v-if="stat.avatar"
                      :src="getImageUrl(stat.avatar)"
                      :alt="stat.name"
                      class="h-8 w-8 rounded-full object-cover"
                    />
                    <div
                      v-else
                      class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600"
                    >
                      {{ adminStore.getUserInitials(stat.name) }}
                    </div>
                    <span class="font-medium text-black">{{ stat.name }}</span>
                  </div>
                  <span class="rounded-full bg-brand-blue px-3 py-1 text-sm font-semibold text-white">
                    {{ stat.count }}
                  </span>
                </div>
              </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <h3 class="mb-4 text-lg font-semibold text-black">Dernières publications</h3>
              <div class="space-y-3">
                <div
                  v-for="pub in adminStore.recentPublications"
                  :key="pub.id"
                  class="flex items-center justify-between border-b border-slate-100 pb-2 last:border-0"
                >
                  <div>
                    <p class="font-medium text-black">{{ pub.title }}</p>
                    <p class="text-sm text-slate-500">Par {{ pub.user_name || 'Utilisateur' }}</p>
                  </div>
                  <span class="text-xs text-slate-400">{{ formatDate(pub.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Users, UserCheck, FileText, Shield, Trash2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import AdminPublicationCard from '../components/admin/AdminPublicationCard.vue'

import { useAuthStore } from '../stores/auth'
import { useAdminStore } from '../stores/admin'
import { getImageUrl } from '../services/imageService'

const auth = useAuthStore()
const adminStore = useAdminStore()
const router = useRouter()

const activeTab = ref('users')
const tabs = [
  { key: 'users', label: 'Utilisateurs' },
  { key: 'publications', label: 'Publications' },
  { key: 'statistics', label: 'Statistiques' }
]

let refreshInterval: ReturnType<typeof setInterval> | null = null

function formatDate(date?: string): string {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

async function handleDeleteUser(user: any) {
  await adminStore.confirmDeleteUser(user)
}

function handlePublicationDeleted(id: number) {
  adminStore.removePublication(id)
}

onMounted(async () => {
  if (!auth.user) await auth.hydrate()
  
  if (auth.user?.role !== 'admin') {
    await Swal.fire({
      title: 'Accès refusé',
      text: 'Vous n\'avez pas les droits d\'administration.',
      icon: 'error'
    })
    router.push('/dashboard')
    return
  }

  await adminStore.loadAll()
  
  // Rafraîchir les utilisateurs en ligne toutes les 30 secondes
  refreshInterval = setInterval(async () => {
    await adminStore.loadOnlineUsers()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
})
</script>