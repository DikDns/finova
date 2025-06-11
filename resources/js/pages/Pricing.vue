<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import RadioGroup from '@/components/ui/radio-group/RadioGroup.vue';
import RadioGroupItem from '@/components/ui/radio-group/RadioGroupItem.vue';
import { Separator } from '@/components/ui/separator';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface MoneyIcon {
    image: string;
    title: string;
}

interface PricingPlan {
    id: string;
    name: string;
    price: string;
    period: string;
    discount?: string;
    description?: string;
}

const selectedPlan = ref('bulanan');

const moneyIcons: MoneyIcon[] = [
    {
        image: '/money_1.webp',
        title: 'Langganan Perhari, Hidup Jadi Simple!',
    },
    {
        image: '/money_2.webp',
        title: 'Gak Perlu Ribet Ngatur Keuangan Lagi!',
    },
    {
        image: '/money_3.webp',
        title: 'Stop Tebak-Tebak Pengeluaran Bulanan!',
    },
];

const pricingPlans: PricingPlan[] = [
    {
        id: 'bulanan',
        name: 'Bulanan',
        price: 'Rp2.500',
        period: '/hari',
        discount: 'Hemat 17%',
        description: 'Langganan Rp75.000 setiap awal bulan',
    },
    {
        id: 'harian',
        name: 'Harian',
        price: 'Rp3.000',
        period: '/hari',
    },
];
</script>

<template>
    <div class="bg-background text-foreground min-h-screen">
        <AppNavbar />
        <Separator />

        <main class="py-16">
            <section class="container mx-auto mb-16 px-4 text-center">
                <h1 class="mb-12 text-4xl font-bold md:text-5xl">
                    Upgrade Finansialmu, <br />
                    <span class="text-primary">Gak Pakai Ribet!</span>
                </h1>

                <div class="mb-16 grid gap-8 md:grid-cols-3">
                    <div v-for="(icon, index) in moneyIcons" :key="index" class="flex flex-col items-center">
                        <img :src="icon.image" :alt="icon.title" class="mb-4 h-24 w-24" loading="lazy" />
                        <h6 class="text-foreground text-center font-medium">{{ icon.title }}</h6>
                    </div>
                </div>

                <Card class="bg-card text-card-foreground mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle class="text-2xl font-bold">Pilih Paket Sesuai Kebutuhan!</CardTitle>
                        <CardDescription>
                            <div class="text-muted-foreground flex flex-col items-center">
                                <p>Coba demo secara gratis. Suka? Mulai dari Rp2.500/hari.</p>
                                <p>Untuk akses fitur mantap lainnya!</p>
                            </div>
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <RadioGroup v-model="selectedPlan" class="space-y-4">
                            <div
                                v-for="plan in pricingPlans"
                                :key="plan.id"
                                class="border-muted hover:border-primary rounded-lg border-2 p-4 transition-all"
                            >
                                <RadioGroupItem :value="plan.id" :id="plan.id" class="peer sr-only" />
                                <label :for="plan.id" class="flex cursor-pointer flex-col space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">{{ plan.name }}</span>
                                        <div class="flex items-center">
                                            <span
                                                v-if="plan.discount"
                                                class="me-6 rounded-full bg-green-100 px-2 py-1 text-xs font-bold text-green-700"
                                            >
                                                {{ plan.discount }}
                                            </span>
                                            <span class="text-2xl font-extrabold">{{ plan.price }}</span
                                            ><span>{{ plan.period }}</span>
                                        </div>
                                    </div>

                                    <span v-if="plan.description" class="text-muted-foreground text-right text-sm">
                                        {{ plan.description }}
                                    </span>
                                </label>
                            </div>
                        </RadioGroup>

                        <Button as-child class="bg-primary hover:bg-primary/90 text-primary-foreground mt-12 w-full rounded-full font-bold">
                            <Link :href="route('register')">Coba Sekarang!</Link>
                        </Button>
                    </CardContent>
                </Card>
            </section>

            <!-- <Separator class="my-16" /> -->

            <section class="container mx-auto px-4">
                <div class="flex flex-col items-center gap-12 md:flex-row">
                    <div class="md:w-1/2">
                        <h2 class="mb-4 text-3xl font-bold">
                            Upgrade &<br />
                            Nikmati Fitur Lengkap!
                        </h2>
                        <p class="text-muted-foreground mb-8">
                            Cuan lancar, finansial stabil<br />
                            Upgrade sekarang biar duit tetap aman dan terkontrol!
                        </p>
                        <div class="flex flex-col gap-4 md:flex-row">
                            <Button as-child class="bg-primary hover:bg-primary/90 text-primary-foreground rounded-full">
                                <Link href="/features/money-control">Mulai Atur Keuanganmu</Link>
                            </Button>
                            <Button as-child class="bg-primary-foreground/90 hover:bg-primary-foreground text-background rounded-full">
                                <Link :href="route('features')">Eksplor Fitur</Link>
                            </Button>
                        </div>
                    </div>
                    <div class="md:w-1/2">
                        <img src="/handMoney.png" alt="Hand with Money" class="mx-auto max-w-md" />
                    </div>
                </div>
            </section>
        </main>

        <Separator />

        <footer class="border-border border-t py-8">
            <div class="text-muted-foreground container mx-auto px-4 text-center">
                <p>&copy; 2025 Finova. All Rights Reserved.</p>
            </div>
        </footer>
    </div>
</template>
