<template>
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
                  :class="adminStore.isUserOnline(user.id) ? 'bg-green-500' : 'bg-slate-300'"
                ></span>
                {{ adminStore.isUserOnline(user.id) ? 'En ligne' : 'Hors ligne' }}
              </span>
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
</template>

<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next'

import { useAuthStore } from '../../stores/auth'
import { useAdminStore } from '../../stores/admin'
import { getImageUrl } from '../../services/imageService'

import type { User } from '../../types'

const auth = useAuthStore()
const adminStore = useAdminStore()

async function handleDeleteUser(user: User) {
  await adminStore.confirmDeleteUser(user)
}
</script>