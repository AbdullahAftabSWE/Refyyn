<script setup>
import {computed, ref} from 'vue'
import {router, usePage} from '@inertiajs/vue3';
import {Head} from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    feedbacks: Array,
    boards: Array,
    statuses: Array,
    sidebarStats: {
        type: Object,
        default: () => ({ totalCount: 0, unreadCount: 0 })
    },
    currentFilter: String,
    currentBoardId: Number,
})

const page = usePage()
const appName = computed(() => page.props.app?.name || 'Feedback')

const pageTitle = computed(() => {
    if (props.currentFilter === 'unread') return 'Unread Requests';
    if (props.currentBoardId) {
        const board = props.boards.find(b => b.id === props.currentBoardId);
        return board ? board.name : 'All Feedback';
    }
    return 'All Feedback';
})

const selectedBoard = ref(null)

// Search and filter state
const searchQuery = ref('')
const selectedStatuses = ref([])
const sortBy = ref('latest') // 'latest', 'votes'
const showStatusDropdown = ref(false)

// Get unique statuses from feedbacks
const availableStatuses = computed(() => {
    const statusMap = new Map()
    props.feedbacks.forEach(f => {
        if (f.status && !statusMap.has(f.status.id)) {
            statusMap.set(f.status.id, f.status)
        }
    })
    return Array.from(statusMap.values())
})

const toggleStatus = (statusId) => {
    const index = selectedStatuses.value.indexOf(statusId)
    if (index === -1) {
        selectedStatuses.value.push(statusId)
    } else {
        selectedStatuses.value.splice(index, 1)
    }
}

const isStatusSelected = (statusId) => {
    return selectedStatuses.value.includes(statusId)
}

const filteredFeedbacks = computed(() => {
    let result = props.feedbacks

    // Filter by board
    if (selectedBoard.value) {
        result = result.filter(f => f.board?.id === selectedBoard.value)
    }

    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim()
        result = result.filter(f =>
            f.title?.toLowerCase().includes(query) ||
            f.description?.toLowerCase().includes(query)
        )
    }

    // Filter by selected statuses
    if (selectedStatuses.value.length > 0) {
        result = result.filter(f => selectedStatuses.value.includes(f.status?.id))
    }

    // Sort
    result = [...result].sort((a, b) => {
        switch (sortBy.value) {
            case 'latest':
                return new Date(b.created_at) - new Date(a.created_at)
            case 'votes':
                return (b.upvotes_count || 0) - (a.upvotes_count || 0)
            default:
                return 0
        }
    })

    return result
})

const selectedItem = ref(null)

// Track read status locally for immediate UI updates
const readFeedbackIds = ref(new Set(
    props.feedbacks.filter(f => f.is_read).map(f => f.id)
))

const isUnread = (feedback) => {
    return !readFeedbackIds.value.has(feedback.id)
}

const selectItem = (item) => {
    // Mark as read locally for immediate UI update
    if (!readFeedbackIds.value.has(item.id)) {
        readFeedbackIds.value.add(item.id)

        // Send to server
        fetch(`/admin/feedback/${item.id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        })
    }

    router.visit(`/admin/feedback/${item.slug}`)
}
</script>

<template>
    <Head title="Dashboard"/>

    <AdminLayout>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="min-h-[100px] flex items-center justify-between px-8 py-6 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ pageTitle }}</h1>
                    <span class="text-sm text-gray-400">{{ filteredFeedbacks.length }} Requests</span>
                </div>
            </header>

            <!-- Filter Bar -->
            <div class="flex items-center justify-between px-8 py-4 border-b border-gray-100 bg-gray-50/50 flex-shrink-0">
                <!-- Left side: Search and Status Filter -->
                <div class="flex items-center gap-4">
                    <!-- Search Input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search requests..."
                            class="w-64 pl-10 pr-4 py-2 text-xs text-gray-700 placeholder:text-gray-500 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-white"
                        >
                        <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="2">
                            <use href="/images/icons.svg#search" />
                        </svg>
                    </div>

                    <!-- Status Filter Dropdown -->
                    <div class="relative">
                        <button
                            @click="showStatusDropdown = !showStatusDropdown"
                            class="flex items-center gap-2 px-3 py-2 text-xs border border-gray-200 rounded-lg hover:bg-white transition-colors bg-white"
                        >
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span class="text-gray-500">Status</span>
                            <span v-if="selectedStatuses.length > 0" class="bg-gray-900 text-white text-xs px-1.5 py-0.5 rounded-full">
                                {{ selectedStatuses.length }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="showStatusDropdown"
                            class="absolute top-full left-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-30"
                        >
                            <div class="p-2">
                                <button
                                    v-for="status in availableStatuses"
                                    :key="status.id"
                                    @click="toggleStatus(status.id)"
                                    :class="[
                                        'w-full flex items-center gap-2 px-3 py-2 text-sm rounded-md transition-colors',
                                        isStatusSelected(status.id) ? 'bg-gray-100' : 'hover:bg-gray-50'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'w-4 h-4 rounded border-2 flex items-center justify-center',
                                            isStatusSelected(status.id) ? 'bg-gray-900 border-gray-900' : 'border-gray-300'
                                        ]"
                                    >
                                        <svg v-if="isStatusSelected(status.id)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="w-2 h-2 rounded-full" :class="$statusDotColors[status.color]"></span>
                                    <span class="text-gray-700">{{ status.name }}</span>
                                </button>
                            </div>
                            <div v-if="selectedStatuses.length > 0" class="border-t border-gray-100 p-2">
                                <button
                                    @click="selectedStatuses = []; showStatusDropdown = false"
                                    class="w-full text-center text-sm text-gray-500 hover:text-gray-700 py-1"
                                >
                                    Clear all
                                </button>
                            </div>
                        </div>

                        <!-- Click outside to close -->
                        <div v-if="showStatusDropdown" class="fixed inset-0 z-20" @click="showStatusDropdown = false"></div>
                    </div>
                </div>

                <!-- Right side: Sort Buttons -->
                <div class="flex items-center gap-1 p-1 border border-gray-200 rounded-lg">
                    <button
                        @click="sortBy = 'latest'"
                        :class="[
                            'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                            sortBy === 'latest' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                        ]"
                    >
                        Latest
                    </button>
                    <button
                        @click="sortBy = 'votes'"
                        :class="[
                            'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                            sortBy === 'votes' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                        ]"
                    >
                        Upvoted
                    </button>
                </div>
            </div>

            <div class="overflow-auto flex-1">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-white font-bold">
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="w-[10%] py-3 px-6 text-left text-xs text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="w-[45%] py-3 px-6 text-left text-xs text-gray-500 uppercase tracking-wider">
                            Feedback
                        </th>
                        <th class="w-[20%] py-3 px-2 text-left text-xs text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="w-[20%] py-3 px-2 text-left text-xs text-gray-500 uppercase tracking-wider">
                            Reported By
                        </th>
                        <th class="w-[5%] py-3 px-2 w-8"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="feedback in filteredFeedbacks"
                        :key="feedback.id"
                        @click="selectItem(feedback)"
                        :class="[
                            'border-b border-gray-100 cursor-pointer transition-colors group',
                            selectedItem?.id === feedback.id ? 'bg-blue-50' : 'hover:bg-gray-50'
                        ]"
                    >
                        <td class="py-5 px-6 relative">
                            <!-- Unread indicator - vertical blue line on left -->
                            <div
                                v-if="isUnread(feedback)"
                                class="absolute left-0 top-0 bottom-0 w-0.5 bg-blue-500"
                            ></div>
                            <div class="text-xs text-gray-500">
                                {{ new Date(feedback.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                            </div>
                        </td>

                        <td class="py-5 px-6">
                            <div class="flex flex-col gap-1">
                                <div :class="[
                                    'text-sm font-medium transition-colors group-hover:text-blue-600',
                                    isUnread(feedback) ? 'text-blue-600' : 'text-gray-900'
                                ]">{{ feedback.title }}</div>
                                <div class="flex items-center gap-4 text-xs text-gray-400 mt-1">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-5 h-5" stroke-width="1.5">
                                            <use href="/images/icons.svg#upvote" />
                                        </svg>
                                        {{ feedback.upvotes_count }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" stroke-width="1.5">
                                            <use href="/images/icons.svg#comment" />
                                        </svg>
                                        {{ feedback.comments_count }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-2">
                            <span
                                :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border-[0.25px]', $statusClasses[feedback.status.color]]">
                                <span class="w-1.5 h-1.5 bg-current rounded-full"></span>
                                {{ feedback.status.name }}
                            </span>
                        </td>

                        <td class="py-4 px-2">
                            <div class="flex items-center gap-3">
                                <img :src="feedback.author.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(feedback.author.name)}&background=random`"
                                     :alt="feedback.author.name"
                                     class="w-9 h-9 rounded-full object-cover"/>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ feedback.author.name }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Empty state -->
                <div v-if="filteredFeedbacks.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-500">
                    <svg class="w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm">No feedback found</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
