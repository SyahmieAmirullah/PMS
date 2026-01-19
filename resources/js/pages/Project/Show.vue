<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
//import { useI18n } from 'vue-i18n';

import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs';

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

import { Badge } from '@/components/ui/badge';
import { 
  ArrowLeft, 
  Users, 
  Mail, 
  Phone, 
  Calendar, 
  FileText,
  CheckCircle2,
  Clock,
  AlertCircle,
  MessageSquare,
  FolderOpen
} from 'lucide-vue-next';
import { format } from 'date-fns';

//const { t } = useI18n();

const props = defineProps({
  project: { type: Object, required: true },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: '/projects' },
  { title: props.project.ProjectNAME, href: `/projects/${props.project.id}` }
];

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

// Task status color
const getTaskStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    'Pending': 'bg-gray-100 text-gray-700',
    'In Progress': 'bg-blue-100 text-blue-700',
    'Completed': 'bg-green-100 text-green-700',
    'Cancelled': 'bg-red-100 text-red-700',
  };
  return colors[status] || 'bg-gray-100 text-gray-700';
};

// Format date
const formatDate = (date: string) => {
  if (!date) return '-';
  try {
    return format(new Date(date), 'dd MMM yyyy');
  } catch {
    return date;
  }
};

const formatDateTime = (date: string) => {
  if (!date) return '-';
  try {
    return format(new Date(date), 'dd MMM yyyy, HH:mm');
  } catch {
    return date;
  }
};

// Statistics
const taskStats = computed(() => {
  const tasks = props.project.tasks || [];
  return {
    total: tasks.length,
    completed: tasks.filter((t: any) => t.TaskSTATUS === 'Completed').length,
    inProgress: tasks.filter((t: any) => t.TaskSTATUS === 'In Progress').length,
    pending: tasks.filter((t: any) => t.TaskSTATUS === 'Pending').length,
  };
});
</script>

<template>
  <Head :title="project.ProjectNAME" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link href="/projects" class="no-underline">
            <Button variant="outline" size="sm">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Back
            </Button>
          </Link>
          <div>
            <BaseTitle size="2xl">{{ project.ProjectNAME }}</BaseTitle>
            <Badge :class="getStatusColor(project.ProjectSTATUS)" class="mt-2">
              {{ project.ProjectSTATUS }}
            </Badge>
          </div>
        </div>
        <Link :href="`/projects/${project.ProjectID}/edit`" class="no-underline">
          <Button>Edit Project</Button>
        </Link>
      </div>

      <!-- Project Overview Cards -->
      <div class="mb-6 grid grid-cols-4 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-muted-foreground">Total Tasks</p>
            <FileText class="h-5 w-5 text-muted-foreground" />
          </div>
          <h3 class="mt-2 text-2xl font-bold">{{ taskStats.total }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-muted-foreground">Completed</p>
            <CheckCircle2 class="h-5 w-5 text-green-600" />
          </div>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ taskStats.completed }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-muted-foreground">In Progress</p>
            <Clock class="h-5 w-5 text-blue-600" />
          </div>
          <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ taskStats.inProgress }}</h3>
        </div>

        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-muted-foreground">Pending</p>
            <AlertCircle class="h-5 w-5 text-yellow-600" />
          </div>
          <h3 class="mt-2 text-2xl font-bold text-yellow-600">{{ taskStats.pending }}</h3>
        </div>
      </div>

      <!-- Main Content Tabs -->
      <Tabs default-value="overview" class="w-full">
        <TabsList class="grid w-full grid-cols-5">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="tasks">Tasks ({{ project.tasks?.length ?? 0 }})</TabsTrigger>
          <TabsTrigger value="phases">Phases ({{ project.phases?.length ?? 0 }})</TabsTrigger>
          <TabsTrigger value="feedback">Feedback ({{ project.feedback?.length ?? 0 }})</TabsTrigger>
          <TabsTrigger value="logs">Logs ({{ project.project_logs?.length ?? 0 }})</TabsTrigger>
        </TabsList>

        <!-- Overview Tab -->
        <TabsContent value="overview" class="space-y-6">
          <!-- Project Details -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold">Project Details</h3>
            <div class="space-y-3">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Description</p>
                <p class="mt-1">{{ project.ProjectDESC || 'No description provided' }}</p>
              </div>
            </div>
          </div>

          <!-- Client Information -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold flex items-center gap-2">
              <Users class="h-5 w-5" />
              Client Information
            </h3>
            <div class="space-y-3">
              <div class="flex items-center gap-2">
                <Users class="h-4 w-4 text-muted-foreground" />
                <span class="font-medium">{{ project.ClientNAME }}</span>
              </div>
              <div v-if="project.ClientEMAIL" class="flex items-center gap-2">
                <Mail class="h-4 w-4 text-muted-foreground" />
                <a :href="`mailto:${project.ClientEMAIL}`" class="text-blue-600 hover:underline">
                  {{ project.ClientEMAIL }}
                </a>
              </div>
              <div v-if="project.ClientPHONE" class="flex items-center gap-2">
                <Phone class="h-4 w-4 text-muted-foreground" />
                <span>{{ project.ClientPHONE }}</span>
              </div>
            </div>
          </div>

          <!-- Assigned Staff -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold flex items-center gap-2">
              <Users class="h-5 w-5" />
              Assigned Staff ({{ project.staff?.length ?? 0 }})
            </h3>
            <div v-if="project.staff && project.staff.length > 0" class="grid grid-cols-2 gap-3">
              <div
                v-for="staff in project.staff"
                :key="staff.id"
                class="flex items-center gap-3 rounded-lg border p-3"
              >
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                  {{ staff.StaffNAME.charAt(0) }}
                </div>
                <div class="flex-1">
                  <p class="font-medium">{{ staff.StaffNAME }}</p>
                  <p class="text-xs text-muted-foreground">{{ staff.StaffEMAIL }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-center text-sm text-muted-foreground py-4">
              No staff assigned to this project
            </p>
          </div>
        </TabsContent>

        <!-- Tasks Tab -->
        <TabsContent value="tasks">
          <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold">Project Tasks</h3>
              <Link :href="`/tasks/create?project_id=${project.id}`" class="no-underline">
                <Button size="sm">Add Task</Button>
              </Link>
            </div>

            <Table class="border-y">
              <TableHeader>
                <TableRow>
                  <TableHead>Task Name</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Due Date</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="task in project.tasks" :key="task.id">
                  <TableCell class="font-medium">{{ task.TaskNAME }}</TableCell>
                  <TableCell>{{ task.TaskDESC || '-' }}</TableCell>
                  <TableCell>
                    <div class="flex items-center gap-2">
                      <Calendar class="h-4 w-4 text-muted-foreground" />
                      {{ formatDate(task.TaskDUE) }}
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :class="getTaskStatusColor(task.TaskSTATUS)">
                      {{ task.TaskSTATUS }}
                    </Badge>
                  </TableCell>
                </TableRow>
                <TableRow v-if="!project.tasks || project.tasks.length === 0">
                  <TableCell colspan="4" class="text-center py-6 text-muted-foreground">
                    No tasks found for this project
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </TabsContent>

        <!-- Phases Tab -->
        <TabsContent value="phases">
          <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold">Project Phases</h3>
              <Link :href="`/phases/create?project_id=${project.id}`" class="no-underline">
                <Button size="sm">Add Phase</Button>
              </Link>
            </div>

            <div v-if="project.phases && project.phases.length > 0" class="space-y-3">
              <div
                v-for="phase in project.phases"
                :key="phase.id"
                class="rounded-lg border p-4 hover:bg-gray-50"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <FolderOpen class="h-5 w-5 text-primary" />
                      <h4 class="font-semibold">{{ phase.PhaseNAME }}</h4>
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                      {{ phase.PhaseDESC || 'No description' }}
                    </p>
                    <p v-if="phase.PhaseUPDATE" class="mt-2 text-xs text-muted-foreground">
                      Last Update: {{ phase.PhaseUPDATE }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-center text-sm text-muted-foreground py-6">
              No phases defined for this project
            </p>
          </div>
        </TabsContent>

        <!-- Feedback Tab -->
        <TabsContent value="feedback">
          <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold">Project Feedback</h3>
              <Link :href="`/feedback/create?project_id=${project.id}`" class="no-underline">
                <Button size="sm">Add Feedback</Button>
              </Link>
            </div>

            <div v-if="project.feedback && project.feedback.length > 0" class="space-y-4">
              <div
                v-for="feedback in project.feedback"
                :key="feedback.id"
                class="rounded-lg border p-4"
              >
                <div class="flex items-start gap-3">
                  <MessageSquare class="h-5 w-5 text-primary mt-1" />
                  <div class="flex-1">
                    <h4 class="font-semibold">{{ feedback.FeedbackTITLE }}</h4>
                    <p class="mt-2 text-sm">{{ feedback.FeedbackDESC }}</p>
                    <p class="mt-2 text-xs text-muted-foreground">
                      {{ formatDateTime(feedback.FeedbackTIME) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-center text-sm text-muted-foreground py-6">
              No feedback recorded for this project
            </p>
          </div>
        </TabsContent>

        <!-- Logs Tab -->
        <TabsContent value="logs">
          <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold">Project Activity Logs</h3>

            <Table class="border-y">
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Time Spent</TableHead>
                  <TableHead>Phase (Before)</TableHead>
                  <TableHead>Phase (Current)</TableHead>
                  <TableHead>Change Reason</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="log in project.project_logs" :key="log.id">
                  <TableCell>{{ formatDate(log.ProjectDATE) }}</TableCell>
                  <TableCell>{{ log.ProjectTIME || '-' }}</TableCell>
                  <TableCell>{{ log.PhaseBEFORE || '-' }}</TableCell>
                  <TableCell>{{ log.PhaseCURRENT || '-' }}</TableCell>
                  <TableCell>{{ log.ChangeREASON || '-' }}</TableCell>
                </TableRow>
                <TableRow v-if="!project.project_logs || project.project_logs.length === 0">
                  <TableCell colspan="5" class="text-center py-6 text-muted-foreground">
                    No activity logs recorded
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </TabsContent>
      </Tabs>
    </BaseCard>
  </AppLayout>
</template>