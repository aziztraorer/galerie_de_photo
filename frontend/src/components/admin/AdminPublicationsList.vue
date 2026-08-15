<template>
  <div v-if="adminStore.publications.length === 0" class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500">
    Aucune publication trouvée.
  </div>
  
  <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    <AdminPublicationCard
      v-for="publication in adminStore.publications"
      :key="publication.id"
      :publication="publication"
      @deleted="handlePublicationDeleted"
    />
  </div>
</template>

<script setup lang="ts">
import AdminPublicationCard from './AdminPublicationCard.vue'
import { useAdminStore } from '../../stores/admin'

const adminStore = useAdminStore()

function handlePublicationDeleted(id: number) {
  adminStore.removePublication(id)
}
</script>