<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
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

const props = defineProps<{
  userLogs: UserLogsData
  filters: {
    search?: string
    per_page: number
  }
}>()

const expandedRow = ref<string | null>(null)
const searchQuery = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)

const toggleRow = (id: string) => {
  expandedRow.value = expandedRow.value === id ? null : id
}

const search = () => {
  router.get(route('admin.userlog'), {
    search: searchQuery.value,
    per_page: perPage.value,
    page: 1
  }, {
    preserveState: true,
    replace: true
  })
}

const goToPage = (page: number) => {
  router.get(route('admin.userlog'), {
    search: searchQuery.value,
    per_page: perPage.value,
    page: page
  }, {
    preserveState: true,
    replace: true
  })
}

const formatUserAgent = (userAgent: string) => {
  if (!userAgent) return 'Unknown'
  if (userAgent.length > 50) {
    return userAgent.substring(0, 50) + '...'
  }
  return userAgent
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
      <div class="max-w-4xl mx-auto">

        <!-- Header Section -->
        <div class="mb-6">
          <h1 class="text-primary text-2xl lg:text-4xl font-bold mb-2 font-serif">User Log</h1>
          <p class="text-muted-foreground text-sm lg:text-base">Daftar aktivitas pengguna dalam sistem</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
              v-model="searchQuery"
              placeholder="Search by user, action, description, or IP address..."
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
        
        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
              <div class="px-7 py-4 border-b">
                <h2 class="text-lg font-serif">Recent User Activities</h2>
                <p class="text-sm text-gray-600 mt-1">
                  Showing {{ userLogs.data.length }} of {{ userLogs.total }} entries
                </p>
              </div>

              <Table class="min-w-full table-fixed text-sm text-left text-gray-700">
                <TableHeader>
                  <TableRow class="bg-gray-100 dark:bg-gray-900 text-gray-600 text-sm">
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
                    <!-- Main Row -->
                    <TableRow
                      @click="toggleRow(log.id)"
                      class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <TableCell class="font-medium">
                        <div>
                          <div class="font-semibold">{{ log.user }}</div>
                          <div class="text-xs text-gray-500">{{ log.user_name }}</div>
                        </div>
                      </TableCell>
                      <TableCell>
                        <span class="px-2 py-1 text-xs rounded-full"
                              :class="{
                                'bg-green-100 text-green-800': log.action === 'Login',
                                'bg-blue-100 text-blue-800': log.action === 'Update',
                                'bg-yellow-100 text-yellow-800': log.action === 'Create',
                                'bg-red-100 text-red-800': log.action === 'Delete',
                                'bg-gray-100 text-gray-800': !['Login', 'Update', 'Create', 'Delete'].includes(log.action)
                              }">
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

                    <!-- Expanded Detail Row -->
                    <TableRow v-if="expandedRow === log.id" class="bg-gray-50 dark:bg-gray-900">
                      <td colspan="7" class="p-4 text-sm text-muted-foreground">
                        <div class="space-y-3">
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                              <p><strong>User ID:</strong> {{ log.user }}</p>
                              <p><strong>User Name:</strong> {{ log.user_name }}</p>
                              <p><strong>Action:</strong> {{ log.action }}</p>
                              <p><strong>Description:</strong> {{ log.description }}</p>
                              <p><strong>IP Address:</strong> {{ log.ipAddress }}</p>
                            </div>
                            <div>
                              <p><strong>User Agent:</strong></p>
                              <p class="text-xs bg-gray-100 p-2 rounded mt-1 break-all">{{ log.userAgent }}</p>
                              <p><strong>Created At:</strong> {{ log.createdAt }}</p>
                              <p><strong>Updated At:</strong> {{ log.updatedAt }}</p>
                            </div>
                          </div>

                          <div v-if="log.oldValues || log.newValues" class="mt-4 space-y-2">
                            <div v-if="log.oldValues" class="bg-red-50 p-3 rounded">
                              <strong class="text-red-800">Old Values:</strong>
                              <pre class="text-xs mt-1 text-red-700">{{ JSON.stringify(log.oldValues, null, 2) }}</pre>
                            </div>
                            <div v-if="log.newValues" class="bg-green-50 p-3 rounded">
                              <strong class="text-green-800">New Values:</strong>
                              <pre class="text-xs mt-1 text-green-700">{{ JSON.stringify(log.newValues, null, 2) }}</pre>
                            </div>
                          </div>
                        </div>
                      </td>
                    </TableRow>
                  </template>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
          <div class="text-sm text-gray-600">
            Page {{ userLogs.current_page }} of {{ userLogs.last_page }}
            ({{ userLogs.total }} total entries)
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

        <!-- Empty State -->
        <div v-if="userLogs.data.length === 0" class="text-center py-12">
          <p class="text-muted-foreground">Tidak ada log aktivitas pengguna</p>
        </div>
      </div>
    </main>
  </div>
</template>