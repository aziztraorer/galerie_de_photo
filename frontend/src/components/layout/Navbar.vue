<template>
  import {
  PawPrint,
  LayoutGrid,
  Folder,
  Heart,
  User,
  LogOut
} from 'lucide-vue-next'
  <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
    <div
      class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8"
    >
      <!-- Logo / Nom -->
      <router-link
        to="/"
        class="text-lg font-semibold tracking-tight text-black"
      >
        AzizDev Animals
      </router-link>

      <!-- Menu Desktop -->
      <nav
        class="hidden items-center gap-6 text-sm font-medium text-slate-700 lg:flex"
      >
        <router-link
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
            to="/favorites"
            class="transition hover:text-brand-blue"
            active-class="text-brand-blue"
          >
            Favorites
          </router-link>

          <router-link
            to="/profile"
            class="transition hover:text-brand-blue"
            active-class="text-brand-blue"
          >
            Profile
          </router-link>

          <button
            type="button"
            class="transition hover:text-brand-blue"
            @click="logout"
          >
            Logout
          </button>
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
            to="/favorites"
            @click="isOpen = false"
          >
            Favorites
          </router-link>

          <router-link
            to="/profile"
            @click="isOpen = false"
          >
            Profile
          </router-link>

          <button
            type="button"
            class="text-left"
            @click="logout"
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Menu } from 'lucide-vue-next'

import { logoutUser } from '../../services/authService'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const isOpen = ref(false)

async function logout() {
  try {
    await logoutUser()
  } catch (error) {
    console.error('Erreur lors de la déconnexion :', error)
  }

  // Supprimer l'utilisateur du store
  auth.setUser(null)

  // Fermer le menu mobile
  isOpen.value = false

  // Retour vers la page de connexion
  router.push('/login')
}
</script>