<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
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

//const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tasks', href: '/tasks' },
  { title: 'Create Task', href: '/tasks/create' }
];

const props = defineProps({
  projects: { type: Object },
  preselectedProjectId: { type: [Number, String], default: null },
});

const form = ref({
  TaskNAME: '',
  TaskDESC: '',
  TaskDUE: '',
  ProjectID: '',
  StaffID: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const selectedProject = computed(() => {
  return (props.projects || []).find((p: any) => p.id === form.value.ProjectID);
});

const projectStaff = computed(() => selectedProject.value?.staff || []);

const staffOptions = computed(() => {
  return projectStaff.value || [];
});

// Set preselected project if provided
onMounted(() => {
  if (props.preselectedProjectId) {
    form.value.ProjectID = Number(props.preselectedProjectId);
  }
  resetStaffIfInvalid();
});

const resetStaffIfInvalid = () => {
  if (!form.value.StaffID) return;
  const exists = staffOptions.value.some((s: any) => s.id === form.value.StaffID);
  if (!exists) form.value.StaffID = '';
};

watch(
  () => form.value.ProjectID,
  () => {
    resetStaffIfInvalid();
  }
);

const validateForm = () => {
  errors.value = {};
  let isValid = true;

  if (!form.value.TaskNAME.trim()) {
    errors.value.TaskNAME = 'Task name is required';
    isValid = false;
  }

  if (!form.value.ProjectID) {
    errors.value.ProjectID = 'Please select a project';
    isValid = false;
  }

  if (!form.value.TaskDUE) {
    errors.value.TaskDUE = 'Due date is required';
    isValid = false;
  }

  return isValid;
};

const submitForm = () => {
  if (!validateForm()) {
    toast.error('Please check your inputs');
    return;
  }

  isSubmitting.value = true;

  router.post('/tasks/create', form.value, {
    onSuccess: () => {
      toast.success('Task created successfully!');
      isSubmitting.value = false;
    },
    onError: (err) => {
      console.log(err);
      toast.error('Failed to create task');
      isSubmitting.value = false;
    },
  });
};

const goBack = () => {
  router.visit('/tasks');
};
</script>

<template>
  <Head title="Create Task" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Create New Task</BaseTitle>
        <Button variant="outline" @click="goBack" class="flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" />
          Back to Tasks
        </Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Task Information Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Task Information</h3>
          
          <div class="space-y-4">
            <div class="flex flex-col space-y-1">
              <Label>Task Name <span class="text-red-500">*</span></Label>
              <Input
                v-model="form.TaskNAME"
                placeholder="Enter task name"
                :class="errors.TaskNAME ? 'border-red-500' : ''"
              />
              <span v-if="errors.TaskNAME" class="text-xs text-red-500">
                {{ errors.TaskNAME }}
              </span>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Task Description</Label>
              <Textarea
                v-model="form.TaskDESC"
                placeholder="Enter task description"
                rows="5"
              />
            </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col space-y-1">
              <Label>Project <span class="text-red-500">*</span></Label>
            <Select v-model="form.ProjectID">
                  <SelectTrigger :class="errors.ProjectID ? 'border-red-500' : ''">
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
                        <span class="text-xs text-muted-foreground ml-2">
                          ({{ project.ClientNAME }})
                        </span>
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <span v-if="errors.ProjectID" class="text-xs text-red-500">
                  {{ errors.ProjectID }}
                </span>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Due Date <span class="text-red-500">*</span></Label>
              <Input
                v-model="form.TaskDUE"
                type="date"
                :class="errors.TaskDUE ? 'border-red-500' : ''"
              />
              <span v-if="errors.TaskDUE" class="text-xs text-red-500">
                {{ errors.TaskDUE }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col space-y-1">
              <Label>Assign Staff</Label>
              <Select v-model="form.StaffID" @update:modelValue="resetStaffIfInvalid">
                <SelectTrigger :class="errors.StaffID ? 'border-red-500' : ''">
                  <SelectValue placeholder="Select Staff" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>Staff</SelectLabel>
                    <SelectItem
                      v-for="staff in staffOptions"
                      :key="staff.id"
                      :value="staff.id"
                    >
                      {{ staff.StaffNAME }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <span v-if="errors.StaffID" class="text-xs text-red-500">
                {{ errors.StaffID }}
              </span>
              <span v-if="form.ProjectID && staffOptions.length === 0" class="text-xs text-muted-foreground">
                No staff assigned to this project.
              </span>
            </div>
          </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting" class="flex items-center gap-2">
            <Save class="h-4 w-4" />
            {{ isSubmitting ? 'Creating...' : 'Create Task' }}
          </Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
