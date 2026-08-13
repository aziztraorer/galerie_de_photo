<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <div class="mx-auto flex max-w-2xl flex-col px-6 py-16 lg:px-8">
      <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">Register</p>
        <h1 class="mt-3 text-3xl font-semibold text-black">Create your account</h1>
        <p class="mt-4 text-slate-700">Create an account to unlock richer information and manage favorites.</p>

        <form class="mt-8 space-y-4" @submit.prevent="submitRegister">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="name">Full name</label>
            <input id="name" v-model="name" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="email">Email</label>
            <input id="email" v-model="email" type="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="password">Password</label>
            <input id="password" v-model="password" type="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="confirmPassword">Confirm password</label>
            <input id="confirmPassword" v-model="confirmPassword" type="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required />
          </div>
          <button type="submit" class="w-full rounded-full bg-brand-blue px-4 py-3 font-medium text-white">Create account</button>
          <p v-if="message" class="text-sm text-slate-700">{{ message }}</p>
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
import { registerUser } from '../services/authService'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const message = ref('')

async function submitRegister() {
  message.value = ''
  try {
    const response = await registerUser({
      name: name.value,
      email: email.value,
      password: password.value,
      confirm_password: confirmPassword.value
    })
    if (response.success && response.data?.user) {
      auth.setUser(response.data.user)
      router.push('/dashboard')
    } else {
      message.value = response.message || 'Registration failed.'
    }
  } catch {
    message.value = 'Unable to register right now.'
  }
}
</script>
