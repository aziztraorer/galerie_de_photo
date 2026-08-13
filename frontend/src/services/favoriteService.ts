import api from './api'
import type {
  ApiResponse,
  Favorite,
  FavoritesData
} from '../types'

export async function fetchFavorites(): Promise<
  ApiResponse<FavoritesData>
> {
  const response = await api.get<
    ApiResponse<FavoritesData>
  >('/favorites')

  return response.data
}

export async function addFavorite(
  animalId: number
): Promise<ApiResponse<Favorite>> {
  const response = await api.post<
    ApiResponse<Favorite>
  >('/favorites', {
    animal_id: animalId
  })

  return response.data
}

export async function removeFavorite(
  animalId: number
): Promise<ApiResponse<null>> {
  const response = await api.delete<ApiResponse<null>>(
    `/favorites/${animalId}`
  )

  return response.data
}

export async function toggleFavorite(
  animalId: number,
  currentlyFavorite: boolean
): Promise<ApiResponse<unknown>> {
  if (currentlyFavorite) {
    return await removeFavorite(animalId)
  }

  return await addFavorite(animalId)
}