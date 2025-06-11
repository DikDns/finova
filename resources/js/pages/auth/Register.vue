<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
// import TextLink from '@/components/TextLink.vue';
// import { Button } from '@/components/ui/button';
// import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
// import AuthBase from '@/layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    username: '',
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="flex h-screen w-screen flex-col bg-[#f0f4f8] md:flex-row">
        <div class="relative h-[30vh] w-full md:h-full md:w-1/2">
            <img src="womanLaptop.webp" alt="woman-laptop" class="h-full w-full object-cover" />
            <div class="absolute right-4 bottom-4 rounded-lg bg-[#ffffff] p-4 sm:right-5 sm:left-5 md:right-10 md:bottom-10 md:left-10 md:p-10">
                <p class="mb-1.5 text-xl font-bold text-[#000000] md:text-3xl">Coba demo secara gratis</p>
                <p class="text-xs text-[#666] md:text-sm">
                    Sebagian besar finover hemat Rp50.000 di dua bulan pertama mereka (dan kamu pasti lebih besar dari mereka)
                </p>
            </div>
            <div
                class="absolute top-4 left-4 flex items-center rounded-full bg-[#ffffff] pt-2 pr-4 pb-2 pl-4 text-[#000000] shadow-md md:top-10 md:left-10 md:pt-2.5 md:pr-5 md:pb-2.5 md:pl-5"
            >
                <div class="pr-2 text-base text-[#000000] md:pr-4 md:text-xl">
                    <i class="fa-solid fa-arrow-left"></i>
                </div>
                <button class="back-btn" onclick="history.back()">Kembali</button>
            </div>
        </div>
        <div class="flex w-full items-center justify-center bg-[#ffffff] p-4 md:w-1/2 md:pt-50 md:pr-0 md:pb-50 md:pl-0">
            <div class="w-full max-w-lg">
                <h1 class="mb-2.5 text-center text-2xl text-[#000000] md:text-4xl">Atur keuanganmu sekarang!</h1>
                <p class="mb-4 text-center text-[#000000] md:mb-8">Tanpa kartu kredit!</p>
                <form @submit.prevent="submit" class="p-4 md:p-8">
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Username</label>
                        <input
                            v-model="form.username"
                            type="text"
                            class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                            required
                            placeholder="Masukan username..."
                        />
                        <InputError :message="form.errors.username" />
                    </div>
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                            required
                            placeholder="Masukan email..."
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[#000000]">Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                class="w-full border-0 border-b border-b-[#ccc] p-2.5 text-[#000000] focus:border-b-[#007bff] focus:outline-0"
                                type="password"
                                required
                                placeholder="Masukan password..."
                            />
                            <span class="absolute top-1/2 right-0 -translate-y-1/2 cursor-pointer">👁</span>
                            <InputError :message="form.errors.password" />
                        </div>
                    </div>
                    <div class="pt-6 md:pt-8">
                        <button
                            type="submit"
                            class="w-full cursor-pointer rounded-3xl border-0 bg-[#284a63] p-3 text-[#ffffff] transition-colors duration-300 hover:bg-[#dff6ff] hover:text-[#284a63] disabled:opacity-75 md:rounded-4xl md:p-4"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 inline h-4 w-4 animate-spin" />
                            Mulai Demo
                        </button>
                    </div>
                </form>
                <div class="mt-4 text-center text-sm text-[#000000]">
                    <p>Sudah punya akun? <a :href="route('login')" class="text-[#007bff] decoration-0">Masuk disini</a></p>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- <div class="flex flex-row justify-center items-center h-screen bg-gray-100">
        <div>
            <img src="/public/womanLaptop.webp" alt="Splash Image">
        </div>
        <div class="">
            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            v-model="form.password"
                            placeholder="Password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Create account
                    </Button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    Already have an account?
                    <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
                </div>
            </form>
        </div>
    </div> -->
