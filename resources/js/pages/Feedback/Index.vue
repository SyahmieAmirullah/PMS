<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { formatDateTime } from '@/lib/date';

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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Feedback', href: '/feedback' },
];

const props = defineProps({
  feedback: { type: Object },
  projects: { type: Object },
  stats: { type: Object },
});

const filters = ref({
  FeedbackTITLE: '',
  ProjectID: '',
});

const itemsPerPage = ref(props.feedback?.per_page ?? 10);
const currentPage = ref(props.feedback?.current_page ?? 1);
const totalPages = computed(() => props.feedback?.last_page ?? 1);

const applyFilters = () => {
  router.get(
    '/feedback',
    {
      FeedbackTITLE: filters.value.FeedbackTITLE.trim() || undefined,
      ProjectID: filters.value.ProjectID || undefined,
      per_page: itemsPerPage.value,
      page: currentPage.value,
    },
    { preserveState: true, replace: true, preserveScroll: true },
  );
};

const resetFilters = () => {
  filters.value = { FeedbackTITLE: '', ProjectID: '' };
  currentPage.value = 1;
  itemsPerPage.value = 10;
  applyFilters();
};

const changePage = (page: number) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  applyFilters();
};

const goCreate = () => {
  router.get('/feedback/create');
};

const paginatedFeedback = computed(() => props.feedback?.data ?? []);
</script>

<template>
  <Head title="Feedback" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Feedback</BaseTitle>
        <Button @click="goCreate">Add Feedback</Button>
      </div>

      <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total</p>
          <h3 class="mt-2 text-2xl font-bold">{{ stats?.total ?? 0 }}</h3>
        </div>
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">This Week</p>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.thisWeek ?? 0 }}</h3>
        </div>
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">This Month</p>
          <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ stats?.thisMonth ?? 0 }}</h3>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex flex-col space-y-1">
            <Label>Title</Label>
            <Input v-model="filters.FeedbackTITLE" class="w-[220px]" placeholder="Search title" />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Project</Label>
            <Select v-model="filters.ProjectID">
              <SelectTrigger class="w-[220px]">
                <SelectValue placeholder="Select project" />
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

          <div class="flex gap-3">
            <Button class="px-6" @click="applyFilters">Search</Button>
            <Button class="px-6" variant="outline" @click="resetFilters">Reset</Button>
          </div>
        </div>
      </div>

      <div class="my-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <Table class="border-y">
          <TableHeader>
            <TableRow>
              <TableHead>No.</TableHead>
              <TableHead>Title</TableHead>
              <TableHead>Project</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(item, index) in paginatedFeedback" :key="item.id">
              <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
              <TableCell class="font-medium">{{ item.FeedbackTITLE }}</TableCell>
              <TableCell>{{ item.project?.ProjectNAME ?? '-' }}</TableCell>
              <TableCell>{{ formatDateTime(item.FeedbackTIME) }}</TableCell>
              <TableCell>
                <div class="flex gap-2">
                  <Button size="sm" variant="outline" @click="router.get(`/feedback/${item.id}`)">View</Button>
                  <Button size="sm" variant="destructive" @click="router.delete(`/feedback/${item.id}`)">Delete</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="paginatedFeedback.length === 0">
              <TableCell colspan="5" class="py-6 text-center text-gray-500">No feedback found</TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="mt-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Rows per page:</span>
            <Select v-model="itemsPerPage" @update:modelValue="applyFilters">
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
          </div>

          <div class="flex items-center gap-2">
            <Button size="sm" variant="outline" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">Previous</Button>
            <span class="text-sm">Page {{ currentPage }} / {{ totalPages }}</span>
            <Button size="sm" variant="outline" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">Next</Button>
          </div>
        </div>
      </div>
    </BaseCard>
  </AppLayout>
</template>
