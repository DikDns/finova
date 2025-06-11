<script setup lang="ts">
import Heading from '@/components/common/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: '/settings/profile',
    },
    {
        title: 'Kata Sandi',
        href: '/settings/password',
    },
];

const page = usePage<SharedData>();

const currentPath = page.props.ziggy.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="mx-auto min-h-svh px-4 py-6 md:max-w-5xl">
        <Heading title="Pengaturan" description="Kelola profil dan pengaturan akun Anda" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-y-0 lg:space-x-12">
            <aside class="w-full max-w-lg lg:w-48">
                <nav class="flex flex-col space-y-2 space-x-0">
                    <Button class="mb-4 w-full justify-start" variant="ghost" as-child>
                        <Link :href="route('budgets')">
                            <ArrowLeft class="h-4 w-4" />
                            Kembali
                        </Link>
                    </Button>

                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
