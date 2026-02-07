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
  { title: 'Phases', href: '/phases' },
];

const props = defineProps({
  phases: { type: Object },
  projects: { type: Object },
  stats: { type: Object },
});

const filters = ref({
  PhaseNAME: '',
  ProjectID: '',
});

const itemsPerPage = ref(props.phases?.per_page ?? 10);
const currentPage = ref(props.phases?.current_page ?? 1);
const totalPages = computed(() => props.phases?.last_page ?? 1);

const applyFilters = () => {
  router.get(
    '/phases',
    {
      PhaseNAME: filters.value.PhaseNAME.trim() || undefined,
      ProjectID: filters.value.ProjectID || undefined,
      per_page: itemsPerPage.value,
      page: currentPage.value,
    },
    { preserveState: true, replace: true, preserveScroll: true },
  );
};

const resetFilters = () => {
  filters.value = { PhaseNAME: '', ProjectID: '' };
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
  router.get('/phases/create');
};

const paginatedPhases = computed(() => props.phases?.data ?? []);
</script>

<template>
  <Head title="Phases" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Phases</BaseTitle>
        <Button @click="goCreate">Add Phase</Button>
      </div>

      <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Total</p>
          <h3 class="mt-2 text-2xl font-bold">{{ stats?.total ?? 0 }}</h3>
        </div>
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">With Documents</p>
          <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.withDocuments ?? 0 }}</h3>
        </div>
        <div class="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
          <p class="text-sm font-medium text-muted-foreground">Without Documents</p>
          <h3 class="mt-2 text-2xl font-bold text-yellow-600">{{ stats?.withoutDocuments ?? 0 }}</h3>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex flex-col space-y-1">
            <Label>Phase Name</Label>
            <Input v-model="filters.PhaseNAME" class="w-[220px]" placeholder="Search phase" />
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
              <TableHead>Phase</TableHead>
              <TableHead>Project</TableHead>
              <TableHead>Order</TableHead>
              <TableHead>Docs</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(phase, index) in paginatedPhases" :key="phase.id">
              <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
              <TableCell class="font-medium">{{ phase.PhaseNAME }}</TableCell>
              <TableCell>{{ phase.project?.ProjectNAME ?? '-' }}</TableCell>
              <TableCell>{{ phase.PhaseORDER ?? 0 }}</TableCell>
              <TableCell>{{ phase.documents_count ?? 0 }}</TableCell>
              <TableCell>
                <div class="flex gap-2">
                  <Button size="sm" variant="outline" @click="router.get(`/phases/${phase.id}`)">View</Button>
                  <Button size="sm" variant="outline" @click="router.get(`/phases/${phase.id}/edit`)">Edit</Button>
                  <Button size="sm" variant="destructive" @click="router.delete(`/phases/${phase.id}`)">Delete</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="paginatedPhases.length === 0">
              <TableCell colspan="6" class="py-6 text-center text-gray-500">No phases found</TableCell>
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
