import { createRouter, createWebHistory } from 'vue-router'

import HomeView from '../views/Home.vue'
import AnimalsView from '../views/Animals.vue'
import CategoriesView from '../views/Categories.vue'
import LoginView from '../views/Login.vue'
import RegisterView from '../views/Register.vue'
import ProfileView from '../views/Profile.vue'
import FavoritesView from '../views/Favorites.vue'
import AnimalDetailsView from '../views/AnimalDetails.vue'
import NotFoundView from '../views/NotFound.vue'
import DashboardView from '../views/Dashboard.vue'

import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },

  {
    path: '/animals',
    name: 'animals',
    component: AnimalsView
  },

  {
    path: '/animals/:id',
    name: 'animal-details',
    component: AnimalDetailsView,
    props: true
  },

  {
    path: '/categories',
    name: 'categories',
    component: CategoriesView
  },

  {
    path: '/categories/:id',
    name: 'category-animals',
    component: AnimalsView
  },

  {
    path: '/login',
    name: 'login',
    component: LoginView
  },

  {
    path: '/register',
    name: 'register',
    component: RegisterView
  },

  // Pages protegees : reservees aux utilisateurs connectes.
  // "meta.requiresAuth" est lu par le garde de navigation ci-dessous.
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true }
  },

  {
    path: '/favorites',
    name: 'favorites',
    component: FavoritesView,
    meta: { requiresAuth: true }
  },

  // Dashboard utilisateur (creation/gestion des annonces) : protege.
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },

  // Page 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Tant qu'on ne sait pas encore si l'utilisateur est connecte
  // (premier chargement, rechargement de la page...), on verifie
  // sa session AVANT de decider d'autoriser ou non l'acces a la route.
  if (!auth.user && !auth.isLoading) {
    await auth.hydrate()
  }

  // Route protegee + utilisateur non connecte => redirection vers l'accueil.
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'home' }
  }

  /*
   * Une fois connecte, la partie "Home" (page d'accueil / landing page)
   * ne doit plus etre accessible : on redirige automatiquement vers la
   * page Animaux.
   */
  if (to.name === 'home' && auth.isAuthenticated) {
    return { path: '/animals' }
  }

  return true
})

export default router