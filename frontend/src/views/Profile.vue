<template>
  <div class="min-h-screen bg-white">

    <Navbar />

    <main class="mx-auto max-w-5xl px-6 py-16 lg:px-8">

      <section
        class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
      >

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

        <div
          v-if="auth.user"
          class="mt-8 space-y-3"
        >

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

        </div>

        <p
          v-else
          class="mt-8 text-slate-700"
        >
          You need to log in before you can view your profile.
        </p>

      </section>


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

import { ref } from 'vue'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'

import { useAuthStore } from '../stores/auth'

import {
  changeUserPassword
} from '../services/authService'

const auth = useAuthStore()

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const loading = ref(false)
const message = ref('')
const success = ref(false)


async function changePassword() {

  message.value = ''
  success.value = false

  if (
    newPassword.value !==
    confirmPassword.value
  ) {

    message.value =
      'Les nouveaux mots de passe ne correspondent pas.'

    return
  }

  if (newPassword.value.length < 8) {

    message.value =
      'Le nouveau mot de passe doit contenir au moins 8 caractères.'

    return
  }

  loading.value = true

  try {

    const response =
      await changeUserPassword({
        current_password:
          currentPassword.value,

        new_password:
          newPassword.value,

        confirm_password:
          confirmPassword.value,
      })


    if (!response.success) {

      message.value =
        response.message ||
        'Impossible de modifier le mot de passe.'

      return
    }


    success.value = true

    message.value =
      'Votre mot de passe a été modifié avec succès.'

    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''

  } catch (error) {

    console.error(
      'Erreur modification mot de passe :',
      error
    )

    message.value =
      'Une erreur est survenue lors de la modification du mot de passe.'

  } finally {

    loading.value = false
  }
}

</script>