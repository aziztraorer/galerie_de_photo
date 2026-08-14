<template>
  <div class="min-h-screen bg-white">
    <Navbar />

    <main class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
      <!-- Section Profil -->
      <section
        class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
      >
        <div class="flex items-center justify-between">
          <div>
            <p
              class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue"
            >
              Profile
            </p>
            <h1 class="mt-3 text-3xl font-semibold text-black">
              Your account
            </h1>
            <p class="mt-4 text-slate-700">
              Manage the details tied to your AzizDev Animals account.
            </p>
          </div>

          <!-- Bouton de déconnexion en rouge à droite -->
          <button
            type="button"
            :disabled="loggingOut"
            class="flex items-center gap-2 rounded-full bg-red-600 px-5 py-3 font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
            @click="confirmLogout"
          >
            <LogOut class="h-5 w-5" />
            Déconnexion
          </button>
        </div>

        <div
          v-if="auth.user"
          class="mt-8 flex flex-col gap-8 sm:flex-row sm:items-start"
        >
          <div class="flex flex-col items-center gap-3">
            <button
              type="button"
              class="group relative h-28 w-28 overflow-hidden rounded-full border border-slate-200 bg-slate-100"
              :disabled="avatarLoading"
              @click="triggerAvatarPicker"
            >
              <img
                v-if="avatarUrl"
                :src="avatarUrl"
                alt="Photo de profil"
                class="h-full w-full object-cover"
              />

              <span
                v-else
                class="flex h-full w-full items-center justify-center text-2xl font-semibold text-slate-400"
              >
                {{ initials }}
              </span>

              <span
                class="absolute inset-0 flex items-center justify-center bg-black/50 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100"
              >
                {{ avatarLoading ? 'Envoi...' : 'Modifier' }}
              </span>
            </button>

            <input
              ref="avatarInput"
              type="file"
              accept="image/png,image/jpeg,image/webp"
              class="hidden"
              @change="onAvatarSelected"
            />

            <p
              v-if="avatarMessage"
              :class="
                avatarSuccess
                  ? 'text-xs text-green-600'
                  : 'text-xs text-red-600'
              "
            >
              {{ avatarMessage }}
            </p>
          </div>

          <div class="flex-1 space-y-3">
            <div
              class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
            >
              <p class="text-sm text-slate-500">
                Name
              </p>
              <p class="text-lg font-semibold text-black">
                {{ auth.user.name }}
              </p>
            </div>

            <div
              class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
            >
              <p class="text-sm text-slate-500">
                Email
              </p>
              <p class="text-lg font-semibold text-black">
                {{ auth.user.email }}
              </p>
            </div>

            <!-- Rôle : visible uniquement pour les administrateurs -->
            <div
              v-if="auth.user.role === 'admin'"
              class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
            >
              <p class="text-sm text-slate-500">
                Rôle
              </p>
              <p class="text-lg font-semibold text-brand-blue">
                {{ auth.user.role || 'Utilisateur' }}
              </p>
            </div>
          </div>
        </div>

        <p
          v-else
          class="mt-8 text-slate-700"
        >
          You need to log in before you can view your profile.
        </p>
      </section>

      <!-- Section Changement de mot de passe -->
      <section
        v-if="auth.user"
        class="mt-8 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
      >
        <p
          class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue"
        >
          Security
        </p>

        <h2 class="mt-3 text-2xl font-semibold text-black">
          Modifier le mot de passe
        </h2>

        <form
          class="mt-8 max-w-xl space-y-5"
          @submit.prevent="changePassword"
        >
          <div>
            <label
              for="currentPassword"
              class="mb-2 block text-sm font-medium text-slate-700"
            >
              Ancien mot de passe
            </label>
            <input
              id="currentPassword"
              v-model="currentPassword"
              type="password"
              autocomplete="current-password"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-blue"
            />
          </div>

          <div>
            <label
              for="newPassword"
              class="mb-2 block text-sm font-medium text-slate-700"
            >
              Nouveau mot de passe
            </label>
            <input
              id="newPassword"
              v-model="newPassword"
              type="password"
              autocomplete="new-password"
              minlength="8"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-blue"
            />
          </div>

          <div>
            <label
              for="confirmPassword"
              class="mb-2 block text-sm font-medium text-slate-700"
            >
              Confirmer le nouveau mot de passe
            </label>
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              type="password"
              autocomplete="new-password"
              minlength="8"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-blue"
            />
          </div>

          <p
            v-if="message"
            :class="
              success
                ? 'text-sm text-green-600'
                : 'text-sm text-red-600'
            "
          >
            {{ message }}
          </p>

          <button
            type="submit"
            :disabled="loading"
            class="rounded-xl bg-brand-blue px-5 py-3 font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{
              loading
                ? 'Modification...'
                : 'Modifier le mot de passe'
            }}
          </button>
        </form>
      </section>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { LogOut } from 'lucide-vue-next'
import Swal from 'sweetalert2'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'

import { useAuthStore } from '../stores/auth'

import {
  changeUserPassword,
  updateUserAvatar,
  logoutUser
} from '../services/authService'

import { getImageUrl } from '../services/imageService'

const auth = useAuthStore()
const router = useRouter()

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const loading = ref(false)
const message = ref('')
const success = ref(false)
const loggingOut = ref(false)

const avatarInput = ref<HTMLInputElement | null>(null)
const avatarLoading = ref(false)
const avatarMessage = ref('')
const avatarSuccess = ref(false)

const avatarUrl = computed(() =>
  auth.user?.avatar_url
    ? getImageUrl(auth.user.avatar_url)
    : ''
)

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

function triggerAvatarPicker() {
  avatarInput.value?.click()
}

async function onAvatarSelected(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) {
    return
  }

  avatarMessage.value = ''
  avatarSuccess.value = false
  avatarLoading.value = true

  try {
    const response = await updateUserAvatar(file)

    if (!response.success || !response.data?.user) {
      avatarMessage.value =
        response.message ||
        'Impossible de mettre à jour la photo de profil.'
      return
    }

    auth.setUser(response.data.user)

    avatarSuccess.value = true
    avatarMessage.value = 'Photo de profil mise à jour.'
  } catch (error) {
    console.error('Erreur upload photo de profil :', error)
    avatarMessage.value =
      'Une erreur est survenue lors de l\'envoi de la photo.'
  } finally {
    avatarLoading.value = false
    target.value = ''
  }
}

async function changePassword() {
  message.value = ''
  success.value = false

  if (newPassword.value !== confirmPassword.value) {
    message.value = 'Les nouveaux mots de passe ne correspondent pas.'
    return
  }

  if (newPassword.value.length < 8) {
    message.value = 'Le nouveau mot de passe doit contenir au moins 8 caractères.'
    return
  }

  loading.value = true

  try {
    const response = await changeUserPassword({
      current_password: currentPassword.value,
      new_password: newPassword.value,
      confirm_password: confirmPassword.value,
    })

    if (!response.success) {
      message.value = response.message || 'Impossible de modifier le mot de passe.'
      return
    }

    success.value = true
    message.value = 'Votre mot de passe a été modifié avec succès.'

    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (error) {
    console.error('Erreur modification mot de passe :', error)
    message.value = 'Une erreur est survenue lors de la modification du mot de passe.'
  } finally {
    loading.value = false
  }
}

async function confirmLogout() {
  const result = await Swal.fire({
    title: 'Se déconnecter ?',
    text: 'Vous devrez vous reconnecter pour accéder à votre compte.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
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