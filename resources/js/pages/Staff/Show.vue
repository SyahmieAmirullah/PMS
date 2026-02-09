<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/date';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ArrowLeft, Mail, Phone } from 'lucide-vue-next';

const props = defineProps({
    staff: { type: Object, required: true },
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Staff', href: '/staff' },
    { title: props.staff.StaffNAME, href: `/staff/${props.staff.id}` },
];

const goBack = () => {
    router.visit('/staff');
};
</script>

<template>
    <Head :title="`Staff - ${staff.StaffNAME}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BaseCard>
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/staff" class="no-underline">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back
                        </Button>
                    </Link>
                    <div>
                        <BaseTitle size="2xl">{{ staff.StaffNAME }}</BaseTitle>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <Badge
                                v-for="role in staff.roles"
                                :key="role.id"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ role.RoleTYPE }}
                            </Badge>
                            <span
                                v-if="!staff.roles || staff.roles.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                No roles
                            </span>
                        </div>
                    </div>
                </div>
                <Button
                    v-if="staff?.id"
                    variant="outline"
                    @click="router.get(`/staff/${staff.id}/edit`)"
                >
                    Edit
                </Button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm font-medium text-muted-foreground">Email</p>
                    <div class="mt-2 flex items-center gap-2">
                        <Mail class="h-4 w-4 text-muted-foreground" />
                        <span>{{ staff.StaffEMAIL || '-' }}</span>
                    </div>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm font-medium text-muted-foreground">Phone</p>
                    <div class="mt-2 flex items-center gap-2">
                        <Phone class="h-4 w-4 text-muted-foreground" />
                        <span>{{ staff.StaffPHONE || '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Projects</h3>
                    <span class="text-sm text-muted-foreground">
                        Total: {{ staff.projects_count ?? (staff.projects?.length ?? 0) }}
                    </span>
                </div>

                <Table class="border-y">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Project</TableHead>
                            <TableHead>Client</TableHead>
                            <TableHead>Start</TableHead>
                            <TableHead>Due</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="project in staff.projects" :key="project.id">
                            <TableCell class="font-medium">{{ project.ProjectNAME }}</TableCell>
                            <TableCell>{{ project.ClientNAME || '-' }}</TableCell>
                            <TableCell>
                                {{ project.pivot?.ProjectSTART ? formatDate(project.pivot.ProjectSTART) : '-' }}
                            </TableCell>
                            <TableCell>
                                {{ project.pivot?.ProjectDUE ? formatDate(project.pivot.ProjectDUE) : '-' }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!staff.projects || staff.projects.length === 0">
                            <TableCell colspan="4" class="py-6 text-center text-muted-foreground">
                                No projects assigned
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </BaseCard>
    </AppLayout>
</template>
