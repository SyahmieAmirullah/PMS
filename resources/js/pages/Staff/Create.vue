<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import BaseCard from '@/components/ui/card/BaseCard.vue';
import BaseTitle from '@/components/ui/card/BaseTitle.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
//import { useI18n } from 'vue-i18n';

import Textarea from '@/components/ui/textarea/Textarea.vue';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';

//const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Staff', href: '/staff' },
  { title: 'Create Staff', href: '/staff/create' }
];

const form = ref({
  StaffNAME: '',
  StaffPHONE: '',
  StaffEMAIL: '',
  StaffPASSWORD: '',
  roles: [
    { RoleTYPE: '', RoleDESC: '', RolePRO: '' }
  ] as Array<{ RoleTYPE: string; RoleDESC: string; RolePRO: string }>,
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const validateForm = () => {
  errors.value = {};
  let isValid = true;

  if (!form.value.StaffNAME.trim()) {
    errors.value.StaffNAME = 'Staff name is required';
    isValid = false;
  }

  if (!form.value.StaffEMAIL.trim()) {
    errors.value.StaffEMAIL = 'Email is required';
    isValid = false;
  } else if (!isValidEmail(form.value.StaffEMAIL)) {
    errors.value.StaffEMAIL = 'Please enter a valid email address';
    isValid = false;
  }

  if (!form.value.StaffPASSWORD.trim()) {
    errors.value.StaffPASSWORD = 'Password is required';
    isValid = false;
  } else if (form.value.StaffPASSWORD.length < 6) {
    errors.value.StaffPASSWORD = 'Password must be at least 6 characters';
    isValid = false;
  }

  return isValid;
};

const isValidEmail = (email: string) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

const addRole = () => {
  form.value.roles.push({ RoleTYPE: '', RoleDESC: '', RolePRO: '' });
};

const removeRole = (index: number) => {
  if (form.value.roles.length > 1) {
    form.value.roles.splice(index, 1);
  } else {
    toast.error('At least one role entry is required');
  }
};

const submitForm = () => {
  if (!validateForm()) {
    toast.error('Please check your inputs');
    return;
  }

  isSubmitting.value = true;

  router.post('/staff/create', form.value, {
    onSuccess: () => {
      toast.success('Staff member created successfully!');
      isSubmitting.value = false;
    },
    onError: (err) => {
      console.log(err);
      toast.error('Failed to create staff member');
      isSubmitting.value = false;
    },
  });
};

const goBack = () => {
  router.visit('/staff');
};
</script>

<template>
  <Head title="Create Staff" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <BaseCard>
      <div class="mb-6 flex items-center justify-between">
        <BaseTitle size="2xl">Create New Staff Member</BaseTitle>
        <Button variant="outline" @click="goBack" class="flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" />
          Back to Staff
        </Button>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Personal Information Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="mb-4 text-lg font-semibold">Personal Information</h3>
          
          <div class="space-y-4">
            <div class="flex flex-col space-y-1">
              <Label>Full Name <span class="text-red-500">*</span></Label>
              <Input
                v-model="form.StaffNAME"
                placeholder="Enter full name"
                :class="errors.StaffNAME ? 'border-red-500' : ''"
              />
              <span v-if="errors.StaffNAME" class="text-xs text-red-500">
                {{ errors.StaffNAME }}
              </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col space-y-1">
                <Label>Phone Number</Label>
                <Input
                  v-model="form.StaffPHONE"
                  placeholder="Enter phone number"
                />
              </div>

              <div class="flex flex-col space-y-1">
                <Label>Email <span class="text-red-500">*</span></Label>
                <Input
                  v-model="form.StaffEMAIL"
                  type="email"
                  placeholder="Enter email address"
                  :class="errors.StaffEMAIL ? 'border-red-500' : ''"
                />
                <span v-if="errors.StaffEMAIL" class="text-xs text-red-500">
                  {{ errors.StaffEMAIL }}
                </span>
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <Label>Password <span class="text-red-500">*</span></Label>
              <Input
                v-model="form.StaffPASSWORD"
                type="password"
                placeholder="Enter password (min. 6 characters)"
                :class="errors.StaffPASSWORD ? 'border-red-500' : ''"
              />
              <span v-if="errors.StaffPASSWORD" class="text-xs text-red-500">
                {{ errors.StaffPASSWORD }}
              </span>
            </div>
          </div>
        </div>

        <!-- Roles Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Roles & Responsibilities</h3>
            <Button type="button" size="sm" variant="outline" @click="addRole">
              <Plus class="h-4 w-4 mr-2" />
              Add Role
            </Button>
          </div>
          
          <div class="space-y-4">
            <div
              v-for="(role, index) in form.roles"
              :key="index"
              class="rounded-lg border p-4 bg-gray-50"
            >
              <div class="mb-3 flex items-center justify-between">
                <h4 class="text-sm font-medium">Role {{ index + 1 }}</h4>
                <Button
                  v-if="form.roles.length > 1"
                  type="button"
                  size="sm"
                  variant="ghost"
                  @click="removeRole(index)"
                  class="text-red-600 hover:text-red-700 hover:bg-red-50"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>

              <div class="space-y-3">
                <div class="flex flex-col space-y-1">
                  <Label>Role Type</Label>
                  <Input
                    v-model="role.RoleTYPE"
                    placeholder="e.g., Developer, Manager, Designer"
                  />
                </div>

                <div class="flex flex-col space-y-1">
                  <Label>Role Description</Label>
                  <Textarea
                    v-model="role.RoleDESC"
                    placeholder="Describe the role and responsibilities"
                    rows="2"
                  />
                </div>

                <div class="flex flex-col space-y-1">
                  <Label>Professional Level/Status</Label>
                  <Input
                    v-model="role.RolePRO"
                    placeholder="e.g., Senior, Junior, Lead"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting" class="flex items-center gap-2">
            <Save class="h-4 w-4" />
            {{ isSubmitting ? 'Creating...' : 'Create Staff Member' }}
          </Button>
        </div>
      </form>
    </BaseCard>
  </AppLayout>
</template>