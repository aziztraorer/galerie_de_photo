<template>
  <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-48 overflow-hidden bg-slate-100">
      <img
        v-if="publication.image_url"
        :src="getImageUrl(publication.image_url)"
        :alt="publication.title"
        class="h-full w-full object-cover transition duration-300 hover:scale-105"
        loading="lazy"
      />
      <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
        Aucune image
      </div>
      <span class="absolute left-3 top-3 rounded-full bg-brand-blue px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow">
        Annonce
      </span>
    </div>

    <div class="space-y-3 p-6">
      <div>
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-blue">
          Publié par {{ publication.user_name || 'Utilisateur' }}
        </p>
        <h3 class="text-xl font-semibold text-black">{{ publication.title }}</h3>
      </div>

      <p v-if="publication.description" class="text-sm leading-7 text-slate-700">
        {{ publication.description }}
      </p>
      <p v-else class="text-sm text-slate-400">Aucune description disponible.</p>

      <div class="flex items-center justify-end gap-3 pt-2">
        <button
          type="button"
          :disabled="deleting"
          class="flex items-center gap-2 rounded-full bg-red-50 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50"
          @click="handleDelete"
        >
          <Trash2 class="h-4 w-4" />
          {{ deleting ? 'Suppression...' : 'Supprimer' }}
        </button>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Trash2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

import { getImageUrl } from '../../services/imageService'
import { deletePublication } from '../../services/publicationService'
import type { Publication } from '../../types'

const props = defineProps<{ publication: Publication }>()
const emit = defineEmits<{ deleted: [id: number] }>()

const deleting = ref(false)

async function handleDelete() {
  const result = await Swal.fire({
    title: 'Supprimer cette publication ?',
    text: `Êtes-vous sûr de vouloir supprimer "${props.publication.title}" ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Oui, supprimer',
    cancelButtonText: 'Annuler'
  })

  if (!result.isConfirmed) return

  deleting.value = true

  try {
    const response = await deletePublication(props.publication.id)
    
    if (!response.success) {
      await Swal.fire({
        title: 'Erreur',
        text: response.message || 'Impossible de supprimer cette publication.',
        icon: 'error'
      })
      return
    }

    await Swal.fire({
      title: 'Supprimée !',
      text: 'La publication a été supprimée avec succès.',
      icon: 'success'
    })

    emit('deleted', props.publication.id)
  } catch (error) {
    console.error('Erreur suppression publication :', error)
    await Swal.fire({
      title: 'Erreur',
      text: 'Une erreur est survenue lors de la suppression.',
      icon: 'error'
    })
  } finally {
    deleting.value = false
  }
}
</script>