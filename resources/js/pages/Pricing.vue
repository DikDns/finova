<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CheckCircle } from 'lucide-vue-next';
// Define pricing packages
const packages = ref([
  {
    id: 'starter',
    name: 'Starter',
    price: '2,500',
    period: 'bulan',
    features: [
      'Coba fitur standar selama 7 hari',
      'Laporan keuangan dasar',
      'Support via email',
    ],
    popular: false
  },
  {
    id: 'premium',
    name: 'Premium',
    price: '3,000',
    period: 'bulan',
    features: [
      'Semua fitur Starter',
      'Fitur laporan lengkap',
      'Akses penuh integrasi',
      'Support prioritas 24/7', 
      'Update fitur terbaru'
    ],
    popular: true
  }
]);
</script>

<template>
    <h1 class="font-serif text-2xl font-bold">Pricing</h1>

    <!-- Template Navigation -->
    <div class="mt-8 flex gap-x-2">
        <Link
            :href="route('home')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >Home</Link
        >
        <Link
            :href="route('features')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >Features</Link
        >
        <Link
            :href="route('pricing')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >Pricing</Link
        >
    </div>
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
          <Link :href="route('features')" class="text-1xl font-medium text-gray-700 hover:text-[#284A63]">Features</Link>
          <Link :href="route('pricing')" class="text-1xl font-bold text-gray-900 hover:text-[#284A63]">Pricing</Link>
        </nav>
        <Link :href="route('login')" class="text-1xl font-medium text-gray-700 me-12 hover:text-[#284A63]">Login</Link>
        <Link :href="route('register')" class="rounded-[16px] bg-[#284A63] px-4 py-2 text-sm font-medium text-white hover:bg-white hover:text-[#284A63]">Begin Planning</Link>
      </div>
    </header>
    <hr class="border-[#D9D9D9] border-2">
    <!-- Hero Section -->
    <section class="py-16 text-center bg-white">
      <div class="container mx-auto px-4">
        <h1 class="mb-4 text-5xl font-bold text-black">Upgrade Finansialmu, <br /><span class="text-[#284A63]">Gak Pakai Ribet!</span></h1>

        <!-- Feature Icons -->
        <div class="mt-12 flex flex-wrap items-center justify-center gap-16">
          <div class="flex flex-col items-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#EFEFF0]">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="6" width="18" height="15" rx="2" stroke="currentColor" stroke-width="2" />
                <path d="M4 11H20" stroke="currentColor" stroke-width="2" />
                <path d="M9 16H15" stroke="currentColor" stroke-width="2" />
                <path d="M8 3L8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                <path d="M16 3L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
              </svg>
            </div>
            <p class="mb-1 text-center text-sm font-medium text-black">Langanan Per Hari, Hitung<br />Pakai Simple!</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#AFB1B6]">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <p class="mb-1 text-center text-sm font-medium text-black">Gak Pakai Ribet Ngatur<br />Keuangan Lagi</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#AFB1B6]">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2" />
                <path d="M9 10L11 12L15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <p class="mb-1 text-center text-sm font-medium text-black">Stop Tebak-Tebak<br />Pengeluaran Bulanan</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="bg-white">
      <div class="container mx-auto px-4">
        <div class="mx-auto max-w-3xl text-center">
          <!-- Pricing Table -->
          <div class="mx-auto max-w-xl rounded-md bg-[#EFEFF0] p-4">
            <h2 class="mb-6 text-3xl font-bold text-black">Pilih Paket Sesuai <br>Kebutuhan!</h2>
            <p class="mb-6 text-gray-600">Coba demo secara gratis, Suka? Mulai dari Rp2.500/hari <br>untuk akses fitur mantap lainnya!</p>

            <!-- Pricing Options -->
            <div class="overflow-hidden rounded-md">
              <!-- Bulanan Option (Selected) -->
              <div class="flex items-center justify-between bg-white p-4">
                <div class="flex items-center">
                  <div class="mr-3 flex h-6 w-6 items-center justify-center rounded-full border-2 border-[#284A63] bg-[#284A63]">
                    <div class="h-2 w-2 rounded-full bg-white"></div>
                  </div>
                  <span class="font-medium text-black">Bulanan</span>
                </div>
                <div class="text-left">
                  <div class="ml-4 inline-block rounded-md bg-green-100 px-2 py-0.5 text-xs font-bold text-green-700">
                    Hemat 17%
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-gray-800">Rp2.500<span class="text-sm text-gray-500">/hari</span></div>
                  <div class="text-xs text-gray-500">Langganan Rp75.000* setiap awal bulan</div>
                </div>
              </div>

              <!-- Harian Option -->
              <div class="flex items-center justify-between border-t border-gray-200 bg-white p-4">
                <div class="flex items-center">
                  <div class="mr-3 flex h-6 w-6 items-center justify-center rounded-full border-2 border-gray-300">
                  </div>
                  <span class="font-medium text text-black">Harian</span>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-gray-500">Rp3.000<span class="text-sm text-gray-500">/hari</span></div>
                </div>
              </div>
            </div>

            <!-- CTA Button -->
            <button class="mb-6 mt-4 w-full rounded-md bg-[#284A63] py-3 font-medium text-white">
              Coba Sekarang!
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white text-left">
      <div class="container mx-auto px-4">
        <div class="mx-auto max-w-3xl">
          <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
            <div class="max-w-lg">
              <h2 class="mb-4 text-3xl font-bold text-black ">Upgrade &<br> Nikmati Fitur <br>Lengkap!</h2>
              <p class="mb-6 text-gray-600">
                Cuan lancar, finansial stabil <br>Upgrade sekarang biar duit tetap aman dan terkontrol! 
              </p>
              <div class="flex flex-wrap gap-4">
                <button class="rounded-md bg-[#284A63] px-6 py-2 font-medium text-white">
                  Mulai Gratis Sekarang
                </button>
                <button class="rounded-md border border-gray-300 px-6 py-2 font-medium text-gray-700 hover:bg-[#284A63] hover:text-white">
                  Pelajari Fitur Lengkap
                </button>
              </div>
            </div>
            <div class="flex justify-center">
              <img src="../../../public/handMoney.png" alt="Feature illustration" class="max-w-xs" />
            </div>
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