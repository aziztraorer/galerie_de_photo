import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  }
})

// Pages accessibles sans etre connecte : on n'y force pas de redirection
// meme si une requete API renvoie 401 (ex: appel "auth/me" au demarrage).
const PUBLIC_PATHS = ['/', '/login', '/register']

/*
 * Filet de securite complementaire au garde de route (router/index.ts) :
 * si la session expire PENDANT que l'utilisateur est deja sur une page
 * protegee (ex: token de session invalide cote serveur), la premiere
 * requete API qui echoue avec 401 renvoie automatiquement vers l'accueil.
 */
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