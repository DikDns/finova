<script setup lang="ts">
import Navbar from '@/components/common/NavBar.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart, Bell, DollarSign, PieChart, Wallet } from 'lucide-vue-next';

const fiturItems = [
    { icon: DollarSign, title: 'Atur Keuangan', color: '#4f46e5' },
    { icon: Bell, title: 'Pengingat Pengeluaran', color: '#4f46e5' },
    { icon: Wallet, title: 'Kelola Tabungan', color: '#4f46e5' },
    { icon: BarChart, title: 'Laporan Pengeluaran', color: '#4f46e5' },
    { icon: PieChart, title: 'Laporan Pemasukan', color: '#4f46e5' },
];
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <Head title="Finova - Finansial dalam Genggaman" />

        <!-- Import Navbar Component -->
        <Navbar />

        <!-- Hero Section -->
        <section class="bg-muted flex-grow py-16">
            <div class="container mx-auto px-4 sm:px-6 md:px-12">
                <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2">
                    <div class="space-y-5 text-center md:text-left">
                        <h1 class="text-4xl leading-tight font-bold sm:text-5xl md:text-6xl">
                            <span class="text-muted-foreground font-serif">Finansial dalam</span><br />
                            <span class="text-primary font-serif">Genggaman</span>
                        </h1>
                        <p class="text-muted-foreground">Kelola keuangan dengan Finova</p>
                        <div class="flex justify-center pt-6 md:justify-start">
                            <Button as-child>
                                <Link href="/register">Mulai Hidup Elegan</Link>
                            </Button>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <img src="/peeps.webp" alt="Hero Image" class="h-auto w-full max-w-md" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonial Section -->
        <section class="bg-muted py-16">
            <div class="container mx-auto px-4 text-center sm:px-6 md:px-12">
                <h2 class="text-muted-foreground mx-auto mb-8 max-w-3xl text-2xl font-medium italic">
                    "Finova bukan sekadar aplikasi, tapi kunci keuangan yang lebih teratur dan tenang."
                </h2>
                <p class="text-lg font-medium">
                    <span class="text-muted-foreground">Rafi Zamzami</span>,
                    <span class="text-muted-foreground/70">Media Social Student Associate</span>
                </p>

                <div class="mt-16 grid grid-cols-2 items-center gap-6 sm:grid-cols-3 md:grid-cols-4">
                    <template v-for="logo in ['/ascendia.webp', '/himarpl.webp', '/manifest.webp', '/explora.webp']" :key="logo">
                        <div class="flex justify-center">
                            <img
                                :src="logo"
                                alt="Partner Logo"
                                class="h-12 opacity-25 brightness-0 grayscale transition duration-300 hover:opacity-100 hover:brightness-100 hover:grayscale-0"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- Pengingat Batas Pengeluaran Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4 sm:px-6 md:px-12">
                <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2">
                    <div class="flex justify-center">
                        <img src="/bell.webp" alt="Notification Bell" class="h-auto w-full max-w-md" />
                    </div>
                    <div class="space-y-6 text-center md:text-left">
                        <h1 class="text-foreground font-serif text-4xl leading-tight font-bold sm:text-5xl md:text-6xl">
                            Pengingat Batas Pengeluaran
                        </h1>
                        <p class="text-muted-foreground text-lg">
                            Fitur ini membantu mu mengelola keuangan dengan memberikan pengingat saat pengeluaran mencapai batas yang ditetapkan.
                        </p>
                        <div class="flex justify-center pt-4 md:justify-start">
                            <Button as-child class="bg-primary/90 hover:bg-primary text-background rounded-full">
                                <Link href="/features/expense-alert">Pelajari Lebih Lanjut</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Carousel Section -->
        <section class="bg-muted py-16">
            <div class="container mx-auto space-y-6 px-4 text-center sm:px-6 md:px-12">
                <h2 class="text-muted-foreground font-serif text-4xl font-bold sm:text-5xl md:text-6xl">Bisa ngapain aja sih Finova?</h2>
                <p class="text-muted-foreground mx-auto max-w-xl text-base">Finova bisa untuk...</p>

                <div class="overflow-hidden" ref="embla">
                    <div class="embla__container flex space-x-6">
                        <div
                            v-for="(item, index) in fiturItems"
                            :key="index"
                            class="bg-card embla__slide w-full flex-shrink-0 rounded-lg p-6 text-center shadow-md sm:max-w-xs md:w-1/2 lg:w-1/3"
                        >
                            <div class="mb-4 flex justify-center">
                                <component :is="item.icon" class="mx-auto h-10 w-10" :style="{ color: item.color }" />
                            </div>
                            <h3 class="text-card-foreground text-xl font-semibold">{{ item.title }}</h3>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-center gap-3" ref="emblaDots">
                    <button
                        v-for="n in fiturItems.length"
                        :key="n"
                        class="bg-muted-foreground/40 embla-dot h-3 w-3 rounded-full"
                        :class="{ 'bg-muted-foreground/70': n === 1 }"
                    />
                </div>
            </div>
        </section>

        <!-- Kontrol Uangmu Section -->
        <section class="bg-background py-16">
            <div class="container mx-auto px-4 sm:px-6 md:px-12">
                <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2">
                    <div class="space-y-6 text-center md:text-left">
                        <h1 class="text-foreground font-serif text-4xl leading-tight font-bold sm:text-5xl md:text-6xl">Uangmu Kendalimu</h1>
                        <p class="text-muted-foreground text-lg">Kontrol penuh atas keuangan pribadi Anda dengan fitur yang komprehensif</p>
                        <div class="flex flex-wrap justify-center gap-4 pt-4 md:justify-start">
                            <Button as-child class="bg-primary/90 text-background hover:bg-primary rounded-full">
                                <Link href="/features/money-control">Mulai Kendalikan Uangmu</Link>
                            </Button>
                            <Button as-child class="bg-primary/90 text-background hover:bg-primary rounded-full">
                                <Link href="/features">Lihat Fitur Lainnya</Link>
                            </Button>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <img src="/handMoney.webp" alt="Hand picking gold coins" class="h-auto w-full max-w-md" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-transparent py-8">
            <div class="container mx-auto px-4 text-center sm:px-6 md:px-12">
                <p class="text-muted-foreground">© 2025 Finova. All Rights Reserved.</p>
            </div>
        </footer>
    </div>
</template>
