<template>
  <div class="grid gap-6 md:grid-cols-2">
    <!-- Publications par utilisateur -->
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

    <!-- Dernières publications -->
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
</template>

<script setup lang="ts">
import { useAdminStore } from '../../stores/admin'
import { getImageUrl } from '../../services/imageService'

const adminStore = useAdminStore()

function formatDate(date?: string): string {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
</script>