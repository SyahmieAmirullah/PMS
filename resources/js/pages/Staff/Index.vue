<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, watchEffect } from 'vue';
import { toast } from 'vue-sonner';
//import { useI18n } from 'vue-i18n';

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import DropdownMenuItem from '@/components/ui/dropdown-menu/DropdownMenuItem.vue';

import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

import { Plus, Search, ChevronLeft, ChevronRight, Eye, Mail, Phone, Users, Briefcase } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

//const { t } = useI18n();
const page = usePage();

watchEffect(() => {
  const s = (page.props.flash as any)?.success;
  const e = (page.props.flash as any)?.error;
  if (s) toast.success(String(s), { duration: 5000 });
  if (e) toast.error(String(e), { duration: 5000 });
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Staff', href: '/staff' }];

const props = defineProps({
  staff: { type: Object },
  roleTypes: { type: Object },
  stats: { type: Object },
});

// Filter form
const formCarian = ref({
  StaffNAME: '',
  StaffEMAIL: '',
  RoleTYPE: '',
});

const hasSearched = ref(false);

// Delete dialog
const showDeleteDialog = ref(false);
const selectedId = ref<number | null>(null);

const openDeleteDialog = (id: number) => {
  selectedId.value = id;
  showDeleteDialog.value = true;
};

const confirmDelete = () => {
  if (!selectedId.value) return;

  router.delete(`/staff/${selectedId.value}`, {
    onSuccess: () => {
      showDeleteDialog.value = false;
      toast.success('Staff member deleted successfully!');
    },
    onError: () => {
      toast.error('Failed to delete staff member');
      showDeleteDialog.value = false;
    },
  });
};

// Search and reset functions
const cariStaff = () => {
  hasSearched.value = true;
  router.get('/staff', {
    StaffNAME: formCarian.value.StaffNAME.trim() || undefined,
    StaffEMAIL: formCarian.value.StaffEMAIL.trim() || undefined,
    RoleTYPE: formCarian.value.RoleTYPE || undefined,
    per_page: itemsPerPage.value,
  }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
};

const resetForm = () => {
  formCarian.value = { StaffNAME: '', StaffEMAIL: '', RoleTYPE: '' };
  itemsPerPage.value = 10;
  hasSearched.value = false;
  router.get('/staff', { per_page: itemsPerPage.value }, { 
    preserveState: true, 
    replace: true, 
    preserveScroll: true 
  });
  toast.success('Search has been reset');
};

const removeFilter = (key: string) => {
  if (key === 'StaffNAME') formCarian.value.StaffNAME = '';
  else if (key === 'StaffEMAIL') formCarian.value.StaffEMAIL = '';
  else if (key === 'RoleTYPE') formCarian.value.RoleTYPE = '';
  
  currentPage.value = 1;
  cariStaff();
  
  const anyFilterActive = Boolean(
    formCarian.value.StaffNAME ||
    formCarian.value.StaffEMAIL ||
    formCarian.value.RoleTYPE
  );
  
  if (!anyFilterActive) hasSearched.value = false;
};

const activeFiltersCount = computed(() => {
  let count = 0;
  if (formCarian.value.StaffNAME) count++;
  if (formCarian.value.StaffEMAIL) count++;
  if (formCarian.value.RoleTYPE) count++;
  return count;
});

// Get initials for avatar
const getInitials = (name: string) => {
  if (!name) return '?';
  const names = name.split(' ');
  if (names.length >= 2) {
    return `${names[0][0]}${names[1][0]}`.toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

// Pagination
const paginatedStaff = computed(() => props.staff.data ?? []);
const totalRows = computed(() => props.staff.total ?? 0);
const currentPage = ref(props.staff.current_page ?? 1);
const itemsPerPage = ref(props.staff.per_page ?? 10);
const totalPages = computed(() => props.staff.last_page ?? 1);

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    router.get('/staff', {
      StaffNAME: formCarian.value.StaffNAME.trim() || undefined,
      StaffEMAIL: formCarian.value.StaffEMAIL.trim() || undefined,
      RoleTYPE: formCarian.value.RoleTYPE || undefined,
      per_page: itemsPerPage.value,
      page: currentPage.value,
    }, {
      preserveState: true,
      replace: true,
      preserveScroll: true,
    });
  }
};

watch(itemsPerPage, (newVal) => {
  router.get('/staff', {
    StaffNAME: formCarian.value.StaffNAME.trim() || undefined,
    StaffEMAIL: formCarian.value.StaffEMAIL.trim() || undefined,
    RoleTYPE: formCarian.value.RoleTYPE || undefined,
    per_page: newVal,
    page: 1,
  }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
});

const previousPage = () => {
  if (currentPage.value > 1) changePage(currentPage.value - 1);
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) changePage(currentPage.value + 1);
};

const pageList = computed<(number | 'ellipsis')[]>(() => {
  const total = Math.max(1, Number(totalPages.value || 1));
  const current = Math.max(1, Number(currentPage.value || 1));
  const delta = 1;

  const left = Math.max(1, current - delta);
  const right = Math.min(total, current + delta);

  const pages: (number | 'ellipsis')[] = [];

  if (left > 1) {
    pages.push(1);
    if (left > 2) pages.push('ellipsis');
  }

  for (let p = left; p <= right; p++) pages.push(p);

  if (right < total) {
    if (right < total - 1) pages.push('ellipsis');
    pages.push(total);
  }

  return pages;
});

const startRow = computed(() => {
  if (totalRows.value === 0) return 0;
  return (currentPage.value - 1) * itemsPerPage.value + 1;
});

const endRow = computed(() => {
  if (totalRows.value === 0) return 0;
  return Math.min(currentPage.value * itemsPerPage.value, totalRows.value);
});
const addStaff=() => {
  router.get('/staff/create');
};
</script>

<template>
  <Head title="Staff" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Staff Members</BaseTitle>
          <Button class="flex items-center gap-2" @click="addStaff">
            <Plus class="h-4 w-4" />Add Staff
          </Button>
      </div>

      <!-- Statistics -->
      <div class="mb-6 grid grid-cols-4 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total Staff</p>
          <h3 class="mt-2 text-2xl font-bold">{{ stats?.total ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">With Projects</p>
          <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ stats?.withProjects ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total Roles</p>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.roles ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Active Projects</p>
          <h3 class="mt-2 text-2xl font-bold text-purple-600">{{ stats?.activeProjects ?? 0 }}</h3>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex flex-col space-y-1">
            <Label>Staff Name</Label>
            <Input
              v-model="formCarian.StaffNAME"
              class="w-[220px]"
              placeholder="Search staff name"
            />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Email</Label>
            <Input
              v-model="formCarian.StaffEMAIL"
              class="w-[220px]"
              placeholder="Search email"
            />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Role Type</Label>
            <Select v-model="formCarian.RoleTYPE">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Select Role" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Roles</SelectLabel>
                  <SelectItem
                    v-for="role in roleTypes"
                    :key="role"
                    :value="role"
                  >
                    {{ role }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Buttons -->
        <div class="mt-6 flex items-center justify-between">
          <div class="flex gap-3">
            <Button class="px-6" @click="cariStaff">
              <Search class="h-4 w-4 mr-2" /> Search
            </Button>
            <Button class="px-6" variant="outline" @click="resetForm">
              Reset
            </Button>
          </div>
        </div>

        <!-- Active Filters Display -->
        <div v-if="hasSearched && activeFiltersCount > 0" class="mt-4 flex flex-wrap items-center gap-2">
          <span class="text-sm font-medium text-gray-700">Active Filters:</span>
          
          <Button 
            v-if="formCarian.StaffNAME" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('StaffNAME')"
          >
            Name: {{ formCarian.StaffNAME }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.StaffEMAIL" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('StaffEMAIL')"
          >
            Email: {{ formCarian.StaffEMAIL }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.RoleTYPE" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('RoleTYPE')"
          >
            Role: {{ formCarian.RoleTYPE }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            variant="ghost" 
            size="sm" 
            class="h-7 text-xs text-red-600 hover:text-red-700 hover:bg-red-50"
            @click="resetForm"
          >
            Clear All
          </Button>
        </div>
      </div>

      <!-- Table -->
      <div class="my-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <Table class="border-y">
          <TableCaption>List of Staff Members</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>No.</TableHead>
              <TableHead>Staff</TableHead>
              <TableHead>Contact</TableHead>
              <TableHead>Roles</TableHead>
              <TableHead>Projects</TableHead>
              <TableHead>Attendance</TableHead>
              <TableHead v-if="page.props.can">Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="(staff, index) in paginatedStaff"
              :key="staff.id"
            >
              <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                    {{ getInitials(staff.StaffNAME) }}
                  </div>
                  <div>
                    <p class="font-medium">{{ staff.StaffNAME }}</p>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="space-y-1">
                  <div v-if="staff.StaffEMAIL" class="flex items-center gap-2 text-sm">
                    <Mail class="h-3 w-3 text-muted-foreground" />
                    <span>{{ staff.StaffEMAIL }}</span>
                  </div>
                  <div v-if="staff.StaffPHONE" class="flex items-center gap-2 text-sm">
                    <Phone class="h-3 w-3 text-muted-foreground" />
                    <span>{{ staff.StaffPHONE }}</span>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex flex-wrap gap-1">
                  <Badge
                    v-for="role in staff.roles"
                    :key="role.id"
                    variant="outline"
                    class="text-xs"
                  >
                    {{ role.RoleTYPE }}
                  </Badge>
                  <span v-if="!staff.roles || staff.roles.length === 0" class="text-sm text-muted-foreground">
                    No roles
                  </span>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-1">
                  <Briefcase class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm font-medium">{{ staff.projects_count ?? 0 }}</span>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-1">
                  <Users class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm font-medium">{{ staff.attendances_count ?? 0 }}</span>
                </div>
              </TableCell>
              <TableCell v-if="page.props.can" class="align-middle">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="outline">...</Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent class="w-56">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <Link :href="`/staff/${staff.id}`" class="no-underline">
                      <DropdownMenuItem>
                        <Eye class="mr-2 h-4 w-4" />
                        View Details
                      </DropdownMenuItem>
                    </Link>
                    <Link :href="`/staff/${staff.id}/edit`" class="no-underline">
                      <DropdownMenuItem>
                        Edit
                      </DropdownMenuItem>
                    </Link>
                    <DropdownMenuItem
                      class="text-red-600"
                      @click="openDeleteDialog(staff.id)"
                    >
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>

            <TableRow v-if="paginatedStaff.length === 0">
              <TableCell colspan="7" class="py-6 text-center text-gray-500">
                No staff members found
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination Controls -->
        <div class="mt-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Rows per page:</span>
            <Select v-model="itemsPerPage">
              <SelectTrigger class="w-[70px]">
                <SelectValue :placeholder="itemsPerPage.toString()" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="5">5</SelectItem>
                <SelectItem :value="10">10</SelectItem>
                <SelectItem :value="25">25</SelectItem>
                <SelectItem :value="50">50</SelectItem>
              </SelectContent>
            </Select>
            <span class="text-sm text-gray-600">
              Showing {{ startRow }} - {{ endRow }} of {{ totalRows }} records
            </span>
          </div>

          <div class="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              :disabled="currentPage === 1"
              @click="previousPage"
            >
              <ChevronLeft class="h-4 w-4" />
              <span>Previous</span>
            </Button>

            <template v-for="(p, idx) in pageList" :key="String(p) + '-' + idx">
              <Button
                v-if="p !== 'ellipsis'"
                variant="outline"
                size="sm"
                :class="p === currentPage ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                @click="changePage(p as number)"
              >
                {{ p }}
              </Button>
              <span v-else class="px-2 text-sm text-gray-500">…</span>
            </template>

            <Button
              variant="outline"
              size="sm"
              :disabled="currentPage === totalPages || totalPages === 0"
              @click="nextPage"
            >
              <span>Next</span>
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Delete Dialog -->
      <AlertDialog v-model:open="showDeleteDialog">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Staff Member</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete this staff member? This action cannot be undone and will remove all associated data.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showDeleteDialog = false">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmDelete()">Yes, Delete</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </BaseCard>
  </AppLayout>
</template>