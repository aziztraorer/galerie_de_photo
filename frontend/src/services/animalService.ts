import api from './api'
import type {
  Animal,
  ApiResponse,
  AnimalsData,
  Category,
  CategoriesData
} from '../types'

export async function fetchAnimals(): Promise<
  ApiResponse<AnimalsData>
> {
  const response = await api.get<ApiResponse<AnimalsData>>(
    '/animals'
  )

  return response.data
}

export async function fetchAnimal(
  id: number
): Promise<ApiResponse<{ animal: Animal }>> {
  const response = await api.get<
    ApiResponse<{ animal: Animal }>
  >(`/animals/${id}`)

  return response.data
}

export async function fetchCategories(): Promise<
  ApiResponse<CategoriesData>
> {
  const response = await api.get<ApiResponse<CategoriesData>>(
    '/categories'
  )

  return response.data
}

export async function fetchCategory(
  id: number
): Promise<
  ApiResponse<{
    category: Category
    animals?: Animal[]
  }>
> {
  const response = await api.get<
    ApiResponse<{
      category: Category
      animals?: Animal[]
    }>
  >(`/categories/${id}`)

  return response.data
}