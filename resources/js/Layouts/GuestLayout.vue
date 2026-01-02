<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear()
</script>

<template>
    <div class="font-space min-h-screen font-sans relative overflow-hidden bg-[#FDFDFC]">
<!--        &lt;!&ndash; Background Image &ndash;&gt;-->
<!--        <img src="/images/aesthetic-background.webp" alt="" class="absolute inset-0 w-full h-full blur-xl  scale-110">-->

        <!-- Form Card -->
        <div class="relative z-10 min-h-screen flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 lg:p-12 border border-gray-100">
                <div class="flex justify-center mb-6">
                    <ApplicationLogo />
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
