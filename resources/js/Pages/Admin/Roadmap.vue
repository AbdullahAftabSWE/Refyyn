<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from "@/Layouts/AdminLayout.vue"

const props = defineProps({
    statuses: Array,
    feedbacksByStatus: Object,
    boards: Array,
    sidebarStats: Object,
})

const showSettings = ref(false)

const toggleStatusVisibility = (status) => {
    router.patch(`/admin/roadmap/status/${status.id}`, {
        show_on_roadmap: !status.show_on_roadmap
    }, {
        preserveScroll: true,
    })
}

const getFeedbacksForStatus = (statusId) => {
    return props.feedbacksByStatus[statusId] || []
}

const getStatusIcon = (status) => {
    const color = status.color
    if (color === 'orange' || color === 'amber' || color === 'yellow') {
        return 'review' // Under Review - hollow circle with border
    } else if (color === 'blue') {
        return 'planned' // Planned - quarter filled circle
    } else if (color === 'purple') {
        return 'progress' // In Progress - filled circle
    } else if (color === 'green' || color === 'emerald') {
        return 'completed' // Completed - filled circle
    }
    return 'default'
}

const statusColorClasses = {
    orange: 'border-orange-500',
    amber: 'border-amber-500',
    yellow: 'border-yellow-500',
    blue: 'border-blue-500',
    purple: 'bg-purple-500',
    green: 'bg-emerald-500',
    emerald: 'bg-emerald-500',
    red: 'border-red-500',
    gray: 'border-gray-500',
}

const viewFeedback = (feedback) => {
    router.visit(`/admin/feedback/${feedback.slug}`)
}
</script>

<template>
    <Head title="Roadmap" />

    <AdminLayout>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="min-h-[100px] flex items-center justify-between px-8 py-6 border-b border-gray-100 flex-shrink-0">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Roadmap</h1>
                </div>

                <button
                    @click="showSettings = !showSettings"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Configure Statuses
                </button>
            </header>

            <!-- Settings Panel -->
            <div v-if="showSettings" class="px-8 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-gray-600">Choose which statuses appear on the public roadmap</span>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        v-for="status in statuses"
                        :key="status.id"
                        @click="toggleStatusVisibility(status)"
                        :class="[
                            'flex items-center gap-2 px-3 py-2 rounded-lg border transition-all',
                            status.show_on_roadmap
                                ? 'bg-white border-gray-300 shadow-sm'
                                : 'bg-gray-100 border-gray-200 opacity-60'
                        ]"
                    >
                        <span
                            class="w-2 h-2 rounded-full"
                            :class="$statusDotColors[status.color]"
                        ></span>
                        <span class="text-sm font-medium text-gray-700">{{ status.name }}</span>
                        <svg
                            v-if="status.show_on_roadmap"
                            class="w-4 h-4 text-green-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg
                            v-else
                            class="w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Roadmap Columns -->
            <div class="flex-1 overflow-x-auto p-8">
                <div class="flex gap-6 h-full">
                    <div
                        v-for="status in statuses.filter(s => s.show_on_roadmap)"
                        :key="status.id"
                        style="min-width: 290px;"
                        class="flex flex-col p-4 border border-gray-100 shadow-sm rounded-xl"
                    >
                        <!-- Column Header -->
                        <div class="flex items-center gap-2 mb-4">
                            <!-- Status Icon -->
                            <div
                                class="w-2 h-2 rounded-full flex-shrink-0"
                                :class="`bg-${status.color}-500`"
                            ></div>

                            <h3 class="font-semibold text-base text-gray-900">{{ status.name }}</h3>
                            <span class="ml-auto bg-gray-100 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-full">
                                {{ getFeedbacksForStatus(status.id).length }}
                            </span>
                        </div>

                        <!-- Feedback Cards -->
                        <div class="flex flex-col gap-3 overflow-y-auto flex-1">
                            <div
                                v-for="feedback in getFeedbacksForStatus(status.id)"
                                :key="feedback.id"
                                @click="viewFeedback(feedback)"
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
                                <p class="text-sm">No items</p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State when no statuses visible -->
                    <div
                        v-if="statuses.filter(s => s.show_on_roadmap).length === 0"
                        class="flex-1 flex items-center justify-center text-gray-400"
                    >
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <p class="text-lg font-medium mb-1">No statuses visible on roadmap</p>
                            <p class="text-sm">Click "Configure Statuses" to select which statuses to display</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
