<template>
  <article
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
  >
    <div class="relative h-48 overflow-hidden bg-slate-100">
      <img
        v-if="publication.image_url"
        :src="getImageUrl(publication.image_url)"
        :alt="publication.title"
        class="h-full w-full object-cover transition duration-300 hover:scale-105"
        loading="lazy"
      />

      <div
        v-else
        class="flex h-full items-center justify-center text-sm text-slate-400"
      >
        Aucune image
      </div>

      <span
        class="absolute left-3 top-3 rounded-full bg-brand-blue px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow"
      >
        Annonce
      </span>
    </div>

    <div class="space-y-3 p-6">
      <div>
        <p
          v-if="publication.user_name"
          class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-blue"
        >
          Publié par {{ publication.user_name }}
        </p>

        <h3 class="text-xl font-semibold text-black">
          {{ publication.title }}
        </h3>
      </div>

      <p
        v-if="publication.description"
        class="text-sm leading-7 text-slate-700"
      >
        {{ publication.description }}
      </p>

      <p
        v-else
        class="text-sm text-slate-400"
      >
        Aucune description disponible.
      </p>

      <div
        v-if="isOwner"
        class="flex items-center justify-end gap-3 pt-2"
      >
        <!-- Etat normal : boutons Supprimer / Modifier -->
        <template v-if="!confirmingDelete">
          <button
            type="button"
            class="flex items-center gap-2 rounded-full bg-red-50 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100"
            @click="confirmingDelete = true"
          >
            <Trash2 class="h-4 w-4" />
            Supprimer
          </button>

          <button
            type="button"
            class="flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            @click="handleEdit"
          >
            <Pencil class="h-4 w-4" />
            Modifier
          </button>
        </template>

        <!-- Etat de confirmation : remplace les boutons, pas de popup navigateur -->
        <template v-else>
          <span class="text-sm text-slate-600">
            Supprimer cette annonce ?
          </span>

          <button
            type="button"
            class="rounded-full border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
            @click="confirmingDelete = false"
          >
            Annuler
          </button>

          <button
            type="button"
            class="rounded-full bg-red-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-700"
            @click="handleDelete"
          >
            Confirmer
          </button>
        </template>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Pencil, Trash2 } from 'lucide-vue-next'

import { useAuthStore } from '../../stores/auth'
import { getImageUrl } from '../../services/imageService'

import type { Publication } from '../../types'

/*
 * Ce composant ne fait pas lui-meme l'appel API : il se contente
 * d'emettre "edit" et "delete", le parent (vue Dashboard ou vue
 * Animaux) se charge de l'appel API et de la mise a jour de la liste.
 *
 * La confirmation de suppression est geree ici via un etat interne
 * (confirmingDelete), et non via window.confirm() : cette fenetre
 * native peut etre bloquee silencieusement par le navigateur (case
 * "ne plus afficher de boite de dialogue" cochee par erreur), ce qui
 * rendait la suppression impossible sans message d'erreur visible.
 */

const props = defineProps<{
  publication: Publication
}>()

const emit = defineEmits<{
  edit: [publication: Publication]
  delete: [id: number]
}>()

const auth = useAuthStore()

const confirmingDelete = ref(false)

const isOwner = computed(() => {
  return (
    auth.isAuthenticated &&
    auth.user &&
    Number(auth.user.id) === Number(props.publication.user_id)
  )
})

function handleEdit() {
  emit('edit', props.publication)
}

function handleDelete() {
  confirmingDelete.value = false
  emit('delete', props.publication.id)
}
</script>