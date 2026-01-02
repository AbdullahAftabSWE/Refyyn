<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="flex-1 flex items-center justify-center">
            <div class="w-full max-w-md">

                <h1 class="text-2xl font-bold text-gray-900 mb-2">Thanks for signing up!</h1>
                <p class="text-gray-500 mb-8">Before getting started, could you verify your
                    email address by clicking on the link we just emailed to you? If you
                    didn't receive the email, we will send you another.</p>

                <div
                    class="mb-4 text-sm font-medium text-green-600"
                    v-if="verificationLinkSent"
                >
                    A new verification link has been sent to the email address you
                    provided during registration.
                </div>

                <form @submit.prevent="submit">
                    <div class="flex flex-col items-center justify-between text-center">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Resend Verification Email
                        </PrimaryButton>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-700 mt-4"
                        >Log Out</Link
                        >
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
