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

const props = defineProps({
  phase: { type: Object, required: true },
  projects: { type: Object },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Phases', href: '/phases' },
  { title: props.phase.PhaseNAME, href: `/phases/${props.phase.id}` },
  { title: 'Edit', href: `/phases/${props.phase.id}/edit` },
];

const form = useForm({
  ProjectID: props.phase.ProjectID,
  PhaseNAME: props.phase.PhaseNAME,
  PhaseDESC: props.phase.PhaseDESC ?? '',
  PhaseUPDATE: props.phase.PhaseUPDATE ?? '',
  PhaseORDER: props.phase.PhaseORDER ?? 0,
  documents: [] as File[],
  delete_document_ids: [] as number[],
});

const projectOptions = computed(() => props.projects ?? []);
const sdlcPhases = [
  { value: 'Planning', label: 'Planning' },
  { value: 'Requirements', label: 'Requirements' },
  { value: 'Design', label: 'Design' },
  { value: 'Implementation', label: 'Implementation' },
  { value: 'Testing', label: 'Testing' },
  { value: 'Deployment', label: 'Deployment' },
  { value: 'Maintenance', label: 'Maintenance' },
];

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files) return;
  form.documents = Array.from(target.files);
};

const toggleDelete = (docId: number) => {
  const idx = form.delete_document_ids.indexOf(docId);
  if (idx === -1) form.delete_document_ids.push(docId);
  else form.delete_document_ids.splice(idx, 1);
};

const submitForm = () => {
  form
    .transform((data) => ({
      ...data,
      _method: 'put',
    }))
    .post(`/phases/${props.phase.id}`, {
      forceFormData: true,
    });
};

const goBack = () => {
  // router.visit(`/phases/${props.phase.id}`);
  router.visit('/phases');
};
</script>

<template>
  <Head :title="`Edit ${phase.PhaseNAME}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Edit Phase</BaseTitle>
        <Button variant="outline" @click="goBack">Back</Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Phase Details</h3>

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
              <Label>Phase Name <span class="text-red-500">*</span></Label>
              <Select v-model="form.PhaseNAME">
                <SelectTrigger>
                  <SelectValue placeholder="Select phase" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>SDLC Phases</SelectLabel>
                    <SelectItem
                      v-for="phase in sdlcPhases"
                      :key="phase.value"
                      :value="phase.value"
                    >
                      {{ phase.label }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <div v-if="form.errors.PhaseNAME" class="text-xs text-red-500">
                {{ form.errors.PhaseNAME }}
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Description</Label>
              <Textarea v-model="form.PhaseDESC" placeholder="Phase description" rows="3" />
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Update</Label>
              <Textarea v-model="form.PhaseUPDATE" placeholder="Latest update" rows="2" />
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Order</Label>
              <Input v-model.number="form.PhaseORDER" type="number" min="0" />
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Documents</h3>

          <div v-if="phase.documents?.length" class="mb-4 space-y-2">
            <div v-for="doc in phase.documents" :key="doc.id" class="flex items-center justify-between rounded border p-3">
              <div class="text-sm">{{ doc.DocumentNAME }}</div>
              <label class="flex items-center gap-2 text-xs text-muted-foreground">
                <input
                  type="checkbox"
                  :checked="form.delete_document_ids.includes(doc.id)"
                  @change="toggleDelete(doc.id)"
                />
                Remove
              </label>
            </div>
          </div>

          <Input type="file" multiple @change="onFileChange" />
          <div class="mt-2 text-xs text-muted-foreground">Upload one or multiple files (max 10MB each).</div>
        </div>

        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Save Changes</Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
