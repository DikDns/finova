<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// buat slide fitur
const activeSlide = ref(0);

const changeSlide = (index) => {
  activeSlide.value = index;
};
</script>

<template>
  <h1 class="font-serif text-2xl font-bold">Features</h1>
  
  <!-- Main Content -->
  <div class="min-h-screen bg-white">
    <!-- Header Navigation -->
    <header class="py-4">
      <div class="container mx-auto flex items-center px-4">
        <!-- Logo -->
        <Link :href="route('home')" class="text-2xl font-bold me-12 text-blue-600">
          <img src="../../../public/finova-logo.svg" alt="Finova Logo" class="h-12" />
        </Link>

        <!-- Navigation Menu -->
        <nav class="flex align-items-start me-auto space-x-12">
          <Link :href="route('features')" class="text-1xl font-bold text-gray-900 hover:text-[#284A63]">Features</Link>
          <Link :href="route('pricing')" class="text-1xl font-medium text-gray-700 hover:text-[#284A63]">Pricing</Link>
        </nav>
        <Link :href="route('login')" class="text-1xl font-medium text-gray-700 me-12 hover:text-[#284A63]">Login</Link>
        <Link :href="route('register')" class="rounded-[16px] bg-[#284A63] px-4 py-2 text-sm font-medium text-white hover:bg-white hover:text-[#284A63]">Begin Planning</Link>
      </div>
    </header>
    <hr class="border-[#D9D9D9] border-2">

    <!-- Hero Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
          <div class="md:w-1/2">
            <h1 class="mb-4 text-5xl font-bold text-black">Atur Keuangan <span class="text-[#284A63]">Jadi Lebih Mudah!</span></h1>
            <p class="mb-6 text-gray-600">Pantau pemasukan dan pengeluaran, dapatkan rekomendasi keuangan berbasis AI, dan buat laporan otomatis dalam satu platform.</p>
            <Link :href="route('register')" class="rounded-md bg-[#284A63] px-6 py-2 font-medium text-white">Begin Planning</Link>
          </div>
          <div class="md:w-1/2 text-right">
            <img src="../../../public/atur-keuangan.png" alt="peeps" class="max-w-md mx-auto">
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4 text-center">
        <h2 class="mb-4 text-3xl font-bold">Fitur Andalan Kami</h2>
        <p class="mb-12 text-gray-600">Fitur yang bikin keuanganmu lebih teratur dan terkontrol.</p>
        
        <div class="relative">
          <!-- Features Slider -->
          <div class="overflow-hidden">
            <!-- Slides -->
            <div class="flex transition-transform duration-300" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
              <!-- Slide 1 -->
              <div class="w-full flex-shrink-0 px-4">
                <div class="rounded-lg bg-white p-6 shadow-md">
                  <img src="../../../public/management.png" alt="Manajemen Keuangan" class="mx-auto mb-4 h-48">
                  <h5 class="text-xl font-bold">Manajemen Keuangan</h5>
                </div>
              </div>
              
              <!-- Slide 2 -->
              <div class="w-full flex-shrink-0 px-4">
                <div class="rounded-lg bg-white p-6 shadow-md">
                  <img src="../../../public/rekomendasi-ai.png" alt="Rekomendasi AI" class="mx-auto mb-4 h-48">
                  <h5 class="text-xl font-bold">Rekomendasi AI</h5>
                </div>
              </div>
              
              <!-- Slide 3 -->
              <div class="w-full flex-shrink-0 px-4">
                <div class="rounded-lg bg-white p-6 shadow-md">
                  <img src="../../../public/alert.png" alt="Alert Batas Bulanan" class="mx-auto mb-4 h-48">
                  <h5 class="text-xl font-bold">Alert Batas Bulanan</h5>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination Dots -->
          <div class="mt-8 flex justify-center gap-2">
            <button 
              v-for="index in 3" 
              :key="index-1"
              @click="changeSlide(index-1)" 
              class="h-3 w-3 rounded-full"
              :class="activeSlide === index-1 ? 'bg-[#284A63]' : 'bg-gray-300'"
              aria-label="Change slide"
            ></button>
          </div>
        </div>
      </div>
    </section>

    <!-- Zero Cost Budgeting Section -->
    <section class="py-16 bg-[#f8f9fa]">
      <div class="container mx-auto px-4">
        <div class="flex flex-col-reverse md:flex-row items-center">
          <div class="md:w-1/2">
            <img src="../../../public/zero-cost.jpg" alt="Zero Cost Budgeting" class="max-w-md mx-auto">
          </div>
          <div class="md:w-1/2 md:pl-12">
            <h2 class="mb-4 text-3xl font-bold">Apa itu Zero <span class="text-[#284A63]">Cost Budgeting Method?</span></h2>
            <p class="mb-6 text-gray-600">Cara ngatur uang biar tiap pemasukan punya tujuan jelas. AI bantu kamu bikin rencana pengeluaran yang pas.</p>
            <Link :href="route('features')" class="rounded-md bg-[#284A63] px-6 py-2 font-medium text-white">Learn More</Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Kelola Uang Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
          <div class="md:w-1/2">
            <h2 class="mb-4 text-3xl font-bold">Waktunya Kelola <span class="text-[#284A63]">Uang Lebih Baik!</span></h2>
            <p class="mb-6 text-gray-600">Bergabung dengan platform kami untuk mendapatkan perencanaan keuangan berbasis AI, notifikasi batas anggaran, dan laporan keuangan otomatis. Yuk mulai atur keuanganmu sekarang. Gratis & gampang!</p>
            <div class="flex flex-wrap gap-4">
              <Link :href="route('register')" class="rounded-md bg-[#284A63] px-6 py-2 font-medium text-white">Begin Planning</Link>
              <Link :href="route('pricing')" class="rounded-md border border-gray-300 px-6 py-2 font-medium text-gray-700 hover:bg-[#284A63] hover:text-white">See Pricing</Link>
            </div>
          </div>
          <div class="md:w-1/2 text-right">
            <img src="../../../public/hand-money.png" alt="Money" class="max-w-md mx-auto">
          </div>
        </div>
      </div>
    </section>

    <hr class="border-[#D9D9D9] border-2">
    
    <!-- Footer -->
    <footer class="py-8 bg-white">
      <div class="container mx-auto px-4 text-center text-sm text-gray-600">
        Copyright © 2025, Finova. All Rights Reserved.
      </div>
    </footer>
  </div>
</template>