<script setup lang="ts">

import {
  ref,
  computed,
  watch,
  onBeforeUnmount
} from 'vue'

import {
  ImagePlus,
  X,
  Send,
  Pencil
} from 'lucide-vue-next'


/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

interface PublicationImage {
  id: number
  image_url: string
}

interface Publication {
  id: number
  title: string
  description?: string
  images?: PublicationImage[]
}


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps<{
  publication?: Publication | null
}>()


/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

const emit = defineEmits<{
  published: [data: any]
  updated: [data: any]
  cancel: []
}>()


/*
|--------------------------------------------------------------------------
| Formulaire
|--------------------------------------------------------------------------
*/

const title = ref('')
const description = ref('')

const files = ref<File[]>([])
const previews = ref<string[]>([])

const loading = ref(false)

const message = ref('')
const success = ref(false)

const fileInput =
  ref<HTMLInputElement | null>(null)


/*
|--------------------------------------------------------------------------
| Mode édition
|--------------------------------------------------------------------------
*/

const isEditing = computed(() => {
  return !!props.publication
})


/*
|--------------------------------------------------------------------------
| URL API
|--------------------------------------------------------------------------
|
| IMPORTANT :
| On utilise la même origine API partout.
|
|--------------------------------------------------------------------------
*/

const API_URL =
  'http://localhost/cours/backend/public'


/*
|--------------------------------------------------------------------------
| Construire URL image
|--------------------------------------------------------------------------
*/

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
| Nettoyer les previews blob
|--------------------------------------------------------------------------
*/

function clearBlobPreviews() {

  previews.value.forEach(url => {

    if (url.startsWith('blob:')) {

      URL.revokeObjectURL(url)

    }

  })

}


/*
|--------------------------------------------------------------------------
| Charger publication
|--------------------------------------------------------------------------
*/

function loadPublication() {

  /*
   * Nettoyer uniquement les anciennes
   * previews locales.
   */

  previews.value.forEach(url => {

    if (url.startsWith('blob:')) {

      URL.revokeObjectURL(url)

    }

  })


  /*
   * Titre
   */

  title.value =
    props.publication?.title || ''


  /*
   * Description
   */

  description.value =
    props.publication?.description || ''


  /*
   * Images existantes
   */

  if (
    props.publication?.images &&
    props.publication.images.length > 0
  ) {

    previews.value =
      props.publication.images.map(image =>
        getImageUrl(image.image_url)
      )

  } else {

    previews.value = []

  }


  /*
   * Nouvelles images
   */

  files.value = []


  /*
   * Messages
   */

  message.value = ''
  success.value = false


  /*
   * Reset input file
   */

  if (fileInput.value) {

    fileInput.value.value = ''

  }

}


/*
|--------------------------------------------------------------------------
| Observer publication
|--------------------------------------------------------------------------
*/

watch(
  () => props.publication,
  () => {

    loadPublication()

  },
  {
    immediate: true
  }
)


/*
|--------------------------------------------------------------------------
| Sélection des images
|--------------------------------------------------------------------------
*/

function selectFiles(event: Event) {

  const input =
    event.target as HTMLInputElement


  const selectedFiles =
    Array.from(input.files || [])


  /*
   * Maximum 4 nouvelles images.
   */

  if (selectedFiles.length > 4) {

    message.value =
      'Vous pouvez sélectionner maximum 4 images.'

    success.value = false

  } else {

    message.value = ''

  }


  /*
   * Garder maximum 4 fichiers.
   */

  files.value =
    selectedFiles.slice(0, 4)


  /*
   * Supprimer les anciennes previews blob.
   */

  previews.value =
    previews.value.filter(preview => {

      if (preview.startsWith('blob:')) {

        URL.revokeObjectURL(preview)

        return false

      }

      return true

    })


  /*
   * Créer previews.
   */

  const newPreviews =
    files.value.map(file =>
      URL.createObjectURL(file)
    )


  /*
   * Anciennes images serveur
   * + nouvelles images locales
   */

  previews.value = [
    ...previews.value,
    ...newPreviews
  ]

}


/*
|--------------------------------------------------------------------------
| Supprimer une image
|--------------------------------------------------------------------------
*/

function removeImage(index: number) {

  const preview =
    previews.value[index]


  /*
   * Si image locale.
   */

  if (
    preview &&
    preview.startsWith('blob:')
  ) {

    URL.revokeObjectURL(preview)


    /*
     * Trouver l'index du fichier
     * correspondant.
     */

    const blobIndexes =
      previews.value
        .map((url, i) =>
          url.startsWith('blob:')
            ? i
            : -1
        )
        .filter(i => i !== -1)


    const fileIndex =
      blobIndexes.indexOf(index)


    if (fileIndex !== -1) {

      files.value.splice(
        fileIndex,
        1
      )

    }

  }


  /*
   * Supprimer preview.
   */

  previews.value.splice(
    index,
    1
  )


  /*
   * Reset input.
   */

  if (fileInput.value) {

    fileInput.value.value = ''

  }

}


/*
|--------------------------------------------------------------------------
| Annuler modification
|--------------------------------------------------------------------------
*/

function cancelEdit() {

  clearBlobPreviews()

  emit('cancel')

}


/*
|--------------------------------------------------------------------------
| Publier / Modifier
|--------------------------------------------------------------------------
*/

async function publish() {

  message.value = ''
  success.value = false


  /*
   * Vérification titre.
   */

  if (!title.value.trim()) {

    message.value =
      'Le titre est obligatoire.'

    return

  }


  /*
   * Pour une nouvelle publication :
   * image obligatoire.
   */

  if (
    !isEditing.value &&
    files.value.length === 0
  ) {

    message.value =
      'Veuillez sélectionner au moins une image.'

    return

  }


  /*
   * FormData
   */

  const formData =
    new FormData()


  formData.append(
    'title',
    title.value.trim()
  )


  formData.append(
    'description',
    description.value.trim()
  )


  /*
   * Nouvelles images.
   */

  files.value.forEach(file => {

    formData.append(
      'images[]',
      file
    )

  })


  /*
   * URL et méthode.
   */

  let url =
    `${API_URL}/api/publications`


  let method =
    'POST'


  /*
   |--------------------------------------------------------------------------
   | MODIFICATION
   |--------------------------------------------------------------------------
   |
   | IMPORTANT :
   | On utilise maintenant :
   |
   | POST /api/publications/{id}/update
   |
   | au lieu de :
   |
   | POST /api/publications/{id}
   |
   | avec _method=PUT.
   |
   */

  if (
    isEditing.value &&
    props.publication
  ) {

    url =
      `${API_URL}/api/publications/${props.publication.id}/update`

    method =
      'POST'

  }


  /*
   * Debug.
   */

  console.log(
    'Requête publication :',
    {
      url,
      method,
      publicationId:
        props.publication?.id,
      isEditing:
        isEditing.value
    }
  )


  loading.value = true


  try {

    const response =
      await fetch(
        url,
        {
          method,

          credentials: 'include',

          body: formData
        }
      )


    /*
     * Récupérer le contenu avant JSON.
     *
     * Cela évite :
     *
     * Unexpected token '<'
     *
     * lorsque PHP renvoie une erreur HTML.
     */

    const responseText =
      await response.text()


    console.log(
      'Réponse serveur :',
      responseText
    )


    let data: any


    try {

      data =
        JSON.parse(responseText)

    } catch (jsonError) {

      console.error(
        'Réponse serveur non JSON :',
        responseText
      )

      message.value =
        `Le serveur a retourné une réponse invalide (${response.status}).`

      success.value = false

      return

    }


    /*
     * Vérifier réponse HTTP.
     */

    if (
      !response.ok ||
      !data.success
    ) {

      message.value =
        data.message ||
        'Une erreur est survenue.'

      success.value = false

      return

    }


    /*
     |--------------------------------------------------------------------------
     | CRÉATION
     |--------------------------------------------------------------------------
     */

    if (!isEditing.value) {

      success.value = true

      message.value =
        'Publication créée avec succès.'


      emit(
        'published',
        data
      )


      /*
       * Reset formulaire.
       */

      title.value = ''

      description.value = ''

      clearBlobPreviews()

      files.value = []

      previews.value = []


      if (fileInput.value) {

        fileInput.value.value = ''

      }

    }


    /*
     |--------------------------------------------------------------------------
     | MODIFICATION
     |--------------------------------------------------------------------------
     */

    else {

      success.value = true

      message.value =
        'Publication modifiée avec succès.'


      emit(
        'updated',
        data
      )

    }


  } catch (error) {

    console.error(
      'Erreur publication :',
      error
    )


    message.value =
      'Impossible de contacter le serveur.'

    success.value = false


  } finally {

    loading.value = false

  }

}


/*
|--------------------------------------------------------------------------
| Nettoyage
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {

  clearBlobPreviews()

})

</script>


<template>

  <form
    class="space-y-6"
    @submit.prevent="publish"
  >

    <!-- =====================================================
         TITRE
    ====================================================== -->

    <div>

      <label
        for="publication-title"
        class="mb-2 block text-sm font-medium text-slate-700"
      >
        Titre de l'annonce
      </label>


      <input
        id="publication-title"
        v-model="title"
        type="text"
        placeholder="Ex : Chien disponible à Abidjan"
        class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-blue-100"
      />

    </div>


    <!-- =====================================================
         DESCRIPTION
    ====================================================== -->

    <div>

      <label
        for="publication-description"
        class="mb-2 block text-sm font-medium text-slate-700"
      >
        Description
      </label>


      <textarea
        id="publication-description"
        v-model="description"
        rows="5"
        placeholder="Décrivez votre animal..."
        class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-blue-100"
      ></textarea>

    </div>


    <!-- =====================================================
         IMAGES
    ====================================================== -->

    <div>

      <label
        for="publication-images"
        class="mb-2 block text-sm font-medium text-slate-700"
      >
        Images
      </label>


      <label
        for="publication-images"
        class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 transition hover:border-brand-blue hover:bg-blue-50"
      >

        <ImagePlus
          class="h-10 w-10 text-slate-400"
        />


        <p
          class="mt-3 text-sm font-medium text-slate-700"
        >
          Ajouter des images
        </p>


        <p
          class="mt-1 text-xs text-slate-500"
        >
          JPG, PNG ou WEBP — maximum 4 images
        </p>

      </label>


      <input
        id="publication-images"
        ref="fileInput"
        type="file"
        multiple
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
        @change="selectFiles"
      />

    </div>


    <!-- =====================================================
         PREVISUALISATION
    ====================================================== -->

    <div
      v-if="previews.length"
      class="grid grid-cols-2 gap-4 sm:grid-cols-4"
    >

      <div
        v-for="(preview, index) in previews"
        :key="`${preview}-${index}`"
        class="group relative aspect-square overflow-hidden rounded-2xl border border-slate-200"
      >

        <img
          :src="preview"
          :alt="`Image ${index + 1}`"
          class="h-full w-full object-cover"
        />


        <button
          type="button"
          class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-white transition hover:bg-black"
          @click="removeImage(index)"
        >

          <X class="h-4 w-4" />

        </button>

      </div>

    </div>


    <!-- =====================================================
         MESSAGE
    ====================================================== -->

    <div
      v-if="message"
      :class="
        success
          ? 'rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700'
          : 'rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700'
      "
    >

      {{ message }}

    </div>


    <!-- =====================================================
         BOUTONS
    ====================================================== -->

    <div
      class="flex flex-col gap-3 sm:flex-row"
    >

      <button
        type="submit"
        :disabled="loading"
        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-blue px-5 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
      >

        <Pencil
          v-if="isEditing"
          class="h-5 w-5"
        />

        <Send
          v-else
          class="h-5 w-5"
        />


        {{
          loading
            ? 'Enregistrement...'
            : isEditing
              ? 'Modifier la publication'
              : 'Publier l’annonce'
        }}

      </button>


      <button
        v-if="isEditing"
        type="button"
        class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100"
        @click="cancelEdit"
      >

        Annuler

      </button>

    </div>

  </form>

</template>