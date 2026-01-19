<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
  staff: Object,
});

const form = useForm({
  StaffNAME: props.staff.StaffNAME,
  StaffEMAIL: props.staff.StaffEMAIL,
  StaffPHONE: props.staff.StaffPHONE || '',
  StaffPASSWORD: '',
  StaffPASSWORD_confirmation: '',
});

const submit = () => {
  form.put(route('staff.update', props.staff.StaffID), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Edit Staff" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-4">
        <Link :href="route('staff.index')">
          <Button variant="ghost" size="sm">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back
          </Button>
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit Staff Member
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>Edit Staff Information</CardTitle>
            <CardDescription>Update staff member details</CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Name -->
              <div class="space-y-2">
                <Label for="name">Full Name *</Label>
                <Input
                  id="name"
                  v-model="form.StaffNAME"
                  type="text"
                  required
                  autofocus
                  :class="{ 'border-red-500': form.errors.StaffNAME }"
                />
                <p v-if="form.errors.StaffNAME" class="text-sm text-red-500">
                  {{ form.errors.StaffNAME }}
                </p>
              </div>

              <!-- Email -->
              <div class="space-y-2">
                <Label for="email">Email Address *</Label>
                <Input
                  id="email"
                  v-model="form.StaffEMAIL"
                  type="email"
                  required
                  :class="{ 'border-red-500': form.errors.StaffEMAIL }"
                />
                <p v-if="form.errors.StaffEMAIL" class="text-sm text-red-500">
                  {{ form.errors.StaffEMAIL }}
                </p>
              </div>

              <!-- Phone -->
              <div class="space-y-2">
                <Label for="phone">Phone Number</Label>
                <Input
                  id="phone"
                  v-model="form.StaffPHONE"
                  type="tel"
                  :class="{ 'border-red-500': form.errors.StaffPHONE }"
                />
                <p v-if="form.errors.StaffPHONE" class="text-sm text-red-500">
                  {{ form.errors.StaffPHONE }}
                </p>
              </div>

              <!-- Divider -->
              <div class="border-t pt-6">
                <p class="text-sm text-gray-600 mb-4">
                  Leave password fields empty to keep current password
                </p>
              </div>

              <!-- Password -->
              <div class="space-y-2">
                <Label for="password">New Password</Label>
                <Input
                  id="password"
                  v-model="form.StaffPASSWORD"
                  type="password"
                  :class="{ 'border-red-500': form.errors.StaffPASSWORD }"
                />
                <p v-if="form.errors.StaffPASSWORD" class="text-sm text-red-500">
                  {{ form.errors.StaffPASSWORD }}
                </p>
              </div>

              <!-- Confirm Password -->
              <div class="space-y-2">
                <Label for="password_confirmation">Confirm New Password</Label>
                <Input
                  id="password_confirmation"
                  v-model="form.StaffPASSWORD_confirmation"
                  type="password"
                />
              </div>

              <!-- Actions -->
              <div class="flex justify-end gap-4 pt-4">
                <Link :href="route('staff.index')">
                  <Button type="button" variant="outline">
                    Cancel
                  </Button>
                </Link>
                <Button type="submit" :disabled="form.processing">
                  {{ form.processing ? 'Updating...' : 'Update Staff' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>