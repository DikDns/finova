<script setup lang="ts">
// Import Vue features
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

// Import components
import AdminSideBarLayout from '@/components/admin/AdminSideBarLayout.vue'
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
import { Card } from '@/components/ui/card'
import {
  Search,
  ChevronLeft,
  ChevronRight,
  Users,
  CreditCard
} from 'lucide-vue-next'

// Define interfaces for type safety
interface UserLogEntry {
  id: string
  user: string
  user_name: string
  action: string
  description: string
  ipAddress: string
  userAgent: string
  oldValues?: any
  newValues?: any
  createdAt: string
  updatedAt: string
}

interface UserLogsData {
  data: UserLogEntry[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// Props from server
const props = defineProps<{
  userLogs: UserLogsData
  filters: {
    search?: string
    per_page: number
  }
  totalUsers: number
  totalSubscriptions: number
  usersGrowth: number
  subscriptionsGrowth: number
}>()

// State
const searchQuery = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)

// Method: perform search
const search = () => {
  router.get(
    route('admin.admindashboard'),
    {
      search: searchQuery.value,
      per_page: perPage.value,
      page: 1
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

// Method: pagination
const goToPage = (page: number) => {
  router.get(
    route('admin.admindashboard'),
    {
      search: searchQuery.value,
      per_page: perPage.value,
      page: page
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

// Utility: format user agent string
const formatUserAgent = (userAgent: string) => {
  if (!userAgent) return 'Unknown'
  return userAgent.length > 50 ? userAgent.substring(0, 50) + '...' : userAgent
}

// Debounce search input
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
    <Head title="Finova - Admin - Dashboard" />
    <AdminSideBarLayout />

    <div class="flex-1 pt-12 px-4 sm:px-6 lg:px-13 pb-15">
      <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-6">
          <h1 class="text-2xl lg:text-4xl font-bold mb-2 font-serif">Dashboard</h1>
          <p class="text-muted-foreground text-sm lg:text-base">
            Daftar aktivitas pengguna dalam sistem
          </p>
        </div>

        <!-- Stats Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <Card class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-center sm:text-left space-y-3 sm:space-y-0">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                <p class="text-3xl font-bold text-gray-900">{{ totalUsers }}</p>
                <p class="text-sm mt-1" :class="usersGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ usersGrowth >= 0 ? '+' : '' }}{{ usersGrowth }}% from last month
                </p>
              </div>
              <div class="p-3 bg-blue-100 rounded-full">
                <Users class="h-8 w-8 text-blue-600" />
              </div>
            </div>
          </Card>

          <Card class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Subscriptions</p>
                <p class="text-3xl font-bold text-gray-900">{{ totalSubscriptions }}</p>
                <p class="text-sm mt-1" :class="subscriptionsGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ subscriptionsGrowth >= 0 ? '+' : '' }}{{ subscriptionsGrowth }}% from last month
                </p>
              </div>
              <div class="p-3 bg-green-100 rounded-full">
                <CreditCard class="h-8 w-8 text-green-600" />
              </div>
            </div>
          </Card>
        </div>

        <!-- Filter Section -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
            <Input
              v-model="searchQuery"
              placeholder="Search by user, action, description, or IP address..."
              class="pl-10 w-full text-sm"
            />
          </div>
          <select
            v-model="perPage"
            @change="search"
            class="w-full sm:w-auto text-sm px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-50"
          >
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
          </select>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
              <div class="px-7 py-4 border-b lg:px-7 lg:py-4 px-4 py-3">
                <h2 class="text-lg font-serif lg:text-lg text-base">User Log</h2>
                <p class="text-sm text-muted-foreground mt-1 lg:text-sm text-xs">
                  Showing {{ userLogs.data.length }} of {{ userLogs.total }} entries
                </p>
              </div>

              <!-- Desktop Table -->
              <div class="hidden lg:block">
                <Table class="min-w-full table-fixed text-sm text-left text-muted-foreground">
                  <TableHeader>
                    <TableRow class="bg-gray-100 text-muted-foreground text-sm">
                      <TableHead class="w-60 whitespace-nowrap">User</TableHead>
                      <TableHead class="w-15 whitespace-nowrap">Action</TableHead>
                      <TableHead class="w-60 whitespace-nowrap">Description</TableHead>
                      <TableHead class="w-36 whitespace-nowrap">IP Address</TableHead>
                      <TableHead class="w-60 whitespace-nowrap">User Agent</TableHead>
                      <TableHead class="w-44 whitespace-nowrap">Created At</TableHead>
                      <TableHead class="w-44 whitespace-nowrap">Updated At</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow
                      v-for="log in userLogs.data"
                      :key="log.id"
                      class="hover:bg-gray-100"
                    >
                      <TableCell class="font-medium">
                        <div>
                          <div class="font-semibold">{{ log.user }}</div>
                          <div class="text-xs text-muted-foreground">{{ log.user_name }}</div>
                        </div>
                      </TableCell>
                      <TableCell>
                        <span
                          class="px-2 py-1 text-xs rounded-full"
                          :class="{
                            'bg-green-100 text-green-800': log.action === 'Login',
                            'bg-blue-100 text-blue-800': log.action === 'Update',
                            'bg-yellow-100 text-yellow-800': log.action === 'Create',
                            'bg-red-100 text-red-800': log.action === 'Delete',
                            'bg-gray-100 text-muted-foreground': !['Login', 'Update', 'Create', 'Delete'].includes(log.action)
                          }"
                        >
                          {{ log.action }}
                        </span>
                      </TableCell>
                      <TableCell class="truncate max-w-[12rem]" :title="log.description">
                        {{ log.description }}
                      </TableCell>
                      <TableCell class="break-words whitespace-normal max-w-[10rem]">
                        {{ log.ipAddress }}
                      </TableCell>
                      <TableCell class="truncate max-w-[14rem]" :title="log.userAgent">
                        {{ formatUserAgent(log.userAgent) }}
                      </TableCell>
                      <TableCell class="whitespace-nowrap">
                        {{ log.createdAt }}
                      </TableCell>
                      <TableCell class="whitespace-nowrap">
                        {{ log.updatedAt }}
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>

              <!-- Mobile Table -->
              <div class="block lg:hidden">
                <div class="min-w-full overflow-x-auto">
                  <Table class="w-full text-xs text-left text-muted-foreground min-w-[600px] sm:min-w-full">
                    <TableHeader>
                      <TableRow class="bg-gray-100 text-muted-foreground text-xs">
                        <TableHead class="w-36 px-2 py-3 whitespace-nowrap">User</TableHead>
                        <TableHead class="w-20 px-2 py-3 whitespace-nowrap">Action</TableHead>
                        <TableHead class="w-48 px-2 py-3 whitespace-nowrap">Description</TableHead>
                        <TableHead class="w-28 px-2 py-3 whitespace-nowrap">IP Address</TableHead>
                        <TableHead class="w-40 px-2 py-3 whitespace-nowrap">User Agent</TableHead>
                        <TableHead class="w-32 px-2 py-3 whitespace-nowrap">Created At</TableHead>
                        <TableHead class="w-32 px-2 py-3 whitespace-nowrap">Updated At</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      <TableRow
                        v-for="log in userLogs.data"
                        :key="log.id"
                        class="hover:bg-gray-100"
                      >
                        <TableCell class="font-medium px-2 py-3">
                          <div class="w-36">
                            <div class="font-semibold text-xs truncate">{{ log.user }}</div>
                            <div class="text-xs text-muted-foreground truncate">{{ log.user_name }}</div>
                          </div>
                        </TableCell>
                        <TableCell class="px-2 py-3">
                          <span
                            class="px-2 py-1 text-xs rounded-full whitespace-nowrap"
                            :class="{
                              'bg-green-100 text-green-800': log.action === 'Login',
                              'bg-blue-100 text-blue-800': log.action === 'Update',
                              'bg-yellow-100 text-yellow-800': log.action === 'Create',
                              'bg-red-100 text-red-800': log.action === 'Delete',
                              'bg-gray-100 text-muted-foreground': !['Login', 'Update', 'Create', 'Delete'].includes(log.action)
                            }"
                          >
                            {{ log.action }}
                          </span>
                        </TableCell>
                        <TableCell class="px-2 py-3">
                          <div class="w-48 truncate text-xs" :title="log.description">
                            {{ log.description }}
                          </div>
                        </TableCell>
                        <TableCell class="px-2 py-3">
                          <div class="w-28 text-xs break-all">
                            {{ log.ipAddress }}
                          </div>
                        </TableCell>
                        <TableCell class="px-2 py-3">
                          <div class="w-40 truncate text-xs" :title="log.userAgent">
                            {{ formatUserAgent(log.userAgent) }}
                          </div>
                        </TableCell>
                        <TableCell class="px-2 py-3">
                          <div class="w-32 text-xs whitespace-nowrap">
                            {{ log.createdAt }}
                          </div>
                        </TableCell>
                        <TableCell class="px-2 py-3">
                          <div class="w-32 text-xs whitespace-nowrap">
                            {{ log.updatedAt }}
                          </div>
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination Section -->
        <div class="flex justify-between items-center mt-6">
          <div class="text-sm text-muted-foreground">
            Page {{ userLogs.current_page }} of {{ userLogs.last_page }} ({{ userLogs.total }} total entries)
          </div>
          <div class="flex space-x-2">
            <Button
              variant="outline"
              size="sm"
              :disabled="userLogs.current_page === 1"
              @click="goToPage(userLogs.current_page - 1)"
            >
              <ChevronLeft class="h-4 w-4 mr-1" />
              Previous
            </Button>
            <Button
              variant="outline"
              size="sm"
              :disabled="userLogs.current_page === userLogs.last_page"
              @click="goToPage(userLogs.current_page + 1)"
            >
              Next
              <ChevronRight class="h-4 w-4 ml-1" />
            </Button>
          </div>
        </div>

        <!-- No data fallback -->
        <div v-if="userLogs.data.length === 0" class="text-center py-12">
          <p class="text-muted-foreground">Tidak ada log aktivitas pengguna</p>
        </div>
      </div>
    </div>
  </div>
</template>