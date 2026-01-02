<script setup>
import { Head } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'

const props = defineProps({
    statuses: Array,
    feedbacksByStatus: Object,
})

const getFeedbacksForStatus = (statusId) => {
    return props.feedbacksByStatus[statusId] || []
}
</script>

<template>
    <Head title="Roadmap" />

    <MemberLayout>
        <div class="h-full flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="max-w-7xl mx-auto w-full px-10 pt-8 pb-6 flex-shrink-0">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Roadmap</h1>
                <p class="text-gray-500">See what we're working on and what's coming next.</p>
            </div>

            <!-- Roadmap Columns -->
            <div class="flex-1 overflow-x-auto w-full max-w-7xl mx-auto px-10 pb-8">
                <div class="flex gap-6 h-full">
                    <div
                        v-for="status in statuses"
                        :key="status.id"
                        style="min-width: 290px;"
                        class="flex flex-col p-4 border border-gray-100 shadow-sm rounded-xl h-full"
                    >

                        <!-- Column Header -->
                        <div class="flex items-center gap-2 mb-4 flex-shrink-0">
                        <!-- Status Icon -->
                        <div
                            class="w-2 h-2 rounded-full flex-shrink-0"
                            :class="$statusDotColors[status.color]"
                        ></div>

                        <h3 class="font-semibold text-lg text-gray-900">{{ status.name }}</h3>
                        <span class="ml-auto bg-gray-100 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ getFeedbacksForStatus(status.id).length }}
                        </span>
                    </div>

                    <!-- Feedback Cards -->
                    <div class="flex flex-col gap-3 overflow-y-auto flex-1">
                        <div
                            v-for="feedback in getFeedbacksForStatus(status.id)"
                            :key="feedback.id"
                            class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-gray-300 transition-all cursor-pointer group"
                        >
                            <h4 class="font-medium text-gray-900 mb-3 text-sm leading-snug group-hover:text-blue-600 transition-colors">
                                {{ feedback.title }}
                            </h4>
                            <div class="flex items-center gap-4 text-gray-500 text-sm">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M 16.881 16 H 7.119 a 1 1 0 0 1 -0.772 -1.636 l 4.881 -5.927 a 1 1 0 0 1 1.544 0 l 4.88 5.927 a 1 1 0 0 1 -0.77 1.636 Z"/>
                                    </svg>
                                    <span class="text-xs">{{ feedback.upvotes_count }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span class="text-xs">{{ feedback.comments_count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="getFeedbacksForStatus(status.id).length === 0"
                            class="text-center py-8 text-gray-400"
                        >
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm">No items yet</p>
                        </div>
                    </div>
                    </div>

                    <!-- Empty State when no statuses visible -->
                    <div
                        v-if="statuses.length === 0"
                        class="flex-1 flex items-center justify-center text-gray-400"
                    >
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <p class="text-lg font-medium mb-1">Roadmap coming soon</p>
                            <p class="text-sm">Check back later to see what's in the works!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
