<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <main class="mx-auto flex max-w-7xl flex-col gap-16 px-6 py-16 lg:px-8">
      <section class="grid items-center gap-10 rounded-3xl border border-slate-200 bg-brand-light p-10 shadow-sm lg:grid-cols-2">
        <div class="space-y-6">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-blue">AzizDev Animals</p>
          <h1 class="text-4xl font-semibold tracking-tight text-black sm:text-5xl">Discover the animal world with clarity and confidence.</h1>
          <p class="max-w-xl text-lg text-slate-700">Explore animals, categories, habitats and characteristics through a professional educational platform designed for modern browsing.</p>
          <div class="flex flex-wrap gap-4">
            <router-link to="/animals" class="rounded-full bg-brand-blue px-6 py-3 font-medium text-white transition hover:bg-blue-700">Explore Animals</router-link>
            <router-link to="/register" class="rounded-full border border-brand-blue px-6 py-3 font-medium text-brand-blue transition hover:bg-blue-50">Create Account</router-link>
          </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <img src="https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=900&q=80" alt="Wildlife scene" class="h-80 w-full rounded-xl object-cover" />
        </div>
      </section>

      <section class="grid gap-8 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 p-8">
          <h2 class="text-2xl font-semibold text-black">About AzizDev Animals</h2>
          <p class="mt-4 text-slate-700">AzizDev Animals brings together animal information in a clear, modern experience. Guests can explore public content, while registered users unlock richer details and personalized features.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 p-8">
          <h2 class="text-2xl font-semibold text-black">What you can explore</h2>
          <ul class="mt-4 space-y-3 text-slate-700">
            <li>Discover animals by category</li>
            <li>Search by name or scientific name</li>
            <li>Learn about habitats, diet and characteristics</li>
            <li>Access richer information by creating an account</li>
          </ul>
        </div>
      </section>
    </main>
    <Footer />
  </div>
</template>

<script setup lang="ts">
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
</script>
