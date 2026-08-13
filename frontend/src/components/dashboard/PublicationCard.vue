<script setup lang="ts">
import {
  Pencil,
  Trash2,
  Image as ImageIcon
} from 'lucide-vue-next'

interface PublicationImage {
  id: number
  image_url: string
}

interface Publication {
  id: number
  user_id?: number
  title: string
  description?: string
  category_id?: number | null
  created_at?: string
  updated_at?: string
  images?: PublicationImage[]
}

const props = defineProps<{
  publication: Publication
}>()

const emit = defineEmits<{
  delete: [id: number]
  edit: [publication: Publication]
}>()

/*
|--------------------------------------------------------------------------
| URL du backend
|--------------------------------------------------------------------------
*/

const API_URL = 'http://localhost/cours/backend/public'

function getImageUrl(path: string): string {
  if (!path) {
    return ''
  }

  if (
    path.startsWith('http://') ||
    path.startsWith('https://')
  ) {
    return path
  }

  return `${API_URL}${path.startsWith('/') ? '' : '/'}${path}`
}

/*
|--------------------------------------------------------------------------
| Modifier
|--------------------------------------------------------------------------
*/

function editPublication() {
  console.log(
    'Clic Modifier :',
    props.publication
  )

  emit(
    'edit',
    props.publication
  )
}

/*
|--------------------------------------------------------------------------
| Supprimer
|--------------------------------------------------------------------------
*/

function deletePublication() {
  emit(
    'delete',
    props.publication.id
  )
}
</script>

<template>

  <article
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
  >

    <!-- =====================================================
         IMAGES
    ====================================================== -->

    <div
      v-if="
        publication.images &&
        publication.images.length > 0
      "
      class="grid grid-cols-2 gap-1 sm:grid-cols-4"
    >

      <div
        v-for="image in publication.images"
        :key="image.id"
        class="aspect-square overflow-hidden bg-slate-100"
      >

        <img
          :src="getImageUrl(image.image_url)"
          :alt="publication.title"
          class="h-full w-full object-cover transition duration-300 hover:scale-105"
          loading="lazy"
        />

      </div>

    </div>


    <!-- =====================================================
         AUCUNE IMAGE
    ====================================================== -->

    <div
      v-else
      class="flex h-48 items-center justify-center bg-slate-100"
    >

      <div class="text-center">

        <ImageIcon
          class="mx-auto h-10 w-10 text-slate-400"
        />

        <p class="mt-2 text-sm text-slate-500">
          Aucune image
        </p>

      </div>

    </div>


    <!-- =====================================================
         CONTENU
    ====================================================== -->

    <div class="p-5">

      <h3 class="text-xl font-bold text-black">
        {{ publication.title }}
      </h3>

      <p
        v-if="publication.description"
        class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600"
      >
        {{ publication.description }}
      </p>

      <p
        v-if="publication.created_at"
        class="mt-3 text-xs text-slate-400"
      >
        Publié le {{ publication.created_at }}
      </p>


      <!-- =================================================
           ACTIONS
      ================================================== -->

      <div
        class="mt-5 flex flex-wrap gap-3 border-t border-slate-100 pt-4"
      >

        <!-- Modifier -->

        <button
          type="button"
          class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
          @click="editPublication"
        >

          <Pencil class="h-4 w-4" />

          Modifier

        </button>


        <!-- Supprimer -->

        <button
          type="button"
          class="flex items-center gap-2 rounded-xl bg-red-50 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100"
          @click="deletePublication"
        >

          <Trash2 class="h-4 w-4" />

          Supprimer

        </button>

      </div>

    </div>

  </article>

</template>