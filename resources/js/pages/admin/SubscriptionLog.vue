<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AdminSideBar from '@/components/admin/AdminSideBar.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Search, ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface SubscriptionLogEntry {
  id: string
  user: string
  invoice: string
  payment_method: string
  start_date: string
  end_date: string
  created_at: string
  updated_at: string
}

interface SubscriptionLogsData {
  data: SubscriptionLogEntry[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const props = defineProps<{
  subscriptionLogs: SubscriptionLogsData
  filters: {
    search?: string
    per_page: number
  }
}>()

const searchQuery = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)

const search = () => {
  router.get(route('admin.subscriptionlog'), {
    search: searchQuery.value,
    per_page: perPage.value,
    page: 1
  }, {
    preserveState: true,
    replace: true
  })
}

const goToPage = (page: number) => {
  router.get(route('admin.subscriptionlog'), {
    search: searchQuery.value,
    per_page: perPage.value,
    page: page
  }, {
    preserveState: true,
    replace: true
  })
}

// Watch for search input changes and debounce
let searchTimeout: ReturnType<typeof setTimeout>
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    search()
  }, 500)
})
</script>


<template>
  <div class="flex min-h-screen">
    <Head title="Finova - Admin - User Log" />

    <!-- Import SideBar Component -->
    <AdminSideBar />
    
    <!-- Main Content -->
    <main class="flex-1 lg:ml-52 pt-16 px-4 sm:px-6 lg:px-10 pb-10">
      <Head title="Finova - Admin - Subscription Log" />

      <!-- Header Section -->
      <h1 class="text-primary text-2xl lg:text-4xl font-bold mb-2 font-serif">Subscription Log</h1>
      <p class="text-muted-foreground text-sm lg:text-base">Daftar aktivitas subscription pengguna dalam sistem</p>

      <!-- Search and Filter Section -->
      <div class="mb-6 mt-6 flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
          <Input
            v-model="searchQuery"
            placeholder="Search by user, invoice, payment method, start date or end date..."
            class="pl-10"
          />
        </div>
        <select
          v-model="perPage"
          @change="search"
          class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="10">10 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
          <option value="100">100 per page</option>
        </select>
      </div>

      <!-- Table -->
      <Table>
        <TableHeader>
          <TableRow class="bg-gray-100 dark:bg-gray-900 text-gray-600 text-sm">
            <TableHead class="w-60 whitespace-nowrap">User</TableHead>
            <TableHead class="w-15 whitespace-nowrap">Invoice</TableHead>
            <TableHead class="w-60 whitespace-nowrap">Payment Method</TableHead>
            <TableHead class="w-36 whitespace-nowrap">Start Date</TableHead>
            <TableHead class="w-60 whitespace-nowrap">End Date</TableHead>
            <TableHead class="w-44 whitespace-nowrap">Created At</TableHead>
            <TableHead class="w-44 whitespace-nowrap">Updated At</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-for="log in subscriptionLogs.data" :key="log.id">
            <TableRow class="hover:bg-gray-100 dark:hover:bg-gray-700">
              <TableCell class="font-medium">{{ log.user }}</TableCell>
              <TableCell>{{ log.invoice }}</TableCell>
              <TableCell>{{ log.payment_method }}</TableCell>
              <TableCell>{{ log.start_date }}</TableCell>
              <TableCell>{{ log.end_date }}</TableCell>
              <TableCell>{{ log.created_at }}</TableCell>
              <TableCell>{{ log.updated_at }}</TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>

      <!-- Pagination & Empty State -->
      <div class="flex justify-between items-center mt-6">
        <div class="text-sm text-gray-600">
          Page {{ subscriptionLogs.current_page }} of {{ subscriptionLogs.last_page }} ({{ subscriptionLogs.total }} total entries)
        </div>
        <div class="flex space-x-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="subscriptionLogs.current_page === 1"
            @click="goToPage(subscriptionLogs.current_page - 1)"
          >
            <ChevronLeft class="h-4 w-4 mr-1" /> Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="subscriptionLogs.current_page === subscriptionLogs.last_page"
            @click="goToPage(subscriptionLogs.current_page + 1)"
          >
            Next <ChevronRight class="h-4 w-4 ml-1" />
          </Button>
        </div>
      </div>

      <div v-if="subscriptionLogs.data.length === 0" class="text-center py-12">
        <p class="text-muted-foreground">Tidak ada log aktivitas subscription pengguna</p>
      </div>
    </main>
  </div>
</template>
