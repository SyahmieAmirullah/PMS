<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/date';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import 'leaflet/dist/leaflet.css';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const props = defineProps({
    meeting: { type: Object, required: true },
    canSelfAttend: { type: Boolean, default: true },
    assignedStaffIds: { type: Array, default: () => [] },
});

const page = usePage();
const auth = computed(() => (page.props.auth as any) || {});
const isStaff = computed(() => auth.value.guard === 'staff');
const staffId = computed(() => auth.value.user?.id);

const myAttendance = computed(() => {
    if (!props.canSelfAttend) return null;
    return (props.meeting.attendances || []).find(
        (a: any) => a.StaffID === staffId.value,
    );
});

const form = useForm({
    AttandanceSTATUS: myAttendance.value?.AttandanceSTATUS ?? 'pending',
    AbsentREASON: myAttendance.value?.AbsentREASON ?? '',
    AttandanceLOCATION: myAttendance.value?.AttandanceLOCATION ?? '',
    AttandanceLAT: myAttendance.value?.AttandanceLAT ?? null,
    AttandanceLNG: myAttendance.value?.AttandanceLNG ?? null,
});

watch(myAttendance, (value) => {
    form.AttandanceSTATUS = value?.AttandanceSTATUS ?? 'pending';
    form.AbsentREASON = value?.AbsentREASON ?? '';
    form.AttandanceLOCATION = value?.AttandanceLOCATION ?? '';
    form.AttandanceLAT = value?.AttandanceLAT ?? null;
    form.AttandanceLNG = value?.AttandanceLNG ?? null;
    initSelfMap();
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Meetings', href: '/meetings' },
    {
        title: props.meeting.MeetingTITLE,
        href: `/meetings/${props.meeting.id}`,
    },
];

const goBack = () => {
    router.visit('/meetings');
};

const mapInstance = ref<L.Map | null>(null);
const markerInstance = ref<L.Marker | null>(null);
const mapReady = ref(false);

const updateSelfLatLng = (latLng: L.LatLng) => {
    form.AttandanceLAT = Number(latLng.lat.toFixed(7));
    form.AttandanceLNG = Number(latLng.lng.toFixed(7));
};

const detectLocation = () => {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const latLng = L.latLng(pos.coords.latitude, pos.coords.longitude);
            updateSelfLatLng(latLng);

            if (mapInstance.value && markerInstance.value) {
                mapInstance.value.setView(latLng, 15);
                markerInstance.value.setLatLng(latLng);
            }
        },
        () => {},
        { enableHighAccuracy: true, timeout: 8000 },
    );
};

const initSelfMap = async () => {
    if (!isStaff.value || !props.canSelfAttend || mapReady.value) return;
    await nextTick();
    const container = document.getElementById('attendance-map-self');
    if (!container) return;

    const lat = form.AttandanceLAT ?? 3.139;
    const lng = form.AttandanceLNG ?? 101.6869;

    const map = L.map('attendance-map-self').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', () => {
        const pos = marker.getLatLng();
        updateSelfLatLng(pos);
    });

    map.on('click', (e: L.LeafletMouseEvent) => {
        marker.setLatLng(e.latlng);
        updateSelfLatLng(e.latlng);
    });

    mapInstance.value = map;
    markerInstance.value = marker;
    mapReady.value = true;

    if (form.AttandanceLAT == null || form.AttandanceLNG == null) {
        detectLocation();
    }
};

const submitAttendance = () => {
    form.post(`/meetings/${props.meeting.id}/attendance/self`, {
        preserveScroll: true,
    });
};

onMounted(() => {
    initSelfMap();
});

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        present: 'bg-green-100 text-green-700 border-green-200',
        absent: 'bg-red-100 text-red-700 border-red-200',
        late: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        excused: 'bg-blue-100 text-blue-700 border-blue-200',
        pending: 'bg-gray-100 text-gray-700 border-gray-200',
    };
    return map[status] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        present: 'Present',
        absent: 'Absent',
        late: 'Late',
        excused: 'Excused',
        pending: 'Pending',
    };
    return map[status] || status;
};
</script>

<template>
    <Head :title="meeting.MeetingTITLE" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BaseCard>
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <BaseTitle size="2xl">{{ meeting.MeetingTITLE }}</BaseTitle>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ meeting.project?.ProjectNAME }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="goBack">Back</Button>
                    <Button @click="router.get(`/meetings/${meeting.id}/edit`)"
                        >Edit</Button
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm font-medium text-muted-foreground">
                        Date
                    </p>
                    <p class="mt-2">{{ formatDate(meeting.MeetingDATE) }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm font-medium text-muted-foreground">
                        Time
                    </p>
                    <p class="mt-2">{{ meeting.MeetingTIME }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm font-medium text-muted-foreground">
                        Meeting Link
                    </p>
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

            <div
                class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <h3 class="mb-4 text-lg font-semibold">Objective</h3>
                <p class="text-sm text-muted-foreground">
                    {{ meeting.MeetingOBJECTIVE || '-' }}
                </p>
            </div>

            <div
                v-if="isStaff && canSelfAttend"
                class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <h3 class="mb-4 text-lg font-semibold">My Attendance</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1">
                        <Label>Status</Label>
                        <Select v-model="form.AttandanceSTATUS">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Status</SelectLabel>
                                    <SelectItem value="pending"
                                        >Pending</SelectItem
                                    >
                                    <SelectItem value="present"
                                        >Present</SelectItem
                                    >
                                    <SelectItem value="absent"
                                        >Absent</SelectItem
                                    >
                                    <SelectItem value="late">Late</SelectItem>
                                    <SelectItem value="excused"
                                        >Excused</SelectItem
                                    >
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex flex-col space-y-1">
                        <Label>Reason (if absent)</Label>
                        <Input
                            v-model="form.AbsentREASON"
                            placeholder="Reason"
                        />
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1">
                        <Label>Location</Label>
                        <Input
                            v-model="form.AttandanceLOCATION"
                            placeholder="Location"
                        />
                    </div>
                    <div class="flex items-end">
                        <Button
                            size="sm"
                            variant="outline"
                            @click="detectLocation"
                        >
                            Use My Location
                        </Button>
                    </div>
                </div>
                <div class="mt-3 text-xs text-muted-foreground">
                    {{
                        form.AttandanceLAT
                            ? `Lat: ${form.AttandanceLAT}`
                            : 'Lat: -'
                    }},
                    {{
                        form.AttandanceLNG
                            ? `Lng: ${form.AttandanceLNG}`
                            : 'Lng: -'
                    }}
                </div>
                <div
                    id="attendance-map-self"
                    class="mt-3 h-[220px] w-full rounded border"
                ></div>
                <div class="mt-4 flex justify-end">
                    <Button
                        :disabled="form.processing"
                        @click="submitAttendance"
                        >Save</Button
                    >
                </div>
            </div>
            <div
                class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <h3 class="mb-4 text-lg font-semibold">Attendance</h3>
                <div
                    v-if="meeting.attendances && meeting.attendances.length"
                    class="space-y-2"
                >
                    <div
                        v-for="attendance in meeting.attendances"
                        :key="attendance.id"
                        class="flex items-center justify-between rounded border p-3"
                    >
                        <div>
                            <div class="text-sm font-medium">
                                {{ attendance.staff?.StaffNAME || '-' }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ attendance.staff?.StaffEMAIL || '' }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                <span
                                    v-if="
                                        attendance.AttandanceLAT != null &&
                                        attendance.AttandanceLNG != null
                                    "
                                >
                                    Lat: {{ attendance.AttandanceLAT }}, Lng:
                                    {{ attendance.AttandanceLNG }}
                                </span>
                                <span v-else>Lat: -, Lng: -</span>
                            </div>
                        </div>
                        <span
                            :class="`inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium ${statusClass(attendance.AttandanceSTATUS)}`"
                        >
                            {{ statusLabel(attendance.AttandanceSTATUS) }}
                        </span>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    No attendance records.
                </p>
            </div>
        </BaseCard>
    </AppLayout>
</template>
