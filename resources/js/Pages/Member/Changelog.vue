<script setup>
import { router, Head } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import dayjs from 'dayjs'

const props = defineProps({
    changelogs: Array,
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')
const formatYear = (date) => dayjs(date).format('YYYY')
const formatMonthDay = (date) => dayjs(date).format('MMM D')

// Strip HTML tags and get plain text excerpt
const getExcerpt = (html, maxLength = 200) => {
    const text = html?.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() || ''
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}
</script>

<template>
    <Head title="Changelog" />

    <MemberLayout>
        <!-- Main Content -->
        <div class="max-w-2xl mx-auto w-full px-10 py-8 gap-12">
            <!-- Sidebar -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight mb-2">Changelog</h1>
                <p class="text-gray-500 mb-8">Check out all of the latest updates, improvements, and fixes.</p>
            </div>

            <!-- Changelogs -->
            <div class="space-y-12">
                <!-- Empty State -->
                <div
                    v-if="changelogs.length === 0"
                    class="text-center py-16 text-gray-400"
                >
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <p class="text-lg font-medium mb-1">No changelogs yet</p>
                    <p class="text-sm">Check back later for updates!</p>
                </div>

                <!-- Changelog entries -->
                <article
                    v-for="changelog in changelogs"
                    :key="changelog.id"
                    @click="router.visit(`/changelog/${changelog.slug}`)"
                    class="group relative cursor-pointer"
                >
                    <!-- Timeline line (desktop) -->
                    <div class="hidden lg:block absolute left-[-40px] top-16 bottom-[-48px] w-px bg-gray-200"></div>

                    <!-- Date (desktop) -->
                    <div class="hidden lg:flex absolute left-[-90px] top-1.5 flex-col items-end pr-8 text-sm text-gray-500 w-20">
                        <span class="whitespace-nowrap font-mono text-xs opacity-60">{{ formatYear(changelog.created_at) }}</span>
                        <span class="font-bold text-gray-900">{{ formatMonthDay(changelog.created_at) }}</span>
                    </div>

                    <!-- Card -->
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 group-hover:border-gray-300">
                        <!-- Image -->
                        <div v-if="changelog.image" class="relative aspect-video w-full overflow-hidden bg-gray-100">
                            <img
                                :src="`/storage/${changelog.image}`"
                                :alt="changelog.title"
                                class="object-cover w-full h-full transform group-hover:scale-105 transition-transform duration-500"
                            />
                            <!-- Mobile date badge -->
                            <div class="lg:hidden absolute bottom-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-medium shadow-sm border border-gray-200">
                                {{ formatDate(changelog.created_at) }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-2 md:p-4">
                            <h2 class="text-xl font-bold mb-2 text-gray-900 tracking-tight group-hover:text-blue-600 transition-colors">
                                {{ changelog.title }}
                            </h2>
                            <p class="text-base leading-relaxed text-gray-600">
                                {{ getExcerpt(changelog.description) }}
                            </p>

                            <!-- Author Footer -->
                            <div class="flex items-center gap-3 mt-4 pt-6 border-t border-gray-100">
                                <img
                                    v-if="changelog.author?.avatar"
                                    :src="changelog.author.avatar"
                                    :alt="changelog.author.name"
                                    class="w-10 h-10 rounded-full ring-2 ring-white"
                                />
                                <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600 ring-2 ring-white">
                                    {{ changelog.author?.name?.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ changelog.author?.name }}</p>
                                    <p class="text-xs text-gray-500">Published on {{ formatDate(changelog.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </MemberLayout>
</template>
