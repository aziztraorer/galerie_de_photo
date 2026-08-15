import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

const PUBLIC_PATHS = ['/', '/login', '/register']

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status

    if (
      status === 401 &&
      !PUBLIC_PATHS.includes(window.location.pathname)
    ) {
      window.location.href = '/'
    }

    return Promise.reject(error)
  }
)

export default api