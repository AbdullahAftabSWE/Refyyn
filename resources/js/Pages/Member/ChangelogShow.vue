<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthModal from '@/Pages/Auth/AuthModal.vue'
import Avatar from "@/Components/Avatar.vue"
import dayjs from 'dayjs'

const props = defineProps({
    changelog: Object,
})

const showAuthModal = ref(false)
const authMode = ref('login')

const formatDateLong = (date) => dayjs(date).format('MMMM D, YYYY')

const goBack = () => {
    router.visit('/changelog')
}
</script>

<template>
    <Head :title="changelog.title" />

    <div class="min-h-screen bg-white font-['Space_Grotesk'] flex flex-col">
        <!-- Navbar -->
        <nav class="border-b border-gray-200 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        R
                    </div>
                    <span class="text-xl font-semibold text-gray-900">Refyyn</span>
                </div>

                <!-- Nav Links -->
                <div class="flex items-center gap-8">
                    <Link href="/requests" class="text-gray-500 font-medium hover:text-gray-900 transition-colors">Feedback</Link>
                    <Link href="/roadmap" class="text-gray-500 font-medium hover:text-gray-900 transition-colors">Roadmap</Link>
                    <Link href="/changelog" class="text-gray-900 font-medium hover:text-gray-600 transition-colors">Changelog</Link>
                </div>

                <!-- Auth Buttons -->
                <div v-if="!$page.props.auth.user" class="flex items-center gap-3">
                    <button
                        @click="showAuthModal = true; authMode = 'login'"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
                        Log In
                    </button>
                    <button
                        @click="showAuthModal = true; authMode = 'signup'"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Sign Up
                    </button>
                </div>
                <div v-else class="flex items-center gap-3 px-2">
                    <Avatar :name="$page.props.auth.user?.name" size="4"/>
                    <span class="text-xs">{{ $page.props.auth.user?.name }}</span>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <div class="flex-1">
            <div class="max-w-4xl mx-auto px-8 py-8">
                <!-- Back button -->
                <button
                    @click="goBack"
                    class="text-sm text-gray-600 hover:text-gray-900 mb-6 inline-flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Changelog
                </button>

                <!-- Title -->
                <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ changelog.title }}</h1>

                <!-- Author info -->
                <div class="flex items-center gap-4 mb-8">
                    <img
                        v-if="changelog.author?.avatar"
                        :src="changelog.author.avatar"
                        :alt="changelog.author.name"
                        class="w-12 h-12 rounded-full object-cover"
                    />
                    <div v-else class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                        {{ changelog.author?.name?.charAt(0) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ changelog.author?.name }}</p>
                        <p class="text-sm text-gray-500">{{ formatDateLong(changelog.created_at) }}</p>
                    </div>
                </div>

                <!-- Hero image -->
                <div v-if="changelog.image" class="mb-8 rounded-lg overflow-hidden">
                    <img :src="`/storage/${changelog.image}`" :alt="changelog.title" class="w-full h-auto rounded-lg" />
                </div>

                <!-- Rich text content -->
                <article class="prose prose-gray max-w-none" v-html="changelog.description"></article>
            </div>
        </div>

        <!-- Auth Modal -->
        <AuthModal
            :show="showAuthModal"
            :mode="authMode"
            @update:show="showAuthModal = $event"
            @update:mode="authMode = $event"
        />
    </div>
</template>
