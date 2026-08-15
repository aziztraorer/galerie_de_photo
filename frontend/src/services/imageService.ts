import api from './api'

export function getImageUrl(path?: string | null): string {
  if (!path) {
    return ''
  }

  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }

  if (path.startsWith('/')) {
    return `http://localhost:8000${path}`
  }

  return `http://localhost:8000/${path}`
}

export async function uploadImage(file: File, title?: string): Promise<unknown> {
  const formData = new FormData()
  formData.append('image', file)
  
  if (title) {
    formData.append('title', title)
  }

  const response = await api.post('/images', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })

  return response.data
}

export async function deleteImage(imageId: number): Promise<unknown> {
  const response = await api.delete(`/images/${imageId}`)
  return response.data
}

export async function updateImage(imageId: number, file: File, title?: string): Promise<unknown> {
  const formData = new FormData()
  formData.append('image', file)
  
  if (title) {
    formData.append('title', title)
  }

  const response = await api.post(`/images/${imageId}/update`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })

  return response.data
}

export async function listImages(): Promise<unknown> {
  const response = await api.get('/images')
  return response.data
}