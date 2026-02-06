<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

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

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: '/projects' },
  { title: 'Create Project', href: '/projects/create' }
];

const props = defineProps({
  staffList: { type: Object },
});

const form = ref({
  ProjectNAME: '',
  ProjectDESC: '',
  ProjectSTATUS: 'planning',
  ClientNAME: '',
  ClientPHONE: '',
  ClientEMAIL: '',
  staff_ids: [] as number[],
  ProjectSTART: '', 
  ProjectDUE: '',
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

  if (form.value.ProjectSTART && form.value.ProjectDUE) {
    if (new Date(form.value.ProjectDUE) < new Date(form.value.ProjectSTART)) {
      errors.value.ProjectDUE = 'Due date must be after start date';
      isValid = false;
    }
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
const toggleStaff = (staffId: number) => {
  const index = form.value.staff_ids.indexOf(staffId);

  if (index === -1) {
    form.value.staff_ids.push(staffId);
  } else {
    form.value.staff_ids.splice(index, 1);
  }
};

const submitForm = () => {
  if (!validateForm()) {
    toast.error('Please check your inputs');
    return;
  }

  isSubmitting.value = true;

  router.post('/projects', form.value, {
    onSuccess: () => {
      toast.success('Project created successfully!');
      isSubmitting.value = false;
    },
    onError: (err) => {
      console.log(err);
      toast.error('Failed to create project');
      isSubmitting.value = false;
    },
  });
};

const goBack = () => {
  router.visit('/projects');
};
</script>

<template>
  <Head title="Create Project" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Create New Project</BaseTitle>
        <Button variant="outline" @click="goBack" class="flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" />
          Back to Projects
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

            <!-- Project Dates -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Project Start Date</Label>
                <Input
                  v-model="form.ProjectSTART"
                  type="date"
                  :class="errors.ProjectSTART ? 'border-red-500' : ''"
                />
                <span v-if="errors.ProjectSTART" class="text-xs text-red-500">
                  {{ errors.ProjectSTART }}
                </span>
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Project Due Date</Label>
                <Input
                  v-model="form.ProjectDUE"
                  type="date"
                  :class="errors.ProjectDUE ? 'border-red-500' : ''"
                />
                <span v-if="errors.ProjectDUE" class="text-xs text-red-500">
                  {{ errors.ProjectDUE }}
                </span>
              </div>
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
              class="flex items-start space-x-3 rounded-lg border p-3 hover:bg-gray-50"
            >
              <Checkbox
  :checked="form.staff_ids.includes(staff.id)"
  @click="toggleStaff(staff.id)"
/>
              <label
                :for="`staff-${staff.id}`"
                class="flex-1 cursor-pointer"
              >
                <div class="text-sm font-medium">
                  {{ staff.StaffNAME }}
                </div>
                <div class="text-xs text-muted-foreground">
                  {{ staff.StaffEMAIL }}
                </div>
              </label>
            </div>

            <p v-if="staffList?.length === 0" class="text-center text-sm text-gray-500 py-4">
              No staff members available
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting" class="flex items-center gap-2">
            <Save class="h-4 w-4" />
            {{ isSubmitting ? 'Creating...' : 'Create Project' }}
          </Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
