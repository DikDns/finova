<script setup lang="ts">
// Import Vue features
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

// Import components
import AdminSideBarLayout from '@/components/admin/AdminSideBarLayout.vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Search, ChevronLeft, ChevronRight, Users, Receipt } from 'lucide-vue-next'

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

// State variables
const searchQuery = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)

// Perform a search and update the view
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

// Go to a specific page
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

// Format long user agent strings
const formatUserAgent = (userAgent: string) => {
  if (!userAgent) return 'Unknown'
  return userAgent.length > 50 ? userAgent.substring(0, 50) + '...' : userAgent
}

// Debounce the search input
let searchTimeout: ReturnType<typeof setTimeout>
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    search()
  }, 500)
})

// Track which log row is expanded
const selectedLogId = ref<string | null>(null)

// Toggle the expanded detail row
const toggleRowDetail = (id: string) => {
  selectedLogId.value = selectedLogId.value === id ? null : id
}

// Format old/new values as readable JSON
const formatJsonValues = (value: any) => {
  try {
    return JSON.stringify(value, null, 2)
  } catch {
    return '-'
  }
}
</script>

<template>
  <div class="flex flex-col lg:flex-row min-h-screen">
    <Head title="Finova - Admin - Dashboard" />
    <AdminSideBarLayout class="hidden lg:block" />

    <div class="flex-1 pt-8 px-2 sm:px-4 lg:px-13 pb-10">
      <div class="max-w-4xl mx-auto w-full">
        <!-- Header Section -->
        <div class="mb-4 sm:mb-6"></div>
        <h1 class="text-xl sm:text-2xl lg:text-4xl font-bold mb-1 sm:mb-2 font-serif">Dashboard</h1>
        <p class="text-muted-foreground text-xs sm:text-sm lg:text-base">
          Daftar aktivitas pengguna dalam sistem
        </p>
        <br />

        <!-- Stats Cards Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-6 mb-6 sm:mb-8">
          <Card class="p-2 sm:p-6">
            <div class="flex flex-row items-center justify-between text-left space-x-2 sm:space-x-3">
              <div>
                <p class="text-xs font-medium text-muted-foreground">Total Users</p>
                <p class="text-lg sm:text-3xl font-bold text-gray-900">{{ totalUsers }}</p>
                <p class="text-xs mt-1" :class="usersGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ usersGrowth >= 0 ? '+' : '' }}{{ usersGrowth }}% from last month
                </p>
              </div>
              <div class="p-2 sm:p-3 bg-blue-100 rounded-full flex-shrink-0">
                <Users class="h-5 w-5 sm:h-8 sm:w-8 text-blue-600" />
              </div>
            </div>
          </Card>

          <Card class="p-2 sm:p-6">
            <div class="flex flex-row items-center justify-between text-left space-x-2 sm:space-x-3">
              <div>
                <p class="text-xs font-medium text-muted-foreground">Total Subscriptions</p>
                <p class="text-lg sm:text-3xl font-bold text-gray-900">{{ totalSubscriptions }}</p>
                <p class="text-xs mt-1" :class="subscriptionsGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ subscriptionsGrowth >= 0 ? '+' : '' }}{{ subscriptionsGrowth }}% from last month
                </p>
              </div>
              <div class="p-2 sm:p-3 bg-green-100 rounded-full flex-shrink-0">
                <Receipt class="h-5 w-5 sm:h-8 sm:w-8 text-green-600" />
              </div>
            </div>
          </Card>
        </div>

        <!-- Filter Section -->
        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
            <Input
              v-model="searchQuery"
              placeholder="Search by user, action, description, or IP address..."
              class="pl-10 w-full text-xs sm:text-sm"
            />
          </div>
          <select
            v-model="perPage"
            @change="search"
            class="w-full sm:w-auto text-xs sm:text-sm px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-50"
          >
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
          </select>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
          <div class="bg-white rounded-2xl shadow-md overflow-x-auto">
            <div class="px-3 py-3 sm:px-7 sm:py-4 border-b">
              <h2 class="text-base sm:text-lg font-serif">User Log</h2>
              <p class="text-xs sm:text-sm text-muted-foreground mt-1">
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
                  <template v-for="log in userLogs.data" :key="log.id">
                    <TableRow
                      class="hover:bg-gray-100 cursor-pointer"
                      @click="toggleRowDetail(log.id)"
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

                    <!-- Detail Row (Expanded) -->
                    <TableRow v-if="selectedLogId === log.id" class="bg-gray-50 text-sm">
                      <TableCell colspan="7" class="p-4">
                        <div class="grid md:grid-cols-2 gap-4">
                          <div class="space-y-1">
                            <div><strong>User ID:</strong> {{ log.user }}</div>
                            <div><strong>User Name:</strong> {{ log.user_name }}</div>
                            <div><strong>Action:</strong> {{ log.action }}</div>
                            <div><strong>Description:</strong> {{ log.description }}</div>
                            <div><strong>IP Address:</strong> {{ log.ipAddress }}</div>
                          </div>
                          <div class="space-y-1">
                            <div><strong>User Agent:</strong></div>
                            <div class="bg-gray-100 px-2 py-1 rounded text-gray-700">
                              {{ log.userAgent }}
                            </div>
                            <div><strong>Created At:</strong> {{ log.createdAt }}</div>
                            <div><strong>Updated At:</strong> {{ log.updatedAt }}</div>
                          </div>
                        </div>

                        <div class="mt-4">
                          <div class="font-semibold text-red-700 mb-1">Old Values:</div>
                          <pre class="bg-red-50 text-red-900 text-xs p-2 rounded overflow-x-auto whitespace-pre-wrap">
{{ formatJsonValues(log.oldValues) }}
                          </pre>
                        </div>

                        <div class="mt-4">
                          <div class="font-semibold text-green-700 mb-1">New Values:</div>
                          <pre class="bg-green-50 text-green-900 text-xs p-2 rounded overflow-x-auto whitespace-pre-wrap">
{{ formatJsonValues(log.newValues) }}
                          </pre>
                        </div>
                      </TableCell>
                    </TableRow>
                  </template>
                </TableBody>
              </Table>
            </div>

            <!-- Mobile Version: Card layout -->
            <div class="block lg:hidden">
              <div class="divide-y">
                <div
                  v-for="log in userLogs.data"
                  :key="log.id"
                  class="py-3 px-2 flex flex-col gap-1"
                >
                  <div class="flex items-center justify-between">
                    <div class="font-semibold text-xs truncate">{{ log.user }}</div>
                    <span
                      class="px-2 py-1 text-xs rounded-full ml-2"
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
                  </div>
                  <div class="text-xs text-muted-foreground truncate">{{ log.user_name }}</div>
                  <div class="text-xs mt-1 truncate" :title="log.description">
                    <span class="font-medium">Desc:</span> {{ log.description }}
                  </div>
                  <div class="flex flex-wrap gap-x-2 text-xs mt-1">
                    <span class="truncate"><span class="font-medium">IP:</span> {{ log.ipAddress }}</span>
                    <span class="truncate"><span class="font-medium">UA:</span> {{ formatUserAgent(log.userAgent) }}</span>
                  </div>
                  <div class="flex flex-wrap gap-x-2 text-xs mt-1">
                    <span><span class="font-medium">Created:</span> {{ log.createdAt }}</span>
                    <span><span class="font-medium">Updated:</span> {{ log.updatedAt }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center mt-4 sm:mt-6 gap-2">
          <div class="text-xs sm:text-sm text-muted-foreground">
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
              <span class="hidden xs:inline">Previous</span>
            </Button>
            <Button
              variant="outline"
              size="sm"
              :disabled="userLogs.current_page === userLogs.last_page"
              @click="goToPage(userLogs.current_page + 1)"
            >
              <span class="hidden xs:inline">Next</span>
              <ChevronRight class="h-4 w-4 ml-1" />
            </Button>
          </div>
        </div>

        <!-- No data fallback -->
        <div v-if="userLogs.data.length === 0" class="text-center py-8 sm:py-12">
          <p class="text-muted-foreground text-xs sm:text-base">Tidak ada log aktivitas pengguna</p>
        </div>
      </div>
    </div>
  </div>
</template>