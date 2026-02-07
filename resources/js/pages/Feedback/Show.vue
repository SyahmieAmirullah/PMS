<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
  feedback: { type: Object, required: true },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Feedback', href: '/feedback' },
  { title: props.feedback.FeedbackTITLE, href: `/feedback/${props.feedback.id}` },
];

const goBack = () => {
  router.visit('/feedback');
};
</script>

<template>
  <Head :title="feedback.FeedbackTITLE" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <div>
          <BaseTitle size="2xl">{{ feedback.FeedbackTITLE }}</BaseTitle>
          <p class="text-sm text-muted-foreground mt-1">{{ feedback.project?.ProjectNAME }}</p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="goBack">Back</Button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Feedback Time</p>
          <p class="mt-2">{{ feedback.FeedbackTIME }}</p>
        </div>
      </div>

      <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Description</h3>
        <p class="text-sm text-muted-foreground">{{ feedback.FeedbackDESC || '-' }}</p>
      </div>
    </BaseCard>
  </AppLayout>
</template>
