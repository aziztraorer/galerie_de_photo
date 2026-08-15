import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import {
  fetchCurrentUser,
  loginUser,
  logoutUser,
  registerUser
} from '../services/authService'

import type {
  LoginPayload,
  RegisterPayload
} from '../services/authService'

import type { User } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const isHydrating = ref(false)

  const isAuthenticated = computed(() => user.value !== null)

  function setUser(nextUser: User | null): void {
    user.value = nextUser
  }

  async function hydrate(): Promise<void> {
    if (isHydrating.value) return
    
    try {
      isHydrating.value = true
      isLoading.value = true
      const response = await fetchCurrentUser()
      if (response.success && response.data?.user) {
        user.value = response.data.user
      } else {
        user.value = null
      }
    } catch {
      user.value = null
    } finally {
      isLoading.value = false
      isHydrating.value = false
    }
  }

  // Méthode pour rafraîchir silencieusement les données
  async function refreshSilently(): Promise<void> {
    if (isHydrating.value) return
    
    try {
      const response = await fetchCurrentUser()
      if (response.success && response.data?.user) {
        user.value = response.data.user
      } else {
        user.value = null
      }
    } catch {
      // Erreur silencieuse
    }
  }

  async function login(payload: LoginPayload): Promise<boolean> {
    try {
      isLoading.value = true
      const response = await loginUser(payload)
      if (response.success && response.data?.user) {
        user.value = response.data.user
        return true
      }
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function register(payload: RegisterPayload): Promise<boolean> {
    try {
      isLoading.value = true
      const response = await registerUser(payload)
      if (response.success && response.data?.user) {
        user.value = response.data.user
        return true
      }
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    try {
      isLoading.value = true
      await logoutUser()
    } catch {
      // Erreur silencieuse
    } finally {
      user.value = null
      isLoading.value = false
    }
  }

  return {
    user,
    isLoading,
    isAuthenticated,
    isHydrating,
    setUser,
    hydrate,
    refreshSilently,
    login,
    register,
    logout
  }
})