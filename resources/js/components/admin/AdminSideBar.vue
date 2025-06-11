<script setup lang="ts">
import { ref } from 'vue'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue'
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue'
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'
import { UserCircle2, Menu } from 'lucide-vue-next'

export interface MenuItem {
  name: string
  route: string
}

defineProps<{
  items?: MenuItem[]
  user?: {
    name: string
    email: string
  }
}>()

const defaultItems = [
  { name: 'Dashboard', route: '/admin/dashboard' },
  { name: 'Account', route: '/admin/account' },
  { name: 'User Log', route: '/admin/userlog' },
  { name: 'Subscription', route: '/admin/subscription' },
  { name: 'Group Category', route: '/admin/group-category' },
]

const openMobile = ref(false)
const setOpenMobile = (value: boolean) => {
  openMobile.value = value
}

const handleLogout = () => {
  router.post('/logout')
}

// Expose mobile toggle function untuk digunakan dari parent
defineExpose({
  toggleMobile: () => setOpenMobile(!openMobile.value)
})
</script>

<template>
  <!-- Mobile Header dengan hamburger menu -->
  <div class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-900 border-b px-4 py-3">
    <div class="flex items-center justify-between">
      <h1 class="text-lg font-bold font-serif">Admin Panel</h1>
      <Sheet :open="openMobile" @update:open="setOpenMobile">
        <SheetTrigger as-child>
          <Button variant="ghost" size="sm" class="p-2">
            <Menu class="w-5 h-5" />
          </Button>
        </SheetTrigger>
        <SheetContent
          side="left"
          class="w-64 p-0 flex flex-col font-serif"
        >
          <SheetHeader class="sr-only">
            <SheetTitle>Sidebar</SheetTitle>
            <SheetDescription>Admin Sidebar</SheetDescription>
          </SheetHeader>
          
          <!-- Profile Section Mobile -->
          <div class="p-4 border-b">
            <div class="flex items-center space-x-3">
              <UserCircle2 class="w-8 h-8 text-muted-foreground" />
              <div>
                <p class="text-sm font-bold font-serif">Administrator</p>
                <p class="text-xs text-muted-foreground font-serif">{{ user?.email }}</p>
              </div>
            </div>
          </div>
          
          <nav class="flex flex-col flex-grow space-y-1 p-4">
            <RouterLink
              v-for="item in items ?? defaultItems"
              :key="item.route"
              :to="item.route"
              class="text-sm font-medium text-muted-foreground hover:text-foreground px-3 py-2 rounded-md hover:bg-muted transition-colors font-serif"
              @click="setOpenMobile(false)"
            >
              {{ item.name }}
            </RouterLink>
          </nav>

          <!-- Logout Section Mobile -->
          <div class="p-4 border-t mt-auto">
            <Button
              variant="destructive"
              size="sm"
              as="button"
              class="w-full font-serif"
              @click="handleLogout"
            >
              Logout
            </Button>
          </div>
        </SheetContent>
      </Sheet>
    </div>
  </div>

  <!-- Sidebar untuk desktop -->
  <aside class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-52 lg:flex-col border-r z-40 font-serif bg-white dark:bg-gray-900">
    <!-- Profile Section -->
    <div class="p-4 border-b">
      <div class="flex items-center space-x-3">
        <UserCircle2 class="w-8 h-8 text-muted-foreground" />
        <div>
          <p class="text-lg font-serif text-primary">Administrator</p>
          <p class="text-xs text-muted-foreground font-serif">{{ user?.email }}</p>
        </div>
      </div>
    </div>
    
    <nav class="flex flex-col flex-grow space-y-1 p-4">
      <RouterLink
        v-for="item in items ?? defaultItems"
        :key="item.route"
        :to="item.route"
        class="text-sm font-medium text-muted-foreground hover:text-foreground px-3 py-2 rounded-md hover:bg-primary transition-colors font-serif"
      >
        {{ item.name }}
      </RouterLink>
    </nav>

    <!-- Logout Section -->
    <div class="p-4 border-t">
      <Button
        variant="destructive"
        size="sm"
        as="button"
        class="w-full font-serif"
        @click="handleLogout"
      >
        Logout
      </Button>
    </div>
  </aside>
</template>