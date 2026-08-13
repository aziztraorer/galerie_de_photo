import api from './api'

export function getImageUrl(
  path?: string | null
): string {
  if (!path) {
    return ''
  }

  if (
    path.startsWith('http://') ||
    path.startsWith('https://')
  ) {
    return path
  }

  if (path.startsWith('/')) {
    return `http://localhost:8000${path}`
  }

  return `http://localhost:8000/${path}`
}

export async function uploadImage(
  file: File
): Promise<unknown> {
  const formData = new FormData()

  formData.append('image', file)

  const response = await api.post(
    '/images',
    formData,
    {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }
  )

  return response.data
}