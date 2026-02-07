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
import { computed, nextTick, onMounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

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
  meeting: { type: Object, required: true },
  projects: { type: Object },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Meetings', href: '/meetings' },
  { title: props.meeting.MeetingTITLE, href: `/meetings/${props.meeting.id}` },
  { title: 'Edit', href: `/meetings/${props.meeting.id}/edit` },
];

const form = useForm({
  ProjectID: props.meeting.ProjectID,
  MeetingTITLE: props.meeting.MeetingTITLE,
  MeetingOBJECTIVE: props.meeting.MeetingOBJECTIVE ?? '',
  MeetingDATE: props.meeting.MeetingDATE,
  MeetingTIME: props.meeting.MeetingTIME,
  MeetingLINK: props.meeting.MeetingLINK ?? '',
  attendances: (props.meeting.attendances || []).map((a: any) => ({
    id: a.id,
    StaffID: a.StaffID,
    AttandanceSTATUS: a.AttandanceSTATUS,
    AttandanceTIME: a.AttandanceTIME,
    AbsentREASON: a.AbsentREASON ?? '',
    AttandanceLOCATION: a.AttandanceLOCATION ?? '',
    AttandanceLAT: a.AttandanceLAT ?? null,
    AttandanceLNG: a.AttandanceLNG ?? null,
    staff: a.staff,
  })),
});

const projectOptions = computed(() => props.projects ?? []);

const attendanceStatusOptions = [
  { value: 'present', label: 'Present' },
  { value: 'absent', label: 'Absent' },
  { value: 'late', label: 'Late' },
  { value: 'excused', label: 'Excused' },
];

const mapInstances = new Map<number, L.Map>();
const markerInstances = new Map<number, L.Marker>();

const updateAttendanceLatLng = (index: number, latLng: L.LatLng) => {
  form.attendances[index].AttandanceLAT = Number(latLng.lat.toFixed(7));
  form.attendances[index].AttandanceLNG = Number(latLng.lng.toFixed(7));
};

const detectLocation = (index: number) => {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const latLng = L.latLng(pos.coords.latitude, pos.coords.longitude);
      updateAttendanceLatLng(index, latLng);

      const attendanceId = form.attendances[index].id ?? index;
      const map = mapInstances.get(attendanceId);
      const marker = markerInstances.get(attendanceId);
      if (map && marker) {
        map.setView(latLng, 15);
        marker.setLatLng(latLng);
      }
    },
    () => {},
    { enableHighAccuracy: true, timeout: 8000 }
  );
};

const initAttendanceMap = async (index: number) => {
  const attendance = form.attendances[index];
  const attendanceId = attendance.id ?? index;
  const containerId = `attendance-map-${attendanceId}`;

  if (mapInstances.has(attendanceId)) return;

  await nextTick();
  const container = document.getElementById(containerId);
  if (!container) return;

  const lat = attendance.AttandanceLAT ?? 3.1390;
  const lng = attendance.AttandanceLNG ?? 101.6869;

  const map = L.map(containerId).setView([lat, lng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map);

  const marker = L.marker([lat, lng], { draggable: true }).addTo(map);
  marker.on('dragend', () => {
    const pos = marker.getLatLng();
    updateAttendanceLatLng(index, pos);
  });

  map.on('click', (e: L.LeafletMouseEvent) => {
    marker.setLatLng(e.latlng);
    updateAttendanceLatLng(index, e.latlng);
  });

  mapInstances.set(attendanceId, map);
  markerInstances.set(attendanceId, marker);

  if (attendance.AttandanceLAT == null || attendance.AttandanceLNG == null) {
    detectLocation(index);
  }
};

onMounted(() => {
  form.attendances.forEach((_, index) => initAttendanceMap(index));
});

watch(
  () => form.attendances.length,
  () => {
    form.attendances.forEach((_, index) => initAttendanceMap(index));
  }
);

const submitForm = () => {
  form.put(`/meetings/${props.meeting.id}`);
};

const goBack = () => {
  router.visit(`/meetings/${props.meeting.id}`);
};
</script>

<template>
  <Head :title="`Edit ${meeting.MeetingTITLE}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Edit Meeting</BaseTitle>
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

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Attendance</h3>

          <div v-if="form.attendances.length" class="space-y-4">
            <div v-for="(attendance, index) in form.attendances" :key="attendance.id" class="rounded border p-4">
              <div class="mb-2 text-sm font-medium">
                {{ attendance.staff?.StaffNAME || 'Staff' }}
                <span class="text-xs text-muted-foreground">{{ attendance.staff?.StaffEMAIL || '' }}</span>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col space-y-1">
                  <Label>Status</Label>
                  <Select v-model="form.attendances[index].AttandanceSTATUS">
                    <SelectTrigger>
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectGroup>
                        <SelectLabel>Status</SelectLabel>
                        <SelectItem
                          v-for="status in attendanceStatusOptions"
                          :key="status.value"
                          :value="status.value"
                        >
                          {{ status.label }}
                        </SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                </div>

                <div class="flex flex-col space-y-1">
                  <Label>Time</Label>
                  <Input v-model="form.attendances[index].AttandanceTIME" type="time" />
                </div>
              </div>

              <div class="mt-3 grid grid-cols-2 gap-4">
                <div class="flex flex-col space-y-1">
                  <Label>Reason (if absent)</Label>
                  <Input v-model="form.attendances[index].AbsentREASON" placeholder="Reason" />
                </div>

                <div class="flex flex-col space-y-1">
                  <Label>Location</Label>
                  <Input v-model="form.attendances[index].AttandanceLOCATION" placeholder="Location" />
                </div>
              </div>

              <div class="mt-3 flex items-center gap-3">
                <Button size="sm" variant="outline" @click="detectLocation(index)">
                  Use My Location
                </Button>
                <span class="text-xs text-muted-foreground">
                  {{ attendance.AttandanceLAT ? `Lat: ${attendance.AttandanceLAT}` : 'Lat: -' }},
                  {{ attendance.AttandanceLNG ? `Lng: ${attendance.AttandanceLNG}` : 'Lng: -' }}
                </span>
              </div>

              <div
                :id="`attendance-map-${attendance.id ?? index}`"
                class="mt-3 h-[220px] w-full rounded border"
              ></div>
            </div>
          </div>

          <p v-else class="text-sm text-muted-foreground">No attendance records.</p>
        </div>

        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Save Changes</Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>
