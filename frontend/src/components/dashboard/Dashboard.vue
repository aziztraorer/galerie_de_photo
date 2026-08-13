<script setup lang="ts">

import {
  ref,
  onMounted,
  nextTick
} from 'vue'

import { useRouter } from 'vue-router'

import {
  LayoutDashboard,
  PawPrint,
  PlusCircle,
  User,
  LogOut,
  Image as ImageIcon,
  Menu,
  X,
  Pencil
} from 'lucide-vue-next'

import { useAuthStore } from '../../stores/auth'
import { logoutUser } from '../../services/authService'

import PublicationForm from './PublicationForm.vue'
import PublicationCard from './PublicationCard.vue'


/*
|--------------------------------------------------------------------------
| Router / Auth
|--------------------------------------------------------------------------
*/

const router = useRouter()
const auth = useAuthStore()


/*
|--------------------------------------------------------------------------
| État
|--------------------------------------------------------------------------
*/

const publications = ref<any[]>([])

const loading = ref(true)

const error = ref('')

const menuOpen = ref(false)


/*
|--------------------------------------------------------------------------
| Publication en cours de modification
|--------------------------------------------------------------------------
*/

const editingPublication = ref<any | null>(null)


/*
|--------------------------------------------------------------------------
| Référence du formulaire
|--------------------------------------------------------------------------
*/

const formSection =
  ref<HTMLElement | null>(null)


/*
|--------------------------------------------------------------------------
| Charger les publications
|--------------------------------------------------------------------------
*/

async function loadPublications() {

  loading.value = true

  error.value = ''

  try {

    const response = await fetch(
      '/api/publications',
      {
        method: 'GET',

        credentials: 'include',

        headers: {
          Accept: 'application/json'
        }
      }
    )

    const data =
      await response.json()

    if (
      !response.ok ||
      !data.success
    ) {

      error.value =
        data.message ||
        'Impossible de charger les publications.'

      return
    }

    publications.value =
      data.data?.publications || []

  } catch (err) {

    console.error(
      'Erreur chargement publications :',
      err
    )

    error.value =
      'Impossible de contacter le serveur.'

  } finally {

    loading.value = false

  }
}


/*
|--------------------------------------------------------------------------
| MODIFIER UNE PUBLICATION
|--------------------------------------------------------------------------
*/

async function editPublication(
  publication: any
) {

  console.log(
    'Publication reçue par Dashboard :',
    publication
  )


  /*
   * Créer une copie.
   */

  editingPublication.value = {
    ...publication,

    images: publication.images
      ? [...publication.images]
      : []
  }


  console.log(
    'Publication en édition :',
    editingPublication.value
  )


  /*
   * Attendre la mise à jour de Vue.
   */

  await nextTick()


  /*
   * Aller vers le formulaire.
   */

  formSection.value?.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  })

}


/*
|--------------------------------------------------------------------------
| ANNULER MODIFICATION
|--------------------------------------------------------------------------
*/

function cancelEdit() {

  editingPublication.value = null

}


/*
|--------------------------------------------------------------------------
| PUBLICATION CRÉÉE
|--------------------------------------------------------------------------
*/

async function handlePublished() {

  editingPublication.value = null

  await loadPublications()

}


/*
|--------------------------------------------------------------------------
| PUBLICATION MODIFIÉE
|--------------------------------------------------------------------------
*/

async function handleUpdated(
  data: any
) {

  console.log(
    'Publication modifiée :',
    data
  )

  editingPublication.value = null

  await loadPublications()

}


/*
|--------------------------------------------------------------------------
| SUPPRIMER
|--------------------------------------------------------------------------
*/

async function deletePublication(
  id: number
) {

  const confirmed =
    window.confirm(
      'Voulez-vous vraiment supprimer cette publication ?'
    )

  if (!confirmed) {
    return
  }


  try {

    const response =
      await fetch(
        `/api/publications/${id}`,
        {
          method: 'DELETE',

          credentials: 'include',

          headers: {
            Accept: 'application/json'
          }
        }
      )


    const data =
      await response.json()


    if (
      !response.ok ||
      !data.success
    ) {

      window.alert(
        data.message ||
        'Impossible de supprimer la publication.'
      )

      return
    }


    publications.value =
      publications.value.filter(
        publication =>
          publication.id !== id
      )


  } catch (err) {

    console.error(
      'Erreur suppression :',
      err
    )

    window.alert(
      'Une erreur est survenue lors de la suppression.'
    )

  }

}


/*
|--------------------------------------------------------------------------
| DÉCONNEXION
|--------------------------------------------------------------------------
*/

async function logout() {

  try {

    await logoutUser()

  } catch (err) {

    console.error(
      'Erreur déconnexion :',
      err
    )

  }

  auth.setUser(null)

  router.push('/login')

}


/*
|--------------------------------------------------------------------------
| NAVIGATION
|--------------------------------------------------------------------------
*/

function goTo(
  path: string
) {

  menuOpen.value = false

  router.push(path)

}


/*
|--------------------------------------------------------------------------
| INITIALISATION
|--------------------------------------------------------------------------
*/

onMounted(
  async () => {

    if (!auth.user) {

      await auth.hydrate()

    }


    if (!auth.isAuthenticated) {

      router.push('/login')

      return

    }


    await loadPublications()

  }
)

</script>


<template>

  <div class="min-h-screen bg-slate-50">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header
      class="sticky top-0 z-50 border-b border-slate-200 bg-white"
    >

      <div
        class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8"
      >

        <!-- Logo -->

        <button
          type="button"
          class="flex items-center gap-2"
          @click="goTo('/dashboard')"
        >

          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-blue text-white"
          >

            <PawPrint class="h-5 w-5" />

          </div>


          <div class="text-left">

            <p class="font-bold text-black">
              AzizDev Animals
            </p>

            <p class="text-xs text-slate-500">
              Espace utilisateur
            </p>

          </div>

        </button>


        <!-- Desktop -->

        <div
          class="hidden items-center gap-4 md:flex"
        >

          <div class="text-right">

            <p
              class="text-sm font-semibold text-black"
            >
              Bonjour
              {{ auth.user?.name || 'Utilisateur' }}
            </p>

            <p class="text-xs text-slate-500">
              Mon espace
            </p>

          </div>


          <button
            type="button"
            class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            @click="logout"
          >

            <LogOut class="h-4 w-4" />

            Déconnexion

          </button>

        </div>


        <!-- Mobile -->

        <button
          type="button"
          class="rounded-xl border border-slate-200 p-2 md:hidden"
          aria-label="Ouvrir le menu"
          @click="menuOpen = !menuOpen"
        >

          <X
            v-if="menuOpen"
            class="h-5 w-5"
          />

          <Menu
            v-else
            class="h-5 w-5"
          />

        </button>

      </div>


      <!-- Menu mobile -->

      <div
        v-if="menuOpen"
        class="border-t border-slate-200 bg-white p-4 md:hidden"
      >

        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm hover:bg-slate-100"
          @click="goTo('/dashboard')"
        >

          <LayoutDashboard class="h-5 w-5" />

          Dashboard

        </button>


        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm hover:bg-slate-100"
          @click="goTo('/animals')"
        >

          <PawPrint class="h-5 w-5" />

          Animals

        </button>


        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm hover:bg-slate-100"
          @click="goTo('/profile')"
        >

          <User class="h-5 w-5" />

          Profil

        </button>


        <button
          type="button"
          class="mt-2 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm text-red-600 hover:bg-red-50"
          @click="logout"
        >

          <LogOut class="h-5 w-5" />

          Déconnexion

        </button>

      </div>

    </header>


    <!-- =====================================================
         LAYOUT
    ====================================================== -->

    <div class="mx-auto flex max-w-7xl">


      <!-- SIDEBAR -->

      <aside
        class="hidden min-h-[calc(100vh-73px)] w-64 border-r border-slate-200 bg-white md:block"
      >

        <nav class="sticky top-[73px] p-4">


          <button
            type="button"
            class="mb-2 flex w-full items-center gap-3 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-brand-blue"
            @click="goTo('/dashboard')"
          >

            <LayoutDashboard class="h-5 w-5" />

            Dashboard

          </button>


          <button
            type="button"
            class="mb-2 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            @click="goTo('/animals')"
          >

            <PawPrint class="h-5 w-5" />

            Animals

          </button>


          <button
            type="button"
            class="mb-2 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            @click="cancelEdit"
          >

            <PlusCircle class="h-5 w-5" />

            Nouvelle publication

          </button>


          <button
            type="button"
            class="mb-2 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            @click="goTo('/profile')"
          >

            <User class="h-5 w-5" />

            Profil

          </button>


          <button
            type="button"
            class="mt-8 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50"
            @click="logout"
          >

            <LogOut class="h-5 w-5" />

            Déconnexion

          </button>

        </nav>

      </aside>


      <!-- =====================================================
           CONTENU
      ====================================================== -->

      <main
        class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8"
      >


        <!-- TITRE -->

        <div class="mb-8">

          <p
            class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-blue"
          >
            Dashboard
          </p>

          <h1
            class="mt-2 text-3xl font-bold text-black"
          >
            Mes publications
          </h1>

          <p class="mt-2 text-slate-600">
            Gérez vos publications et ajoutez vos animaux.
          </p>

        </div>


        <!-- =================================================
             FORMULAIRE
        ================================================== -->

        <section
          ref="formSection"
          class="scroll-mt-24 mb-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >

          <div
            class="mb-6 flex items-center gap-3"
          >

            <div
              class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-blue"
            >

              <Pencil
                v-if="editingPublication"
                class="h-5 w-5"
              />

              <PlusCircle
                v-else
                class="h-5 w-5"
              />

            </div>


            <div>

              <h2
                class="font-semibold text-black"
              >

                {{
                  editingPublication
                    ? 'Modifier la publication'
                    : 'Nouvelle publication'
                }}

              </h2>


              <p class="text-sm text-slate-500">

                {{
                  editingPublication
                    ? 'Modifiez les informations de votre publication.'
                    : 'Ajoutez une publication.'
                }}

              </p>

            </div>

          </div>


          <!-- FORMULAIRE -->

          <PublicationForm
            :publication="editingPublication"
            @published="handlePublished"
            @updated="handleUpdated"
            @cancel="cancelEdit"
          />

        </section>


        <!-- =================================================
             LISTE
        ================================================== -->

        <section>

          <div
            class="mb-5 flex items-center justify-between"
          >

            <div>

              <h2
                class="text-xl font-bold text-black"
              >
                Mes publications
              </h2>

              <p
                class="mt-1 text-sm text-slate-500"
              >

                {{ publications.length }}

                publication{{
                  publications.length > 1
                    ? 's'
                    : ''
                }}

              </p>

            </div>

          </div>


          <!-- Chargement -->

          <div
            v-if="loading"
            class="rounded-3xl border border-slate-200 bg-white p-10 text-center"
          >

            <p class="text-slate-500">
              Chargement des publications...
            </p>

          </div>


          <!-- Erreur -->

          <div
            v-else-if="error"
            class="rounded-3xl border border-red-200 bg-red-50 p-6"
          >

            <p class="font-medium text-red-600">
              {{ error }}
            </p>

            <button
              type="button"
              class="mt-4 rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white"
              @click="loadPublications"
            >
              Réessayer
            </button>

          </div>


          <!-- Aucune publication -->

          <div
            v-else-if="publications.length === 0"
            class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"
          >

            <div
              class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100"
            >

              <ImageIcon
                class="h-8 w-8 text-slate-400"
              />

            </div>


            <h3
              class="mt-5 text-lg font-semibold text-black"
            >
              Aucune publication
            </h3>


            <p
              class="mt-2 text-sm text-slate-500"
            >
              Vous n'avez pas encore créé de publication.
            </p>

          </div>


          <!-- Publications -->

          <div
            v-else
            class="space-y-6"
          >

            <PublicationCard
              v-for="publication in publications"
              :key="publication.id"
              :publication="publication"
              @delete="deletePublication"
              @edit="editPublication"
            />

          </div>

        </section>

      </main>

    </div>

  </div>

</template>