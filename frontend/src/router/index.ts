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

  {
    path: '/profile',
    name: 'profile',
    component: ProfileView
  },

  {
    path: '/favorites',
    name: 'favorites',
    component: FavoritesView
  },

  // Dashboard utilisateur
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView
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

export default router