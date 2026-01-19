<template>
  <AuthBase
    title="Welcome Back 👋"
    description="Login to continue to your dashboard"
  >
    <Head title="Log in" />

    <div
      v-if="status"
      class="mb-4 text-center text-sm font-medium text-green-600"
    >
      {{ status }}
    </div>

    <Form
      v-bind="store.form()"
      :reset-on-success="['password']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-8"
    >
      <div class="space-y-6 p-6 rounded-2xl shadow-sm border bg-white/70 backdrop-blur-md">
        <div class="grid gap-2">
          <Label for="email" class="font-semibold">Email Address</Label>
          <Input
            id="email"
            type="email"
            name="email"
            required
            autofocus
            :tabindex="1"
            autocomplete="email"
            placeholder="email@example.com"
            class="h-11 rounded-xl"
          />
          <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
          <div class="flex items-center justify-between">
            <Label for="password" class="font-semibold">Password</Label>
            <TextLink
              v-if="canResetPassword"
              :href="request()"
              class="text-sm"
              :tabindex="5"
            >
              Forgot password?
            </TextLink>
          </div>
          <Input
            id="password"
            type="password"
            name="password"
            required
            :tabindex="2"
            autocomplete="current-password"
            placeholder="••••••••"
            class="h-11 rounded-xl"
          />
          <InputError :message="errors.password" />
        </div>

        <div class="flex items-center gap-3">
          <Checkbox id="remember" name="remember" :tabindex="3" />
          <Label for="remember" class="text-sm">Remember me</Label>
        </div>

        <Button
          type="submit"
          class="mt-2 w-full h-11 text-base rounded-xl shadow-md hover:shadow-lg transition"
          :tabindex="4"
          :disabled="processing"
          data-test="login-button"
        >
          <LoaderCircle
            v-if="processing"
            class="h-4 w-4 animate-spin"
          />
          <span v-else>Log In</span>
        </Button>
      </div>

      <p class="text-center text-sm text-muted-foreground">
        Don't have an account?
        <TextLink :href="register()" :tabindex="5" class="font-semibold">
          Sign up
        </TextLink>
      </p>
    </Form>
  </AuthBase>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{ status?: string; canResetPassword: boolean }>();
</script>
