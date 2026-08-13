<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <div class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
      <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">Animal details</p>
        <h1 class="mt-3 text-3xl font-semibold text-black">Detailed animal information</h1>
        <p class="mt-4 text-slate-700">This page will display the full animal content and the protected information state for authenticated users.</p>
      </div>
    </div>
    <Footer />
  </div>
</template>

<script setup lang="ts">
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
</script>
