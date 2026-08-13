import api from './api'
import type { ApiResponse, User } from '../types'

export interface LoginPayload {
  email: string
  password: string
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
}

export interface AuthData {
  user: User
}

export interface ChangePasswordPayload {
  current_password: string
  new_password: string
  confirm_password: string
}

/**
 * Connexion
 */
export async function loginUser(
  payload: LoginPayload
): Promise<ApiResponse<AuthData>> {
  const response = await api.post<ApiResponse<AuthData>>(
    '/auth/login',
    payload
  )

  return response.data
}

/**
 * Inscription
 */
export async function registerUser(
  payload: RegisterPayload
): Promise<ApiResponse<AuthData>> {
  const response = await api.post<ApiResponse<AuthData>>(
    '/auth/register',
    payload
  )

  return response.data
}

/**
 * Récupérer l'utilisateur connecté
 */
export async function fetchCurrentUser(): Promise<
  ApiResponse<AuthData>
> {
  const response = await api.get<ApiResponse<AuthData>>(
    '/auth/me'
  )

  return response.data
}

/**
 * Déconnexion
 */
export async function logoutUser(): Promise<
  ApiResponse<null>
> {
  const response = await api.post<ApiResponse<null>>(
    '/auth/logout'
  )

  return response.data
}

/**
 * Changer le mot de passe de l'utilisateur connecté
 */
export async function changeUserPassword(
  payload: ChangePasswordPayload
): Promise<ApiResponse<null>> {
  const response = await api.post<ApiResponse<null>>(
    '/auth/change-password',
    payload
  )

  return response.data
}