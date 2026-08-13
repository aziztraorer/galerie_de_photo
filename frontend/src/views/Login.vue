<template>
  <div class="min-h-screen bg-white">
    <Navbar />

    <div
      class="mx-auto flex max-w-2xl flex-col px-6 py-16 lg:px-8"
    >
      <div
        class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
      >
        <p
          class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue"
        >
          Login
        </p>

        <h1
          class="mt-3 text-3xl font-semibold text-black"
        >
          Access your account
        </h1>

        <p class="mt-4 text-slate-700">
          Use your email and password to sign in and unlock richer animal information.
        </p>

        <form
          class="mt-8 space-y-4"
          @submit.prevent="submitLogin"
        >
          <div>
            <label
              class="mb-2 block text-sm font-medium text-slate-700"
              for="email"
            >
              Email
            </label>

            <input
              id="email"
              v-model="email"
              type="email"
              autocomplete="email"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-blue"
              required
            />
          </div>

          <div>
            <label
              class="mb-2 block text-sm font-medium text-slate-700"
              for="password"
            >
              Password
            </label>

            <input
              id="password"
              v-model="password"
              type="password"
              autocomplete="current-password"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-blue"
              required
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-full bg-brand-blue px-4 py-3 font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ loading ? 'Login...' : 'Login' }}
          </button>

          <p
            v-if="message"
            class="text-sm text-red-600"
          >
            {{ message }}
          </p>
        </form>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'

import { loginUser } from '../services/authService'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const message = ref('')
const loading = ref(false)

async function submitLogin() {
  message.value = ''
  loading.value = true

  try {
    const response = await loginUser(
      email.value,
      password.value
    )

    if (
      response.success &&
      response.data?.user
    ) {
      auth.setUser(response.data.user)

      await router.push('/dashboard')

      return
    }

    message.value =
      response.message || 'Login failed.'
  } catch (error: any) {
    console.error(
      'Login error:',
      error
    )

    message.value =
      error?.response?.data?.message ||
      'Unable to log in right now.'
  } finally {
    loading.value = false
  }
}
</script>