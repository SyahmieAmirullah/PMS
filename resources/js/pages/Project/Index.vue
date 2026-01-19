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

import Textarea from '@/components/ui/textarea/Textarea.vue';
import { Plus, Search, ChevronLeft, ChevronRight, Eye, Users, Folder } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

//const { t } = useI18n();
const page = usePage();

watchEffect(() => {
  const s = (page.props.flash as any)?.success;
  const e = (page.props.flash as any)?.error;
  if (s) toast.success(String(s), { duration: 5000 });
  if (e) toast.error(String(e), { duration: 5000 });
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Projects', href: '/projects' }];

const props = defineProps({
  projects: { type: Object },
  staffList: { type: Object },
  stats: { type: Object },
});

// Filter form
const formCarian = ref({
  ProjectNAME: '',
  ProjectSTATUS: '',
  ClientNAME: '',
  StaffID: '',
});

const hasSearched = ref(false);
const initialPerPage = Number(new URLSearchParams(window.location.search).get('per_page') ?? 10);

// Delete dialog
const showDeleteDialog = ref(false);
const selectedId = ref<number | null>(null);

const openDeleteDialog = (id: number) => {
  selectedId.value = id;
  showDeleteDialog.value = true;
};

const confirmDelete = () => {
  if (!selectedId.value) return;

  router.delete(`/projects/${selectedId.value}`, {
    onSuccess: () => {
      showDeleteDialog.value = false;
      toast.success('Project deleted successfully!');
    },
    onError: () => {
      toast.error('Failed to delete project');
      showDeleteDialog.value = false;
    },
  });
};

// Edit dialog
const showEditDialog = ref(false);
const editProject = ref({
  id: null as string | number | null,
  ProjectNAME: '',
  ProjectDESC: '',
  ProjectSTATUS: '',
  ClientNAME: '',
  ClientPHONE: '',
  ClientEMAIL: '',
  staff_ids: [] as any[],
});

const openEditDialog = (project: any) => {
  editProject.value = {
    id: project.id,
    ProjectNAME: project.ProjectNAME,
    ProjectDESC: project.ProjectDESC ?? '',
    ProjectSTATUS: project.ProjectSTATUS,
    ClientNAME: project.ClientNAME,
    ClientPHONE: project.ClientPHONE ?? '',
    ClientEMAIL: project.ClientEMAIL ?? '',
    staff_ids: project.staff?.map((s: any) => s.id) ?? [],
  };
  showEditDialog.value = true;
};

const updateProject = () => {
  if (!editProject.value.id) return;
  router.put(
    `/projects/${editProject.value.id}`,
    {
      ProjectNAME: editProject.value.ProjectNAME,
      ProjectDESC: editProject.value.ProjectDESC,
      ProjectSTATUS: editProject.value.ProjectSTATUS,
      ClientNAME: editProject.value.ClientNAME,
      ClientPHONE: editProject.value.ClientPHONE,
      ClientEMAIL: editProject.value.ClientEMAIL,
      staff_ids: editProject.value.staff_ids,
    },
    {
      onSuccess: () => {
        toast.success('Project updated successfully!');
        showEditDialog.value = false;
      },
      onError: (err) => {
        console.log(err);
        toast.error('Please check your inputs');
      },
    }
  );
};

// Search and reset functions
const cariProject = () => {
  hasSearched.value = true;
  router.get('/projects', {
    ProjectNAME: formCarian.value.ProjectNAME.trim() || undefined,
    ProjectSTATUS: formCarian.value.ProjectSTATUS || undefined,
    ClientNAME: formCarian.value.ClientNAME.trim() || undefined,
    StaffID: formCarian.value.StaffID || undefined,
    per_page: itemsPerPage.value,
  }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
};

const resetForm = () => {
  formCarian.value = { ProjectNAME: '', ProjectSTATUS: '', ClientNAME: '', StaffID: '' };
  itemsPerPage.value = 10;
  hasSearched.value = false;
  router.get('/projects', { per_page: itemsPerPage.value }, { 
    preserveState: true, 
    replace: true, 
    preserveScroll: true 
  });
  toast.success('Search has been reset');
};

const removeFilter = (key: string) => {
  if (key === 'ProjectNAME') formCarian.value.ProjectNAME = '';
  else if (key === 'ProjectSTATUS') formCarian.value.ProjectSTATUS = '';
  else if (key === 'ClientNAME') formCarian.value.ClientNAME = '';
  else if (key === 'StaffID') formCarian.value.StaffID = '';
  
  currentPage.value = 1;
  cariProject();
  
  const anyFilterActive = Boolean(
    formCarian.value.ProjectNAME ||
    formCarian.value.ProjectSTATUS ||
    formCarian.value.ClientNAME ||
    formCarian.value.StaffID
  );
  
  if (!anyFilterActive) hasSearched.value = false;
};

const activeFiltersCount = computed(() => {
  let count = 0;
  if (formCarian.value.ProjectNAME) count++;
  if (formCarian.value.ProjectSTATUS) count++;
  if (formCarian.value.ClientNAME) count++;
  if (formCarian.value.StaffID) count++;
  return count;
});

// Status badge color
const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    'Active': 'bg-green-100 text-green-700 border-green-200',
    'Completed': 'bg-blue-100 text-blue-700 border-blue-200',
    'On Hold': 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'Cancelled': 'bg-red-100 text-red-700 border-red-200',
  };
  return colors[status] || 'bg-gray-100 text-gray-700 border-gray-200';
};

// Pagination
const paginatedProjects = computed(() => props.projects.data ?? []);
const totalRows = computed(() => props.projects.total ?? 0);
const currentPage = ref(props.projects.current_page ?? 1);
const itemsPerPage = ref(props.projects.per_page ?? 10);
const totalPages = computed(() => props.projects.last_page ?? 1);

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    router.get('/projects', {
      ProjectNAME: formCarian.value.ProjectNAME.trim() || undefined,
      ProjectSTATUS: formCarian.value.ProjectSTATUS || undefined,
      ClientNAME: formCarian.value.ClientNAME.trim() || undefined,
      StaffID: formCarian.value.StaffID || undefined,
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
  router.get('/projects', {
    ProjectNAME: formCarian.value.ProjectNAME.trim() || undefined,
    ProjectSTATUS: formCarian.value.ProjectSTATUS || undefined,
    ClientNAME: formCarian.value.ClientNAME.trim() || undefined,
    StaffID: formCarian.value.StaffID || undefined,
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
const addProject = () => {
  router.get('/projects/create');
};
const projectStatuses = ['Active', 'Completed', 'On Hold', 'Cancelled'];
</script>

<template>
  <Head title="Projects" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Projects</BaseTitle>
        <Button @click="addProject"><Plus class="h-4 w-4" />Add Project</Button>
      </div>

      <!-- Statistics -->
      <div class="mb-6 grid grid-cols-4 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total Projects</p>
          <h3 class="mt-2 text-2xl font-bold">{{ stats?.total ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Active</p>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.active ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Completed</p>
          <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ stats?.completed ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">On Hold</p>
          <h3 class="mt-2 text-2xl font-bold text-yellow-600">{{ stats?.onHold ?? 0 }}</h3>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex flex-col space-y-1">
            <Label>Project Name</Label>
            <Input
              v-model="formCarian.ProjectNAME"
              class="w-[220px]"
              placeholder="Search project name"
            />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Status</Label>
            <Select v-model="formCarian.ProjectSTATUS">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Select Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Status</SelectLabel>
                  <SelectItem
                    v-for="status in projectStatuses"
                    :key="status"
                    :value="status"
                  >
                    {{ status }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Client Name</Label>
            <Input
              v-model="formCarian.ClientNAME"
              class="w-[220px]"
              placeholder="Search client name"
            />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Staff Member</Label>
            <Select v-model="formCarian.StaffID">
              <SelectTrigger class="w-[200px]">
                <SelectValue placeholder="Select Staff" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Staff</SelectLabel>
                  <SelectItem
                    v-for="staff in staffList"
                    :key="staff.id"
                    :value="staff.id"
                  >
                    {{ staff.StaffNAME }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Buttons -->
        <div class="mt-6 flex items-center justify-between">
          <div class="flex gap-3">
            <Button class="px-6" @click="cariProject">
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
            v-if="formCarian.ProjectNAME" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('ProjectNAME')"
          >
            Project: {{ formCarian.ProjectNAME }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.ProjectSTATUS" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('ProjectSTATUS')"
          >
            Status: {{ formCarian.ProjectSTATUS }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.ClientNAME" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('ClientNAME')"
          >
            Client: {{ formCarian.ClientNAME }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.StaffID" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('StaffID')"
          >
            Staff: {{ staffList?.find((s: any) => s.id === formCarian.StaffID)?.StaffNAME }}
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
          <TableCaption>List of Projects</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>No.</TableHead>
              <TableHead>Project Name</TableHead>
              <TableHead>Client</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Staff</TableHead>
              <TableHead>Tasks</TableHead>
              <TableHead>Phases</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="(project, index) in paginatedProjects"
              :key="project.id"
            >
              <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
              <TableCell class="font-medium">{{ project.ProjectNAME }}</TableCell>
              <TableCell>
                <div>
                  <div class="font-medium">{{ project.ClientNAME }}</div>
                  <div class="text-xs text-muted-foreground">{{ project.ClientEMAIL }}</div>
                </div>
              </TableCell>
              <TableCell>
                <Badge :class="getStatusColor(project.ProjectSTATUS)">
                  {{ project.ProjectSTATUS }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-1">
                  <Users class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ project.staff?.length ?? 0 }}</span>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-1">
                  <Folder class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ project.tasks_count ?? 0 }}</span>
                </div>
              </TableCell>
              <TableCell>
                <span class="text-sm">{{ project.phases_count ?? 0 }}</span>
              </TableCell>
              <TableCell class="align-middle">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="outline">...</Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent class="w-56">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                      <DropdownMenuItem @click="router.get(`/projects/${project.ProjectID}`)">
                        <Eye class="mr-2 h-4 w-4" />
                        View Details
                      </DropdownMenuItem>
                    <DropdownMenuItem @click="openEditDialog(project)">
                      Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      class="text-red-600"
                      @click="openDeleteDialog(project.id)"
                    >
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>

            <TableRow v-if="paginatedProjects.length === 0">
              <TableCell colspan="8" class="py-6 text-center text-gray-500">
                No projects found
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination Controls -->
        <div class="mt-4 flex items-center justify-between">
          <!-- Left: Rows per page selector -->
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

          <!-- Right: Page navigation -->
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
            <AlertDialogTitle>Delete Project</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete this project? This action cannot be undone and will remove all associated data including tasks, phases, and feedback.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showDeleteDialog = false">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmDelete()">Yes, Delete</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <!-- Edit Dialog -->
      <Dialog v-model:open="showEditDialog">
        <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto dialog-content">
          <DialogHeader>
            <DialogTitle>Edit Project</DialogTitle>
            <DialogDescription>Update project information</DialogDescription>
          </DialogHeader>

          <div class="py-6 space-y-6">
            <div class="flex flex-col space-y-1">
              <Label>Project Name</Label>
              <Input
                v-model="editProject.ProjectNAME"
                placeholder="Enter project name"
              />
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Description</Label>
              <Textarea
                v-model="editProject.ProjectDESC"
                placeholder="Enter project description"
                rows="4"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Status</Label>
                <Select v-model="editProject.ProjectSTATUS">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Project Status</SelectLabel>
                      <SelectItem
                        v-for="status in projectStatuses"
                        :key="status"
                        :value="status"
                      >
                        {{ status }}
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Client Name</Label>
                <Input
                  v-model="editProject.ClientNAME"
                  placeholder="Enter client name"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Client Phone</Label>
                <Input
                  v-model="editProject.ClientPHONE"
                  placeholder="Enter client phone"
                />
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Client Email</Label>
                <Input
                  v-model="editProject.ClientEMAIL"
                  type="email"
                  placeholder="Enter client email"
                />
              </div>
            </div>
          </div>

          <DialogFooter>
            <Button variant="destructive" @click="showEditDialog = false">Cancel</Button>
            <Button @click="updateProject">Save Changes</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </BaseCard>
  </AppLayout>
</template>

<style scoped>
.dialog-content::-webkit-scrollbar {
  width: 8px;
}
.dialog-content::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}
.dialog-content::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>