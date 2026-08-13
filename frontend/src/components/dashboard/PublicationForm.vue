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

import {
  createPublication,
  updatePublication
} from '../../services/publicationService'

import { getImageUrl } from '../../services/imageService'

import type { Publication } from '../../types'


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

const file = ref<File | null>(null)
const preview = ref<string>('')

const loading = ref(false)

const message = ref('')
const success = ref(false)

const fileInput =
  ref<HTMLInputElement | null>(null)


/*
|--------------------------------------------------------------------------
| Mode Ã©dition
|--------------------------------------------------------------------------
*/

const isEditing = computed(() => {
  return !!props.publication
})


/*
|--------------------------------------------------------------------------
| Nettoyer la preview blob
|--------------------------------------------------------------------------
*/

function clearBlobPreview() {

  if (preview.value.startsWith('blob:')) {

    URL.revokeObjectURL(preview.value)

  }

}


/*
|--------------------------------------------------------------------------
| Charger publication
|--------------------------------------------------------------------------
*/

function loadPublication() {

  clearBlobPreview()

  title.value =
    props.publication?.title || ''

  description.value =
    props.publication?.description || ''

  preview.value =
    props.publication?.image_url
      ? getImageUrl(props.publication.image_url)
      : ''

  file.value = null

  message.value = ''
  success.value = false

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
| SÃ©lection de l'image
|--------------------------------------------------------------------------
*/

function selectFile(event: Event) {

  const input =
    event.target as HTMLInputElement

  const selectedFile =
    input.files && input.files.length > 0
      ? input.files[0]
      : null

  message.value = ''

  clearBlobPreview()

  file.value = selectedFile

  preview.value = selectedFile
    ? URL.createObjectURL(selectedFile)
    : ''

}


/*
|--------------------------------------------------------------------------
| Supprimer l'image sÃ©lectionnÃ©e
|--------------------------------------------------------------------------
*/

function removeImage() {

  clearBlobPreview()

  file.value = null
  preview.value = ''

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

  clearBlobPreview()

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

  if (!title.value.trim()) {

    message.value =
      'Le titre est obligatoire.'

    return

  }

  /*
   * Pour une nouvelle publication : image obligatoire.
   */

  if (
    !isEditing.value &&
    !file.value
  ) {

    message.value =
      'Veuillez sÃ©lectionner une image.'

    return

  }

  loading.value = true

  try {

    let response

    if (isEditing.value && props.publication) {

      response = await updatePublication(
        props.publication.id,
        title.value.trim(),
        description.value.trim(),
        file.value
      )

    } else {

      response = await createPublication(
        title.value.trim(),
        description.value.trim(),
        file.value
      )

    }

    if (!response.success) {

      message.value =
        response.message ||
        'Une erreur est survenue.'

      success.value = false

      return

    }

    if (!isEditing.value) {

      success.value = true

      message.value =
        'Publication crÃ©Ã©e avec succÃ¨s.'

      emit('published', response)

      title.value = ''
      description.value = ''

      clearBlobPreview()

      file.value = null
      preview.value = ''

      if (fileInput.value) {

        fileInput.value.value = ''

      }

    } else {

      success.value = true

      message.value =
        'Publication modifiÃ©e avec succÃ¨s.'

      emit('updated', response)

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

  clearBlobPreview()

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
        placeholder="Ex : Chien disponible Ã  Abidjan"
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
        placeholder="DÃ©crivez votre animal..."
        class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-blue-100"
      ></textarea>

    </div>


    <!-- =====================================================
         IMAGE
    ====================================================== -->

    <div>

      <label
        for="publication-image"
        class="mb-2 block text-sm font-medium text-slate-700"
      >
        Image
      </label>

      <label
        v-if="!preview"
        for="publication-image"
        class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 transition hover:border-brand-blue hover:bg-blue-50"
      >

        <ImagePlus class="h-10 w-10 text-slate-400" />

        <p class="mt-3 text-sm font-medium text-slate-700">
          Ajouter une image
        </p>

        <p class="mt-1 text-xs text-slate-500">
          JPG, PNG ou WEBP â€” 5 Mo maximum
        </p>

      </label>

      <input
        id="publication-image"
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
        @change="selectFile"
      />

      <div
        v-if="preview"
        class="group relative mt-2 aspect-video w-full max-w-sm overflow-hidden rounded-2xl border border-slate-200"
      >

        <img
          :src="preview"
          alt="AperÃ§u de l'image"
          class="h-full w-full object-cover"
        />

        <button
          type="button"
          class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-white transition hover:bg-black"
          @click="removeImage"
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

    <div class="flex flex-col gap-3 sm:flex-row">

      <button
        type="submit"
        :disabled="loading"
        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-blue px-5 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
      >

        <Pencil v-if="isEditing" class="h-5 w-5" />
        <Send v-else class="h-5 w-5" />

        {{
          loading
            ? 'Enregistrement...'
            : isEditing
              ? 'Modifier la publication'
              : "Publier l'annonce"
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