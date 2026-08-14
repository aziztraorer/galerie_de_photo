<template>
  <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
    <div
      class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8"
    >
      <!-- Logo / Nom -->
      <router-link
        :to="auth.isAuthenticated ? '/animals' : '/'"
        class="text-lg font-semibold tracking-tight text-black"
      >
        AzizDev Animals
      </router-link>

      <!-- Menu Desktop -->
      <nav
        class="hidden items-center gap-6 text-sm font-medium text-slate-700 lg:flex"
      >
        <router-link
          v-if="!auth.isAuthenticated"
          to="/"
          class="transition hover:text-brand-blue"
          exact-active-class="text-brand-blue"
        >
          Home
        </router-link>

        <router-link
          to="/animals"
          class="transition hover:text-brand-blue"
          active-class="text-brand-blue"
        >
          Animals
        </router-link>

        <router-link
          to="/categories"
          class="transition hover:text-brand-blue"
          active-class="text-brand-blue"
        >
          Categories
        </router-link>

        <!-- Utilisateur connecté -->
        <template v-if="auth.isAuthenticated">
          <router-link
            to="/dashboard"
            class="transition hover:text-brand-blue"
            active-class="text-brand-blue"
          >
            Dashboard
          </router-link>

          <router-link
            to="/favorites"
            class="transition hover:text-brand-blue"
            active-class="text-brand-blue"
          >
            Favorites
          </router-link>

          <!-- Avatar cliquable vers le profil -->
          <router-link
            to="/profile"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border-2 border-brand-blue transition hover:border-blue-700"
            active-class="border-blue-700"
          >
            <img
              v-if="auth.user?.avatar_url"
              :src="getImageUrl(auth.user.avatar_url)"
              :alt="auth.user.name"
              class="h-full w-full rounded-full object-cover"
            />
            <span
              v-else
              class="text-sm font-semibold text-brand-blue"
            >
              {{ initials }}
            </span>
          </router-link>
        </template>

        <!-- Utilisateur non connecté -->
        <template v-else>
          <router-link
            to="/login"
            class="transition hover:text-brand-blue"
            active-class="text-brand-blue"
          >
            Login
          </router-link>

          <router-link
            to="/register"
            class="rounded-full bg-brand-blue px-4 py-2 text-white transition hover:bg-blue-700"
            active-class="bg-blue-700"
          >
            Register
          </router-link>
        </template>
      </nav>

      <!-- Bouton menu mobile -->
      <button
        type="button"
        class="rounded-full border border-slate-200 p-2 text-slate-700 lg:hidden"
        @click="isOpen = !isOpen"
        aria-label="Toggle menu"
      >
        <Menu class="h-5 w-5" />
      </button>
    </div>

    <!-- Menu Mobile -->
    <div
      v-if="isOpen"
      class="border-t border-slate-200 bg-white px-6 py-4 lg:hidden"
    >
      <div class="flex flex-col gap-3 text-sm font-medium text-slate-700">
        <router-link
          v-if="!auth.isAuthenticated"
          to="/"
          @click="isOpen = false"
        >
          Home
        </router-link>

        <router-link
          to="/animals"
          @click="isOpen = false"
        >
          Animals
        </router-link>

        <router-link
          to="/categories"
          @click="isOpen = false"
        >
          Categories
        </router-link>

        <!-- Utilisateur connecté -->
        <template v-if="auth.isAuthenticated">
          <router-link
            to="/dashboard"
            @click="isOpen = false"
          >
            Dashboard
          </router-link>

          <router-link
            to="/favorites"
            @click="isOpen = false"
          >
            Favorites
          </router-link>

          <router-link
            to="/profile"
            @click="isOpen = false"
            class="flex items-center gap-3"
          >
            <img
              v-if="auth.user?.avatar_url"
              :src="getImageUrl(auth.user.avatar_url)"
              :alt="auth.user.name"
              class="h-8 w-8 rounded-full object-cover"
            />
            <span v-else>{{ auth.user?.name || 'Profile' }}</span>
          </router-link>

          <button
            type="button"
            :disabled="loggingOut"
            class="text-left text-red-600 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-50"
            @click="confirmLogout"
          >
            Logout
          </button>
        </template>

        <!-- Utilisateur non connecté -->
        <template v-else>
          <router-link
            to="/login"
            @click="isOpen = false"
          >
            Login
          </router-link>

          <router-link
            to="/register"
            @click="isOpen = false"
          >
            Register
          </router-link>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Menu } from 'lucide-vue-next'
import Swal from 'sweetalert2'

import { logoutUser } from '../../services/authService'
import { useAuthStore } from '../../stores/auth'
import { getImageUrl } from '../../services/imageService'

const auth = useAuthStore()
const router = useRouter()

const isOpen = ref(false)
const loggingOut = ref(false)

const initials = computed(() => {
  const name = auth.user?.name?.trim() ?? ''
  if (!name) return '?'
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]!.toUpperCase())
    .join('')
})

async function confirmLogout() {
  const result = await Swal.fire({
    title: 'Se déconnecter ?',
    text: 'Vous devrez vous reconnecter pour accéder à votre compte.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Oui, se déconnecter',
    cancelButtonText: 'Annuler'
  })

  if (!result.isConfirmed) {
    return
  }

  await logout()
}

async function logout() {
  loggingOut.value = true

  try {
    await logoutUser()
  } catch (error) {
    console.error('Erreur lors de la déconnexion :', error)
  } finally {
    loggingOut.value = false
  }

  auth.setUser(null)
  isOpen.value = false

  await Swal.fire({
    title: 'Déconnecté',
    text: 'Vous avez été déconnecté avec succès.',
    icon: 'success',
    timer: 1500,
    showConfirmButton: false
  })

  router.push('/login')
}
</script>