import api from './api'
import type { ApiResponse, User } from '../types'

export interface AdminUser extends User {
  last_activity?: string | null
  is_online?: number
  seconds_since_activity?: number
  publications_count?: number
}

export interface AdminUsersResponse {
  users: AdminUser[]
  total: number
}

export interface OnlineUsersData {
  users: AdminUser[]
  count: number
  minutes: number
}

export async function fetchAdminUsers(): Promise<ApiResponse<AdminUsersResponse>> {
  try {
    const response = await api.get<ApiResponse<AdminUsersResponse>>('/admin/users')
    return response.data
  } catch (error) {
    return {
      success: false,
      message: 'Erreur lors du chargement des utilisateurs'
    }
  }
}

export async function fetchOnlineUsers(minutes: number = 5): Promise<ApiResponse<OnlineUsersData>> {
  try {
    const response = await api.get<ApiResponse<OnlineUsersData>>(`/admin/users/online?minutes=${minutes}`)
    return response.data
  } catch (error) {
    return {
      success: false,
      message: 'Erreur lors du chargement des utilisateurs en ligne'
    }
  }
}

export async function deleteAdminUser(userId: number): Promise<ApiResponse<null>> {
  try {
    const response = await api.delete<ApiResponse<null>>(`/admin/users/${userId}`)
    return response.data
  } catch (error) {
    return {
      success: false,
      message: 'Erreur lors de la suppression de l\'utilisateur'
    }
  }
}