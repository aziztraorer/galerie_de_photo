export interface User {
  id: number
  name: string
  email: string
  role?: string
  created_at?: string
  updated_at?: string
}

export interface Category {
  id: number
  name: string
  description?: string | null
  created_at?: string
  updated_at?: string
}

export interface Animal {
  id: number
  name: string
  scientific_name?: string | null
  short_description?: string | null
  description?: string | null
  image_url?: string | null
  category_id?: number | null
  category_name?: string | null
  category?: Category | null
  created_at?: string
  updated_at?: string
}

export interface Publication {
  id: number
  user_id: number
  title: string
  description?: string
  image_url?: string
  user_name?: string | null
  user_email?: string | null
  created_at?: string
  updated_at?: string
}

export interface Favorite {
  id: number
  user_id: number
  animal_id: number
  animal?: Animal | null
  created_at?: string
  updated_at?: string
}

export interface ApiResponse<T> {
  success: boolean
  message?: string
  data?: T
}

export interface AnimalsData {
  animals: Animal[]
}

export interface PublicationsData {
  publications: Publication[]
}

export interface CategoriesData {
  categories: Category[]
}

export interface FavoritesData {
  favorites: Favorite[]
}