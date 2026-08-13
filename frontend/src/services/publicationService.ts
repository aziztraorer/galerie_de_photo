import api from './api'
import type {
  ApiResponse,
  Publication,
  PublicationsData
} from '../types'

export async function fetchPublications(): Promise<
  ApiResponse<PublicationsData>
> {
  const response = await api.get<
    ApiResponse<PublicationsData>
  >('/publications')

  return response.data
}

export async function createPublication(
  title: string,
  description: string,
  file: File | null
): Promise<ApiResponse<{ publication: Publication }>> {
  const formData = new FormData()

  formData.append('title', title)
  formData.append('description', description)

  if (file) {
    formData.append('image', file)
  }

  const response = await api.post<
    ApiResponse<{ publication: Publication }>
  >('/publications', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })

  return response.data
}

export async function updatePublication(
  id: number,
  title: string,
  description: string,
  file: File | null
): Promise<ApiResponse<{ publication: Publication }>> {
  const formData = new FormData()

  formData.append('title', title)
  formData.append('description', description)

  if (file) {
    formData.append('image', file)
  }

  const response = await api.post<
    ApiResponse<{ publication: Publication }>
  >(`/publications/${id}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    params: {
      _method: 'PUT'
    }
  })

  return response.data
}

export async function deletePublication(
  id: number
): Promise<ApiResponse<null>> {
  const response = await api.delete<ApiResponse<null>>(
    `/publications/${id}`
  )

  return response.data
}