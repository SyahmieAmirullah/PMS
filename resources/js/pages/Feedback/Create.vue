<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const props = defineProps({
  projects: { type: Object },
  preselectedProjectId: { type: [String, Number, null], default: null },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Feedback', href: '/feedback' },
  { title: 'Create', href: '/feedback/create' },
];

const form = useForm({
  ProjectID: props.preselectedProjectId ? Number(props.preselectedProjectId) : '',
  FeedbackTITLE: '',
  FeedbackDESC: '',
  FeedbackTIME: new Date().toISOString().slice(0, 16),
});

const projectOptions = computed(() => props.projects ?? []);

const submitForm = () => {
  form.post('/feedback');
};

const goBack = () => {
  router.visit('/feedback');
};
</script>

<template>
  <Head title="Create Feedback" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Create Feedback</BaseTitle>
        <Button variant="outline" @click="goBack">Back</Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Feedback Details</h3>

          <div class="space-y-4">
            <div class="flex flex-col space-y-1">
              <Label>Project <span class="text-red-500">*</span></Label>
              <Select v-model="form.ProjectID">
                <SelectTrigger>
                  <SelectValue placeholder="Select project" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>Projects</SelectLabel>
                    <SelectItem
                      v-for="project in projectOptions"
                      :key="project.id"
                      :value="project.id"
                    >
                      {{ project.ProjectNAME }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <div v-if="form.errors.ProjectID" class="text-xs text-red-500">
                {{ form.errors.ProjectID }}
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Title <span class="text-red-500">*</span></Label>
              <Input v-model="form.FeedbackTITLE" placeholder="Enter title" />
              <div v-if="form.errors.FeedbackTITLE" class="text-xs text-red-500">
                {{ form.errors.FeedbackTITLE }}
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Description</Label>
              <Textarea v-model="form.FeedbackDESC" placeholder="Feedback details" rows="4" />
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Feedback Time <span class="text-red-500">*</span></Label>
              <Input v-model="form.FeedbackTIME" type="datetime-local" />
              <div v-if="form.errors.FeedbackTIME" class="text-xs text-red-500">
                {{ form.errors.FeedbackTIME }}
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Create Feedback</Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
