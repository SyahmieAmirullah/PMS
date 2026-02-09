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
import { Plus, Search, ChevronLeft, ChevronRight, Calendar, AlertTriangle, CheckCircle } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { parseISO, isPast } from 'date-fns';
import { formatDate } from '@/lib/date';

//const { t } = useI18n();
const page = usePage();

watchEffect(() => {
  const s = (page.props.flash as any)?.success;
  const e = (page.props.flash as any)?.error;
  if (s) toast.success(String(s), { duration: 5000 });
  if (e) toast.error(String(e), { duration: 5000 });
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tasks', href: '/tasks' }];

const props = defineProps({
  tasks: { type: Object },
  projects: { type: Object },
  stats: { type: Object },
});

// Filter form
const formCarian = ref({
  TaskNAME: '',
  ProjectID: '',
  date_from: '',
  date_to: '',
  status: '',
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

  router.delete(`/tasks/${selectedId.value}`, {
    onSuccess: () => {
      showDeleteDialog.value = false;
      toast.success('Task deleted successfully!');
    },
    onError: () => {
      toast.error('Failed to delete task');
      showDeleteDialog.value = false;
    },
  });
};

// Edit dialog
const showEditDialog = ref(false);
const editTask = ref({
  id: null as string | number | null,
  TaskNAME: '',
  TaskDESC: '',
  TaskDUE: '',
  ProjectID: '',
  StaffID: '',
  TaskSTATUS: 'pending',
});

const openEditDialog = (task: any) => {
  editTask.value = {
    id: task.id,
    TaskNAME: task.TaskNAME,
    TaskDESC: task.TaskDESC ?? '',
    TaskDUE: task.TaskDUE,
    ProjectID: task.ProjectID,
    StaffID: task.StaffID ?? '',
    TaskSTATUS: task.TaskSTATUS ?? 'pending',
  };
  showEditDialog.value = true;
};

const updateTask = () => {
  if (!editTask.value.id) return;
  router.put(
    `/tasks/${editTask.value.id}`,
    {
      TaskNAME: editTask.value.TaskNAME,
      TaskDESC: editTask.value.TaskDESC,
      TaskDUE: editTask.value.TaskDUE,
      ProjectID: editTask.value.ProjectID,
      StaffID: editTask.value.StaffID,
      TaskSTATUS: editTask.value.TaskSTATUS,
    },
    {
      onSuccess: () => {
        toast.success('Task updated successfully!');
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
const cariTask = () => {
  hasSearched.value = true;
  router.get('/tasks', {
    TaskNAME: formCarian.value.TaskNAME.trim() || undefined,
    ProjectID: formCarian.value.ProjectID || undefined,
    date_from: formCarian.value.date_from || undefined,
    date_to: formCarian.value.date_to || undefined,
    status: formCarian.value.status || undefined,
    per_page: itemsPerPage.value,
  }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  });
};

const resetForm = () => {
  formCarian.value = { 
    TaskNAME: '', 
    ProjectID: '', 
    date_from: '', 
    date_to: '',
    status: '',
  };
  itemsPerPage.value = 10;
  hasSearched.value = false;
  router.get('/tasks', { per_page: itemsPerPage.value }, { 
    preserveState: true, 
    replace: true, 
    preserveScroll: true 
  });
  toast.success('Search has been reset');
};

const removeFilter = (key: string) => {
  if (key === 'TaskNAME') formCarian.value.TaskNAME = '';
  else if (key === 'ProjectID') formCarian.value.ProjectID = '';
  else if (key === 'date_from') formCarian.value.date_from = '';
  else if (key === 'date_to') formCarian.value.date_to = '';
  else if (key === 'status') formCarian.value.status = '';
  
  currentPage.value = 1;
  cariTask();
  
  const anyFilterActive = Boolean(
    formCarian.value.TaskNAME ||
    formCarian.value.ProjectID ||
    formCarian.value.date_from ||
    formCarian.value.date_to ||
    formCarian.value.status
  );
  
  if (!anyFilterActive) hasSearched.value = false;
};

const activeFiltersCount = computed(() => {
  let count = 0;
  if (formCarian.value.TaskNAME) count++;
  if (formCarian.value.ProjectID) count++;
  if (formCarian.value.date_from) count++;
  if (formCarian.value.date_to) count++;
  if (formCarian.value.status) count++;
  return count;
});

// Check if task is overdue
const isOverdue = (dateStr: string) => {
  if (!dateStr) return false;
  try {
    return isPast(parseISO(dateStr));
  } catch {
    return false;
  }
};

// Get task status badge
const getTaskBadge = (task: any) => {
  const status = task.TaskSTATUS;
  if (status === 'completed') {
    return { text: 'Completed', class: 'bg-green-100 text-green-700 border-green-200' };
  }
  if (status === 'cancelled') {
    return { text: 'Cancelled', class: 'bg-red-100 text-red-700 border-red-200' };
  }
  if (status === 'in_progress') {
    return { text: 'In Progress', class: 'bg-blue-100 text-blue-700 border-blue-200' };
  }
  if (isOverdue(task.TaskDUE)) {
    return { text: 'Overdue', class: 'bg-red-100 text-red-700 border-red-200' };
  }
  return { text: 'Pending', class: 'bg-gray-100 text-gray-700 border-gray-200' };
};

const markTaskDone = (taskId: number) => {
  router.put(`/tasks/${taskId}/done`, {}, {
    onSuccess: () => toast.success('Task marked as done.'),
    onError: () => toast.error('Failed to mark task as done.'),
  });
};

// Pagination
const paginatedTasks = computed(() => props.tasks.data ?? []);
const totalRows = computed(() => props.tasks.total ?? 0);
const currentPage = ref(props.tasks.current_page ?? 1);
const itemsPerPage = ref(props.tasks.per_page ?? 10);
const totalPages = computed(() => props.tasks.last_page ?? 1);

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    router.get('/tasks', {
      TaskNAME: formCarian.value.TaskNAME.trim() || undefined,
      ProjectID: formCarian.value.ProjectID || undefined,
      date_from: formCarian.value.date_from || undefined,
      date_to: formCarian.value.date_to || undefined,
      status: formCarian.value.status || undefined,
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
  router.get('/tasks', {
    TaskNAME: formCarian.value.TaskNAME.trim() || undefined,
    ProjectID: formCarian.value.ProjectID || undefined,
    date_from: formCarian.value.date_from || undefined,
    date_to: formCarian.value.date_to || undefined,
    status: formCarian.value.status || undefined,
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

const statusOptions = [
  { value: 'upcoming', label: 'Upcoming' },
  { value: 'overdue', label: 'Overdue' },
];
const taskStatusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
];
const addTask = () => {
  router.get('/tasks/create');
};

const editProject = computed(() => {
  return (props.projects || []).find((p: any) => p.id === editTask.value.ProjectID);
});

const editProjectStaff = computed(() => editProject.value?.staff || []);

watch(
  () => editTask.value.ProjectID,
  () => {
    if (!editTask.value.StaffID) return;
    const exists = (editProjectStaff.value || []).some((s: any) => s.id === editTask.value.StaffID);
    if (!exists) editTask.value.StaffID = '';
  }
);
</script>

<template>
  <Head title="Tasks" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Tasks</BaseTitle>
          <Button class="flex items-center gap-2" @click="addTask">
            <Plus class="h-4 w-4" />Add Task
          </Button>
      </div>

      <!-- Statistics -->
      <div class="mb-6 grid grid-cols-4 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total Tasks</p>
          <h3 class="mt-2 text-2xl font-bold">{{ stats?.total ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Upcoming</p>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.upcoming ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Overdue</p>
          <h3 class="mt-2 text-2xl font-bold text-red-600">{{ stats?.overdue ?? 0 }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">This Week</p>
          <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ stats?.thisWeek ?? 0 }}</h3>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex flex-col space-y-1">
            <Label>Task Name</Label>
            <Input
              v-model="formCarian.TaskNAME"
              class="w-[220px]"
              placeholder="Search task name"
            />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Project</Label>
            <Select v-model="formCarian.ProjectID">
              <SelectTrigger class="w-[200px]">
                <SelectValue placeholder="Select Project" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Projects</SelectLabel>
                  <SelectItem
                    v-for="project in projects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.ProjectNAME }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Status</Label>
            <Select v-model="formCarian.status">
              <SelectTrigger class="w-[150px]">
                <SelectValue placeholder="Select Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Status</SelectLabel>
                  <SelectItem
                    v-for="status in statusOptions"
                    :key="status.value"
                    :value="status.value"
                  >
                    {{ status.label }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Due Date From</Label>
            <Input
              v-model="formCarian.date_from"
              type="date"
              class="w-[180px]"
            />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Due Date To</Label>
            <Input
              v-model="formCarian.date_to"
              type="date"
              class="w-[180px]"
            />
          </div>
        </div>

        <!-- Buttons -->
        <div class="mt-6 flex items-center justify-between">
          <div class="flex gap-3">
            <Button class="px-6" @click="cariTask">
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
            v-if="formCarian.TaskNAME" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('TaskNAME')"
          >
            Task: {{ formCarian.TaskNAME }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.ProjectID" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('ProjectID')"
          >
            Project: {{ projects?.find((p: any) => p.id === formCarian.ProjectID)?.ProjectNAME }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.status" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('status')"
          >
            Status: {{ statusOptions.find(s => s.value === formCarian.status)?.label }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.date_from" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('date_from')"
          >
            From: {{ formatDate(formCarian.date_from) }}
            <span class="ml-1">×</span>
          </Button>

          <Button 
            v-if="formCarian.date_to" 
            variant="outline" 
            size="sm" 
            class="h-7 px-2 text-xs"
            @click="removeFilter('date_to')"
          >
            To: {{ formatDate(formCarian.date_to) }}
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
          <TableCaption>List of Tasks</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>No.</TableHead>
              <TableHead>Task Name</TableHead>
              <TableHead>Project</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Due Date</TableHead>
              <TableHead>Status</TableHead>
              <TableHead v-if="page.props.can">Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="(task, index) in paginatedTasks"
              :key="task.id"
              :class="isOverdue(task.TaskDUE) ? 'bg-red-50' : ''"
            >
              <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
              <TableCell class="font-medium">
                <div class="flex items-center gap-2">
                  <AlertTriangle v-if="isOverdue(task.TaskDUE)" class="h-4 w-4 text-red-600" />
                  <CheckCircle v-else class="h-4 w-4 text-green-600" />
                  {{ task.TaskNAME }}
                </div>
              </TableCell>
              <TableCell>
                <Link 
                  :href="`/projects/${task.project?.id}`" 
                  class="text-primary hover:underline"
                >
                  {{ task.project?.ProjectNAME }}
                </Link>
              </TableCell>
              <TableCell class="max-w-xs truncate">{{ task.TaskDESC || '-' }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <Calendar class="h-4 w-4 text-muted-foreground" />
                  <span :class="isOverdue(task.TaskDUE) ? 'text-red-600 font-medium' : ''">
                    {{ formatDate(task.TaskDUE) }}
                  </span>
                </div>
              </TableCell>
              <TableCell>
                <Badge :class="getTaskBadge(task).class">
                  {{ getTaskBadge(task).text }}
                </Badge>
              </TableCell>
              <TableCell v-if="page.props.can" class="align-middle">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="outline">...</Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent class="w-56">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="openEditDialog(task)">
                      Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      :class="task.TaskSTATUS === 'completed' ? 'text-gray-400 pointer-events-none' : ''"
                      @click="task.TaskSTATUS === 'completed' ? null : markTaskDone(task.id)"
                    >
                      Mark Done
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      class="text-red-600"
                      @click="openDeleteDialog(task.id)"
                    >
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>

            <TableRow v-if="paginatedTasks.length === 0">
              <TableCell colspan="7" class="py-6 text-center text-gray-500">
                No tasks found
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
            <AlertDialogTitle>Delete Task</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete this task? This action cannot be undone.
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
            <DialogTitle>Edit Task</DialogTitle>
            <DialogDescription>Update task information</DialogDescription>
          </DialogHeader>

          <div class="py-6 space-y-6">
            <div class="flex flex-col space-y-1">
              <Label>Task Name</Label>
              <Input
                v-model="editTask.TaskNAME"
                placeholder="Enter task name"
              />
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Description</Label>
              <Textarea
                v-model="editTask.TaskDESC"
                placeholder="Enter task description"
                rows="4"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Project</Label>
                <Select v-model="editTask.ProjectID">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Project" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Projects</SelectLabel>
                      <SelectItem
                        v-for="project in projects"
                        :key="project.id"
                        :value="project.id"
                      >
                        {{ project.ProjectNAME }}
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Due Date</Label>
                <Input
                  v-model="editTask.TaskDUE"
                  type="date"
                />
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Assign Staff</Label>
              <Select v-model="editTask.StaffID">
                <SelectTrigger>
                  <SelectValue placeholder="Select Staff" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>Staff</SelectLabel>
                    <SelectItem
                      v-for="staff in editProjectStaff"
                      :key="staff.id"
                      :value="staff.id"
                    >
                      {{ staff.StaffNAME }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <span v-if="editTask.ProjectID && editProjectStaff.length === 0" class="text-xs text-muted-foreground">
                No staff assigned to this project.
              </span>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Status</Label>
              <Select v-model="editTask.TaskSTATUS">
                <SelectTrigger>
                  <SelectValue placeholder="Select Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>Task Status</SelectLabel>
                    <SelectItem
                      v-for="status in taskStatusOptions"
                      :key="status.value"
                      :value="status.value"
                    >
                      {{ status.label }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>
          </div>

          <DialogFooter>
            <Button variant="destructive" @click="showEditDialog = false">Cancel</Button>
            <Button @click="updateTask">Save Changes</Button>
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
