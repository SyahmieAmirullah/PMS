<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
  meeting: { type: Object, required: true },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Meetings', href: '/meetings' },
  { title: props.meeting.MeetingTITLE, href: `/meetings/${props.meeting.id}` },
];

const goBack = () => {
  router.visit('/meetings');
};
</script>

<template>
  <Head :title="meeting.MeetingTITLE" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <div>
          <BaseTitle size="2xl">{{ meeting.MeetingTITLE }}</BaseTitle>
          <p class="text-sm text-muted-foreground mt-1">{{ meeting.project?.ProjectNAME }}</p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="goBack">Back</Button>
          <Button @click="router.get(`/meetings/${meeting.id}/edit`)">Edit</Button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Date</p>
          <p class="mt-2">{{ meeting.MeetingDATE }}</p>
        </div>
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Time</p>
          <p class="mt-2">{{ meeting.MeetingTIME }}</p>
        </div>
        <div class="rounded-lg border bg-white p-4">
          <p class="text-sm font-medium text-muted-foreground">Meeting Link</p>
          <p class="mt-2">
            <a
              v-if="meeting.MeetingLINK"
              :href="meeting.MeetingLINK"
              class="text-blue-600 hover:underline"
              target="_blank"
            >
              {{ meeting.MeetingLINK }}
            </a>
            <span v-else>-</span>
          </p>
        </div>
      </div>

      <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Objective</h3>
        <p class="text-sm text-muted-foreground">{{ meeting.MeetingOBJECTIVE || '-' }}</p>
      </div>

      <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Attendance</h3>
        <div v-if="meeting.attendances && meeting.attendances.length" class="space-y-2">
          <div
            v-for="attendance in meeting.attendances"
            :key="attendance.id"
            class="flex items-center justify-between rounded border p-3"
          >
            <div>
              <div class="text-sm font-medium">{{ attendance.staff?.StaffNAME || '-' }}</div>
              <div class="text-xs text-muted-foreground">{{ attendance.staff?.StaffEMAIL || '' }}</div>
              <div class="text-xs text-muted-foreground">
                <span v-if="attendance.AttandanceLAT != null && attendance.AttandanceLNG != null">
                  Lat: {{ attendance.AttandanceLAT }}, Lng: {{ attendance.AttandanceLNG }}
                </span>
                <span v-else>Lat: -, Lng: -</span>
              </div>
            </div>
            <span class="text-xs font-medium">{{ attendance.AttandanceSTATUS }}</span>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">No attendance records.</p>
      </div>
    </BaseCard>
  </AppLayout>
</template>
