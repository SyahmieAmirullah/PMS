<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
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

import Textarea from '@/components/ui/textarea/Textarea.vue';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';

//const { t } = useI18n();

const props = defineProps({
  project: { type: Object, required: true },
  staffList: { type: Object },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: '/projects' },
  { title: props.project.ProjectNAME, href: `/projects/${props.project.id}` },
  { title: 'Edit', href: `/projects/${props.project.id}/edit` }
];

const form = ref({
  ProjectNAME: '',
  ProjectDESC: '',
  ProjectSTATUS: '',
  ClientNAME: '',
  ClientPHONE: '',
  ClientEMAIL: '',
  staff_ids: [] as number[],
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const projectStatuses = [
  { value: 'planning', label: 'Planning' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
];

// Populate form with existing data
onMounted(() => {
  form.value = {
    ProjectNAME: props.project.ProjectNAME || '',
    ProjectDESC: props.project.ProjectDESC || '',
    ProjectSTATUS: props.project.ProjectSTATUS || 'planning',
    ClientNAME: props.project.ClientNAME || '',
    ClientPHONE: props.project.ClientPHONE || '',
    ClientEMAIL: props.project.ClientEMAIL || '',
    staff_ids: props.project.staff?.map((s: any) => s.id) || [],
  };
});

const validateForm = () => {
  errors.value = {};
  let isValid = true;

  if (!form.value.ProjectNAME.trim()) {
    errors.value.ProjectNAME = 'Project name is required';
    isValid = false;
  }

  if (!form.value.ClientNAME.trim()) {
    errors.value.ClientNAME = 'Client name is required';
    isValid = false;
  }

  if (form.value.ClientEMAIL && !isValidEmail(form.value.ClientEMAIL)) {
    errors.value.ClientEMAIL = 'Please enter a valid email address';
    isValid = false;
  }

  return isValid;
};

const isValidEmail = (email: string) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

const setStaffSelected = (staffId: number, checked: boolean | 'indeterminate') => {
  const isChecked = checked === true;
  const index = form.value.staff_ids.indexOf(staffId);
  if (isChecked && index === -1) {
    form.value.staff_ids.push(staffId);
  } else if (!isChecked && index > -1) {
    form.value.staff_ids.splice(index, 1);
  }
};

const isStaffSelected = (staffId: number) => form.value.staff_ids.includes(staffId);

const submitForm = () => {
  if (!validateForm()) {
    toast.error('Please check your inputs');
    return;
  }

  isSubmitting.value = true;

  router.put(`/projects/${props.project.id}`, form.value, {
    onSuccess: () => {
      toast.success('Project updated successfully!');
      isSubmitting.value = false;
    },
    onError: (err) => {
      console.log(err);
      toast.error('Failed to update project');
      isSubmitting.value = false;
    },
  });
};

const goBack = () => {
  router.visit(`/projects/${props.project.id}`);
};
</script>

<template>
  <Head :title="`Edit ${project.ProjectNAME}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <div>
          <BaseTitle size="2xl">Edit Project</BaseTitle>
          <p class="text-sm text-muted-foreground mt-1">{{ project.ProjectNAME }}</p>
        </div>
        <Button variant="outline" @click="goBack" class="flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" />
          Back to Project
        </Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Project Information Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Project Information</h3>
          
          <div class="space-y-4">
            <div class="flex flex-col space-y-1">
              <Label>Project Name <span class="text-red-500">*</span></Label>
              <Input
                v-model="form.ProjectNAME"
                placeholder="Enter project name"
                :class="errors.ProjectNAME ? 'border-red-500' : ''"
              />
              <span v-if="errors.ProjectNAME" class="text-xs text-red-500">
                {{ errors.ProjectNAME }}
              </span>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Project Description</Label>
              <Textarea
                v-model="form.ProjectDESC"
                placeholder="Enter project description"
                rows="4"
              />
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Status <span class="text-red-500">*</span></Label>
              <Select v-model="form.ProjectSTATUS">
                <SelectTrigger>
                  <SelectValue placeholder="Select Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>Project Status</SelectLabel>
                    <SelectItem
                      v-for="status in projectStatuses"
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
        </div>

        <!-- Client Information Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Client Information</h3>
          
          <div class="space-y-4">
            <div class="flex flex-col space-y-1">
              <Label>Client Name <span class="text-red-500">*</span></Label>
              <Input
                v-model="form.ClientNAME"
                placeholder="Enter client name"
                :class="errors.ClientNAME ? 'border-red-500' : ''"
              />
              <span v-if="errors.ClientNAME" class="text-xs text-red-500">
                {{ errors.ClientNAME }}
              </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Client Phone</Label>
                <Input
                  v-model="form.ClientPHONE"
                  placeholder="Enter client phone number"
                />
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Client Email</Label>
                <Input
                  v-model="form.ClientEMAIL"
                  type="email"
                  placeholder="Enter client email"
                  :class="errors.ClientEMAIL ? 'border-red-500' : ''"
                />
                <span v-if="errors.ClientEMAIL" class="text-xs text-red-500">
                  {{ errors.ClientEMAIL }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Staff Assignment Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Assign Staff Members</h3>
          
          <div class="space-y-2">
            <div
              v-for="staff in staffList"
              :key="staff.id"
              class="flex items-center space-x-3 rounded-lg border p-3 hover:bg-gray-50"
            >
              <Checkbox
                :id="`staff-${staff.id}`"
                :checked="isStaffSelected(staff.id)"
                @update:checked="(checked) => setStaffSelected(staff.id, checked)"
              />
              <label
                :for="`staff-${staff.id}`"
                class="flex-1 cursor-pointer text-sm font-medium"
              >
                <div>{{ staff.StaffNAME }}</div>
                <div class="text-xs text-muted-foreground">{{ staff.StaffEMAIL }}</div>
              </label>
            </div>

            <p v-if="staffList?.length === 0" class="text-center text-sm text-gray-500 py-4">
              No staff members available
            </p>
          </div>
        </div>

        <!-- Project Statistics (Read-only) -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Project Statistics</h3>
          
          <div class="grid grid-cols-3 gap-4">
            <div class="rounded-lg border bg-gray-50 p-4">
              <p class="text-sm font-medium text-muted-foreground">Total Tasks</p>
              <p class="mt-2 text-2xl font-bold">{{ project.tasks?.length ?? 0 }}</p>
            </div>

            <div class="rounded-lg border bg-gray-50 p-4">
              <p class="text-sm font-medium text-muted-foreground">Total Phases</p>
              <p class="mt-2 text-2xl font-bold">{{ project.phases?.length ?? 0 }}</p>
            </div>

            <div class="rounded-lg border bg-gray-50 p-4">
              <p class="text-sm font-medium text-muted-foreground">Assigned Staff</p>
              <p class="mt-2 text-2xl font-bold">{{ form.staff_ids.length }}</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
          <Button type="button" variant="outline" @click="goBack">
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting" class="flex items-center gap-2">
            <Save class="h-4 w-4" />
            {{ isSubmitting ? 'Updating...' : 'Update Project' }}
          </Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
