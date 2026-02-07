<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
  phase: { type: Object, required: true },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Phases', href: '/phases' },
  { title: props.phase.PhaseNAME, href: `/phases/${props.phase.id}` },
];

const goBack = () => {
  router.visit('/phases');
};
</script>

<template>
  <Head :title="phase.PhaseNAME" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <div>
          <BaseTitle size="2xl">{{ phase.PhaseNAME }}</BaseTitle>
          <p class="text-sm text-muted-foreground mt-1">{{ phase.project?.ProjectNAME }}</p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="goBack">Back</Button>
          <Button @click="router.get(`/phases/${phase.id}/edit`)">Edit</Button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Description</p>
          <p class="mt-2">{{ phase.PhaseDESC || '-' }}</p>
        </div>
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Update</p>
          <p class="mt-2">{{ phase.PhaseUPDATE || '-' }}</p>
        </div>
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Order</p>
          <p class="mt-2">{{ phase.PhaseORDER ?? 0 }}</p>
        </div>
      </div>

      <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Documents</h3>
        <div v-if="phase.documents && phase.documents.length" class="space-y-2">
          <div v-for="doc in phase.documents" :key="doc.id" class="flex items-center justify-between rounded border p-3">
            <div>
              <div class="text-sm font-medium">{{ doc.DocumentNAME }}</div>
              <div class="text-xs text-muted-foreground">Version: {{ doc.DocumentVERSION || '1.0' }}</div>
            </div>
            <a v-if="doc.document_url" :href="doc.document_url" class="text-sm text-blue-600 hover:underline" target="_blank">View</a>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">No documents uploaded.</p>
      </div>
    </BaseCard>
  </AppLayout>
</template>
