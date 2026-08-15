import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import Swal from 'sweetalert2'
import api from '../services/api'

import type { User, Publication } from '../types'
import { fetchPublications } from '../services/publicationService'
import { getImageUrl } from '../services/imageService'

export interface AdminUser extends User {
  last_activity?: string | null
  is_online?: number
  seconds_since_activity?: number
  publications_count?: number
}

export const useAdminStore = defineStore('admin', () => {
  const users = ref<AdminUser[]>([])
  const publications = ref<Publication[]>([])
  const loading = ref(false)
  const onlineCount = ref(0)
  const deletingUser = ref<number | null>(null)
  const error = ref<string | null>(null)

  const onlineUsers = computed(() => onlineCount.value)
  
  const adminCount = computed(() => {
    return users.value.filter(u => u.role === 'admin').length
  })

  const totalUsers = computed(() => {
    return users.value.length
  })

  const userPublicationStats = computed(() => {
    const stats: { userId: number; name: string; avatar?: string; count: number }[] = []
    users.value.forEach(user => {
      const count = publications.value.filter(p => p.user_id === user.id).length
      if (count > 0) {
        stats.push({
          userId: user.id,
          name: user.name,
          avatar: user.avatar_url || undefined,
          count
        })
      }
    })
    return stats.sort((a, b) => b.count - a.count)
  })

  const recentPublications = computed(() => {
    return [...publications.value]
      .sort((a, b) => {
        const dateA = a.created_at ? new Date(a.created_at).getTime() : 0
        const dateB = b.created_at ? new Date(b.created_at).getTime() : 0
        return dateB - dateA
      })
      .slice(0, 5)
  })

  function isUserOnline(userId: number): boolean {
    const user = users.value.find(u => u.id === userId)
    return user ? (user.is_online === 1) : false
  }

  function getUserInitials(name: string): string {
    if (!name) return '?'
    return name
      .split(' ')
      .filter(Boolean)
      .slice(0, 2)
      .map((part) => part[0]!.toUpperCase())
      .join('')
  }

  function getUserPublicationCount(userId: number): number {
    return publications.value.filter(p => p.user_id === userId).length
  }

  function getLastActivity(user: AdminUser): string {
    if (!user.last_activity) return 'Jamais'
    try {
      const date = new Date(user.last_activity)
      if (isNaN(date.getTime())) return 'Jamais'
      return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
      })
    } catch {
      return 'Jamais'
    }
  }

  async function loadUsers() {
    try {
      error.value = null
      const response = await api.get('/admin/users')
      
      if (response.data.success && response.data.data?.users) {
        users.value = response.data.data.users
        onlineCount.value = users.value.filter(u => u.is_online === 1).length
      } else {
        error.value = response.data.message || 'Erreur lors du chargement des utilisateurs'
        users.value = []
        onlineCount.value = 0
      }
    } catch (err: any) {
      if (err.response?.status === 403) {
        error.value = 'Vous n\'avez pas les droits d\'administration.'
      } else if (err.response?.status === 401) {
        error.value = 'Vous devez être connecté pour accéder à l\'administration.'
      } else {
        error.value = 'Erreur de connexion au serveur.'
      }
      users.value = []
      onlineCount.value = 0
    }
  }

  async function loadOnlineUsers() {
    try {
      const response = await api.get('/admin/users/online?minutes=5')
      if (response.data.success && response.data.data) {
        onlineCount.value = response.data.data.count || 0
      } else {
        onlineCount.value = users.value.filter(u => u.is_online === 1).length
      }
    } catch {
      onlineCount.value = users.value.filter(u => u.is_online === 1).length
    }
  }

  async function loadPublications() {
    try {
      const response = await fetchPublications()
      if (response.success) {
        publications.value = response.data?.publications || []
      }
    } catch {
      publications.value = []
    }
  }

  async function loadAll() {
    loading.value = true
    error.value = null
    try {
      await loadUsers()
      await loadPublications()
      await loadOnlineUsers()
    } catch {
      error.value = 'Erreur lors du chargement des données'
    } finally {
      loading.value = false
    }
  }

  async function deleteUser(userId: number) {
    deletingUser.value = userId
    try {
      const response = await api.delete(`/admin/users/${userId}`)
      
      if (response.data.success) {
        users.value = users.value.filter(u => u.id !== userId)
        onlineCount.value = users.value.filter(u => u.is_online === 1).length
        await Swal.fire({
          title: 'Supprimé !',
          text: 'L\'utilisateur a été supprimé avec succès.',
          icon: 'success'
        })
        return true
      }
      
      await Swal.fire({
        title: 'Erreur',
        text: response.data.message || 'Impossible de supprimer l\'utilisateur.',
        icon: 'error'
      })
      return false
      
    } catch (err: any) {
      await Swal.fire({
        title: 'Erreur',
        text: err.response?.data?.message || 'Une erreur est survenue lors de la suppression.',
        icon: 'error'
      })
      return false
    } finally {
      deletingUser.value = null
    }
  }

  async function confirmDeleteUser(user: AdminUser) {
    if (user.role === 'admin') {
      await Swal.fire({
        title: 'Action impossible',
        text: 'Vous ne pouvez pas supprimer un administrateur.',
        icon: 'warning'
      })
      return false
    }

    const result = await Swal.fire({
      title: 'Supprimer l\'utilisateur ?',
      text: `Êtes-vous sûr de vouloir supprimer ${user.name} ? Cette action est irréversible et supprimera également toutes ses publications.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    })
    
    if (!result.isConfirmed) return false
    return await deleteUser(user.id)
  }

  function removePublication(id: number) {
    publications.value = publications.value.filter(p => p.id !== id)
  }

  return {
    users,
    publications,
    loading,
    onlineCount,
    deletingUser,
    error,
    onlineUsers,
    adminCount,
    totalUsers,
    userPublicationStats,
    recentPublications,
    loadAll,
    loadUsers,
    loadOnlineUsers,
    loadPublications,
    deleteUser,
    confirmDeleteUser,
    removePublication,
    isUserOnline,
    getUserInitials,
    getUserPublicationCount,
    getLastActivity
  }
})