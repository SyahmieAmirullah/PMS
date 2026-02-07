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
  { title: 'Meetings', href: '/meetings' },
  { title: 'Create', href: '/meetings/create' },
];

const form = useForm({
  ProjectID: props.preselectedProjectId ? Number(props.preselectedProjectId) : '',
  MeetingTITLE: '',
  MeetingOBJECTIVE: '',
  MeetingDATE: '',
  MeetingTIME: '',
  MeetingLINK: '',
});

const projectOptions = computed(() => props.projects ?? []);

const submitForm = () => {
  form.post('/meetings');
};

const goBack = () => {
  router.visit('/meetings');
};
</script>

<template>
  <Head title="Create Meeting" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Create Meeting</BaseTitle>
        <Button variant="outline" @click="goBack">Back</Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Meeting Details</h3>

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
              <Input v-model="form.MeetingTITLE" placeholder="Enter meeting title" />
              <div v-if="form.errors.MeetingTITLE" class="text-xs text-red-500">
                {{ form.errors.MeetingTITLE }}
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Objective</Label>
              <Textarea v-model="form.MeetingOBJECTIVE" placeholder="Meeting objective" rows="4" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Date <span class="text-red-500">*</span></Label>
                <Input v-model="form.MeetingDATE" type="date" />
                <div v-if="form.errors.MeetingDATE" class="text-xs text-red-500">
                  {{ form.errors.MeetingDATE }}
                </div>
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Time <span class="text-red-500">*</span></Label>
                <Input v-model="form.MeetingTIME" type="time" />
                <div v-if="form.errors.MeetingTIME" class="text-xs text-red-500">
                  {{ form.errors.MeetingTIME }}
                </div>
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Meeting Link</Label>
              <Input v-model="form.MeetingLINK" placeholder="https://meet.example.com/..." />
              <div v-if="form.errors.MeetingLINK" class="text-xs text-red-500">
                {{ form.errors.MeetingLINK }}
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Create Meeting</Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
