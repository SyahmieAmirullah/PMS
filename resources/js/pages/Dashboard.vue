<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { FolderKanban, CheckCircle2, PauseCircle, PlayCircle, CalendarClock } from 'lucide-vue-next';
import { formatDate } from '@/lib/date';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const props = defineProps({
    projects: { type: Array, default: () => [] },
    meetings: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const page = usePage();
const auth = computed(() => (page.props.auth as any) || {});
const isStaff = computed(() => auth.value.guard === 'staff');

const statusBadge = (status: string) => {
    const map: Record<string, string> = {
        in_progress: 'bg-green-100 text-green-700 border-green-200',
        completed: 'bg-blue-100 text-blue-700 border-blue-200',
        on_hold: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        planning: 'bg-gray-100 text-gray-700 border-gray-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
    };
    return map[status] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        in_progress: 'In Progress',
        completed: 'Completed',
        on_hold: 'On Hold',
        planning: 'Planning',
        cancelled: 'Cancelled',
    };
    return map[status] || status;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <BaseCard>
                <div class="flex items-center justify-between">
                    <div>
                        <BaseTitle size="2xl">
                            {{ isStaff ? 'My Assigned Projects' : 'Projects Overview' }}
                        </BaseTitle>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ isStaff ? 'Projects assigned to you' : 'Latest projects in the system' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <FolderKanban class="h-4 w-4" />
                        {{ props.projects.length }} projects
                    </div>
                </div>
            </BaseCard>

            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">Active</p>
                        <PlayCircle class="h-5 w-5 text-green-600" />
                    </div>
                    <h3 class="mt-2 text-2xl font-bold text-green-600">{{ stats?.active ?? 0 }}</h3>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">Completed</p>
                        <CheckCircle2 class="h-5 w-5 text-blue-600" />
                    </div>
                    <h3 class="mt-2 text-2xl font-bold text-blue-600">{{ stats?.completed ?? 0 }}</h3>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">On Hold</p>
                        <PauseCircle class="h-5 w-5 text-yellow-600" />
                    </div>
                    <h3 class="mt-2 text-2xl font-bold text-yellow-600">{{ stats?.onHold ?? 0 }}</h3>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Assigned Projects</h3>
                    <span class="text-sm text-muted-foreground">Latest 10</span>
                </div>
                <div v-if="props.projects.length" class="grid gap-3 md:grid-cols-2">
                    <div
                        v-for="project in props.projects"
                        :key="project.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ project.ProjectNAME }}</div>
                            <Badge :class="statusBadge(project.ProjectSTATUS)">
                                {{ statusLabel(project.ProjectSTATUS) }}
                            </Badge>
                        </div>
                        <div class="mt-2 text-sm text-muted-foreground">
                            Client: {{ project.ClientNAME || '-' }}
                        </div>
                        <div class="mt-3 flex items-center gap-4 text-xs text-muted-foreground">
                            <span>Tasks: {{ project.tasks_count ?? 0 }}</span>
                            <span>Phases: {{ project.phases_count ?? 0 }}</span>
                            <span>Feedback: {{ project.feedback_count ?? 0 }}</span>
                        </div>
                        <div class="mt-3">
                            <Link
                                :href="`/projects/${project.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                View Project
                            </Link>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">No projects assigned.</p>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <CalendarClock class="h-5 w-5 text-indigo-600" />
                        <h3 class="text-lg font-semibold">Upcoming Meetings</h3>
                    </div>
                    <span class="text-sm text-muted-foreground">Next 5</span>
                </div>
                <div v-if="props.meetings.length" class="grid gap-3 md:grid-cols-2">
                    <div
                        v-for="meeting in props.meetings"
                        :key="meeting.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ meeting.MeetingTITLE }}</div>
                            <span class="text-xs text-muted-foreground">
                                {{ formatDate(meeting.MeetingDATE) }} {{ meeting.MeetingTIME }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-muted-foreground">
                            Project: {{ meeting.project?.ProjectNAME || '-' }}
                        </div>
                        <div class="mt-3">
                            <Link
                                :href="`/meetings/${meeting.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                View Meeting
                            </Link>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">No upcoming meetings.</p>
            </div>
        </div>
    </AppLayout>
</template>
