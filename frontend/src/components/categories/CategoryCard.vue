<template>
  <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="flex items-center justify-between">
      <div class="rounded-2xl bg-brand-light p-3 text-brand-blue">
        <component :is="iconComponent" class="h-6 w-6" />
      </div>
      <span class="text-sm text-slate-500">{{ category.animal_count ?? 0 }} animals</span>
    </div>
    <h3 class="mt-6 text-xl font-semibold text-black">{{ category.name }}</h3>
    <p class="mt-3 text-sm leading-7 text-slate-700">{{ category.description }}</p>
    <router-link :to="`/categories/${category.id}`" class="mt-6 inline-flex rounded-full bg-brand-blue px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Explore Category</router-link>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Category } from '../../types'
import { Fish, Bird, PawPrint, Bug, Sparkles, ScanEye, Shield } from 'lucide-vue-next'

const props = defineProps<{ category: Category & { animal_count?: number } }>()

const iconMap: Record<string, any> = {
  'Mammals': PawPrint,
  'Birds': Bird,
  'Reptiles': Shield,
  'Amphibians': Sparkles,
  'Fish': Fish,
  'Insects': Bug,
  'Arachnids': ScanEye
}

const iconComponent = computed(() => iconMap[props.category.name] ?? Sparkles)
</script>
