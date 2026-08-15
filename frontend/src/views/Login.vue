<template>
  <div class="min-h-screen bg-white">
    <Navbar />

    <div class="mx-auto flex max-w-2xl flex-col px-6 py-16 lg:px-8">
      <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">
          Login
        </p>

        <h1 class="mt-3 text-3xl font-semibold text-black">
          Access your account
        </h1>

        <p class="mt-4 text-slate-700">
          Use your email and password to sign in.
        </p>

        <!-- Message d'erreur avec tentatives restantes fusionné -->
        <div v-if="message && !isLocked" 
             class="mt-6 rounded-xl p-4"
             :class="attemptsRemaining !== null && attemptsRemaining > 0 ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-red-50 text-red-700 border border-red-200'">
          <div class="flex items-start gap-3">
            <svg v-if="attemptsRemaining !== null && attemptsRemaining > 0" class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg v-else class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="font-medium">
                {{ message }}
                <span v-if="attemptsRemaining !== null && attemptsRemaining > 0" class="font-bold">
                  Il vous reste {{ attemptsRemaining }} tentative(s) avant que votre compte ne soit bloqué.
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Message de blocage -->
        <div v-if="isLocked && lockMinutes > 0" 
             class="mt-6 rounded-xl bg-red-50 p-4 text-red-700 border border-red-200">
          <div class="flex items-start gap-3">
            <svg class="h-6 w-6 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <div>
              <p class="font-semibold">Compte bloqué</p>
              <p class="mt-1 text-sm">
                Veuillez attendre <strong>{{ lockMinutes }}</strong> minute(s) avant de réessayer.
              </p>
              <p class="mt-2 text-sm">
                <a href="#" class="text-brand-blue hover:underline font-medium" @click.prevent="handleForgotPassword">
                  Mot de passe oublié ? Cliquez ici pour réinitialiser votre compte
                </a>
              </p>
            </div>
          </div>
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="submitLogin">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="email">
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              autocomplete="email"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-blue"
              :disabled="isLocked"
              required
              @blur="checkLockStatus"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700" for="password">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              autocomplete="current-password"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-brand-blue"
              :disabled="isLocked"
              required
            />
          </div>

          <button
            type="submit"
            :disabled="loading || isLocked"
            class="w-full rounded-full bg-brand-blue px-4 py-3 font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ loading ? 'Connexion...' : isLocked ? ' Compte bloqué' : 'Se connecter' }}
          </button>

          <div class="mt-4 text-center text-sm text-slate-500">
            <router-link to="/register" class="text-brand-blue hover:underline">
              Pas encore de compte ? Inscrivez-vous
            </router-link>
          </div>
        </form>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'

import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'

import { loginUser } from '../services/authService'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const message = ref('')
const loading = ref(false)
const isLocked = ref(false)
const lockMinutes = ref(0)
const attemptsRemaining = ref<number | null>(null)

let unlockTimer: ReturnType<typeof setTimeout> | null = null

// Vérifier le statut de verrouillage
async function checkLockStatus(): Promise<void> {
  if (!email.value) return
  
  try {
    const response = await api.get(`/auth/lock-status?email=${encodeURIComponent(email.value)}`)
    if (response.data && response.data.is_locked) {
      isLocked.value = true
      lockMinutes.value = response.data.lock_minutes || 5
      message.value = `Compte bloqué pour ${lockMinutes.value} minutes.`
    } else {
      isLocked.value = false
      lockMinutes.value = 0
      attemptsRemaining.value = response.data.remaining_attempts || null
    }
  } catch (error) {
    // Ignorer les erreurs
  }
}

// Surveiller les changements d'email
watch(email, () => {
  if (email.value) {
    checkLockStatus()
  }
})

function parseErrorMessage(errorMsg: string): void {
  message.value = errorMsg
  isLocked.value = false
  lockMinutes.value = 0
  attemptsRemaining.value = null

  if (errorMsg.includes('bloqué') || errorMsg.includes('bloquée')) {
    isLocked.value = true
    
    const minutesMatch = errorMsg.match(/(\d+)\s*minute/)
    if (minutesMatch) {
      lockMinutes.value = parseInt(minutesMatch[1])
    } else if (errorMsg.includes('10 jours')) {
      lockMinutes.value = 10 * 24 * 60
    } else {
      lockMinutes.value = 5
    }
  }

  const attemptsMatch = errorMsg.match(/reste\s*(\d+)\s*tentative/)
  if (attemptsMatch) {
    attemptsRemaining.value = parseInt(attemptsMatch[1])
  }
}

async function submitLogin() {
  if (isLocked.value) {
    message.value = `⚠️ Compte bloqué. Veuillez attendre ${lockMinutes.value} minute(s).`
    return
  }

  message.value = ''
  loading.value = true

  try {
    const response = await loginUser({
      email: email.value,
      password: password.value
    })

    if (response.success && response.data?.user) {
      auth.setUser(response.data.user)
      await router.push('/dashboard')
      return
    }

    const errorMsg = response.message || 'Login failed.'
    parseErrorMessage(errorMsg)

  } catch (error: any) {
    console.error('Login error:', error)
    
    if (error?.response?.status === 423) {
      const errorMsg = error?.response?.data?.message || 'Compte bloqué. Veuillez réessayer plus tard.'
      parseErrorMessage(errorMsg)
      isLocked.value = true
      
      if (lockMinutes.value > 0) {
        startUnlockTimer()
      }
      return
    }
    
    const errorMsg = error?.response?.data?.message || 'Une erreur est survenue.'
    parseErrorMessage(errorMsg)
    
  } finally {
    loading.value = false
  }
}

function startUnlockTimer(): void {
  if (unlockTimer) {
    clearTimeout(unlockTimer)
    unlockTimer = null
  }

  if (lockMinutes.value > 0) {
    const delayMs = lockMinutes.value * 60 * 1000
    unlockTimer = setTimeout(() => {
      isLocked.value = false
      lockMinutes.value = 0
      message.value = '✅ Votre compte est maintenant débloqué. Vous pouvez vous reconnecter.'
      unlockTimer = null
    }, delayMs)
  }
}

function handleForgotPassword() {
  Swal.fire({
    title: 'Mot de passe oublié',
    text: 'Veuillez contacter l\'administrateur pour réinitialiser votre mot de passe.',
    icon: 'info',
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'OK'
  })
}

onUnmounted(() => {
  if (unlockTimer) {
    clearTimeout(unlockTimer)
    unlockTimer = null
  }
})
</script>