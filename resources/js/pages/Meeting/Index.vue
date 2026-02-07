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
  { title: 'Meetings', href: '/meetings' },
];

const props = defineProps({
  meetings: { type: Object },
  projects: { type: Object },
  stats: { type: Object },
});

const filters = ref({
  MeetingTITLE: '',
  ProjectID: '',
  date_from: '',
  date_to: '',
});

const itemsPerPage = ref(props.meetings?.per_page ?? 10);
const currentPage = ref(props.meetings?.current_page ?? 1);
const totalPages = computed(() => props.meetings?.last_page ?? 1);

const applyFilters = () => {
  router.get(
    '/meetings',
    {
      MeetingTITLE: filters.value.MeetingTITLE.trim() || undefined,
      ProjectID: filters.value.ProjectID || undefined,
      date_from: filters.value.date_from || undefined,
      date_to: filters.value.date_to || undefined,
      per_page: itemsPerPage.value,
      page: currentPage.value,
    },
    { preserveState: true, replace: true, preserveScroll: true },
  );
};

const resetFilters = () => {
  filters.value = { MeetingTITLE: '', ProjectID: '', date_from: '', date_to: '' };
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
  router.get('/meetings/create');
};

const paginatedMeetings = computed(() => props.meetings?.data ?? []);
</script>

<template>
  <Head title="Meetings" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Meetings</BaseTitle>
        <Button @click="goCreate">Add Meeting</Button>
      </div>

      <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total</p>
          <h3 class="mt-2 text-2xl font-bold">{{ stats?.total ?? 0 }}</h3>
        </div>
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Upcoming</p>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.upcoming ?? 0 }}</h3>
        </div>
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Past</p>
          <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ stats?.past ?? 0 }}</h3>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex flex-col space-y-1">
            <Label>Title</Label>
            <Input v-model="filters.MeetingTITLE" class="w-[220px]" placeholder="Search title" />
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

          <div class="flex flex-col space-y-1">
            <Label>Date From</Label>
            <Input v-model="filters.date_from" type="date" class="w-[180px]" />
          </div>

          <div class="flex flex-col space-y-1">
            <Label>Date To</Label>
            <Input v-model="filters.date_to" type="date" class="w-[180px]" />
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
              <TableHead>Time</TableHead>
              <TableHead>Link</TableHead>
              <TableHead>Attendance</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(meeting, index) in paginatedMeetings" :key="meeting.id">
              <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
              <TableCell class="font-medium">{{ meeting.MeetingTITLE }}</TableCell>
              <TableCell>{{ meeting.project?.ProjectNAME ?? '-' }}</TableCell>
              <TableCell>{{ meeting.MeetingDATE }}</TableCell>
              <TableCell>{{ meeting.MeetingTIME }}</TableCell>
              <TableCell>
                <a
                  v-if="meeting.MeetingLINK"
                  :href="meeting.MeetingLINK"
                  class="text-blue-600 hover:underline"
                  target="_blank"
                >
                  Link
                </a>
                <span v-else>-</span>
              </TableCell>
              <TableCell>{{ meeting.attendances_count ?? 0 }}</TableCell>
              <TableCell>
                <div class="flex gap-2">
                  <Button size="sm" variant="outline" @click="router.get(`/meetings/${meeting.id}`)">View</Button>
                  <Button size="sm" variant="outline" @click="router.get(`/meetings/${meeting.id}/edit`)">Edit</Button>
                  <Button size="sm" variant="destructive" @click="router.delete(`/meetings/${meeting.id}`)">Delete</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="paginatedMeetings.length === 0">
              <TableCell colspan="7" class="py-6 text-center text-gray-500">No meetings found</TableCell>
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
