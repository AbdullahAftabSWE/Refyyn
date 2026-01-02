<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import MemberLayout from '@/Layouts/MemberLayout.vue'
import FeedbackModal from '@/Pages/Member/FeedbackModal.vue'
import { useAuthModal } from '@/Composables/useAuthModal'

const props = defineProps({
    boards: Array,
    statuses: Array,
    feedbacks: Array,
    upvotedIds: Array,
})

const { requireAuth } = useAuthModal()

const showFeedbackModal = ref(false)

// Search and filter state
const searchQuery = ref('')
const selectedBoard = ref(null)
const selectedStatuses = ref([])
const sortBy = ref('latest') // 'latest', 'votes'
const showStatusDropdown = ref(false)

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

// Track upvoted items locally (initialized from server)
const upvotedItems = ref(new Set(props.upvotedIds || []))

// Track upvote counts locally for immediate UI updates
const upvoteCounts = ref(
    Object.fromEntries(props.feedbacks.map(f => [f.id, f.upvotes_count || 0]))
)

// Track which items are currently being upvoted
const upvotingItems = ref(new Set())

const toggleVote = async (feedbackId) => {
    if (!requireAuth()) return
    if (upvotingItems.value.has(feedbackId)) return
    upvotingItems.value.add(feedbackId)

    try {
        const response = await fetch(`/feedback/${feedbackId}/upvote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        })

        const data = await response.json()

        if (data.success) {
            if (data.upvoted) {
                upvotedItems.value.add(feedbackId)
            } else {
                upvotedItems.value.delete(feedbackId)
            }
            upvoteCounts.value[feedbackId] = data.upvoteCount
        }
    } catch (error) {
        console.error('Failed to toggle upvote:', error)
    } finally {
        upvotingItems.value.delete(feedbackId)
    }
}

const isUpvoted = (feedbackId) => {
    return upvotedItems.value.has(feedbackId)
}

const getUpvoteCount = (feedbackId) => {
    return upvoteCounts.value[feedbackId] ?? 0
}

const shortenDescription = (text, maxLength = 110) => {
    if (!text) return ''
    if (text.length <= maxLength) return text
    return text.substring(0, maxLength) + '...'
}

const selectBoard = (boardId) => {
    selectedBoard.value = selectedBoard.value === boardId ? null : boardId
}

const openFeedbackModal = () => {
    if (!requireAuth()) return
    showFeedbackModal.value = true
}
</script>

<template>
    <Head title="Feedback" />

    <MemberLayout>
        <div class="max-w-7xl mx-auto flex gap-10 p-6">
            <!-- Left Sidebar -->
            <aside class="w-64 flex-shrink-0 self-start bg-white">
                <div class="px-4">
                    <!-- Submit Button -->
                    <button
                        @click="openFeedbackModal"
                        class="w-full flex justify-center text-center gap-2 px-3 py-2 mt-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:scale-105 duration-200 mb-6"
                    >
                        <svg class="w-5 h-5" stroke-width="1.5">
                            <use href="/images/icons.svg#plus" />
                        </svg>
                        Submit Idea
                    </button>

                    <!-- Boards Section -->
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">BOARDS</h3>
                    <div class="space-y-2">
                        <button
                            @click="selectedBoard = null"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors group',
                                !selectedBoard ? 'text-gray-900 bg-white border border-gray-200 shadow-sm' : 'text-gray-700 hover:bg-gray-100 border border-transparent'
                            ]"
                        >
                            <span
                                class="w-2 h-2 rounded-full ring-2 group-hover:ring-4 ring-opacity-20 transition-all bg-black ring-black"
                            ></span>
                            <span class="flex-1 text-left">All Requests</span>
                            <span class="right text-xs text-gray-600">{{ feedbacks.length }}</span>
                        </button>
                        <button
                            v-for="board in boards"
                            :key="board.id"
                            @click="selectBoard(board.id)"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors group',
                                selectedBoard === board.id ? 'text-gray-900 bg-white border border-gray-200 shadow-sm' : 'text-gray-700 hover:bg-gray-100 border border-transparent'
                            ]"
                        >
                            <span
                                class="w-2 h-2 rounded-full ring-2 group-hover:ring-4 ring-opacity-20 transition-all"
                                :class="$boardColorClasses[board.color] || 'bg-gray-500 ring-gray-500'"
                            ></span>
                            <span class="flex-1 text-left">{{ board.name }}</span>
                            <span class="right text-xs text-gray-600">{{ board.feedback_count }}</span>
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ selectedBoard ? boards.find(b => b.id === selectedBoard)?.name : 'All Requests' }}
                        </h1>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search posts..."
                                    class="h-9 pl-10 pr-4 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="2">
                                    <use href="/images/icons.svg#search" />
                                </svg>
                            </div>

                            <!-- Status Filter -->
                            <div class="relative">
                                <button
                                    @click="showStatusDropdown = !showStatusDropdown"
                                    class="h-9 w-9 flex items-center justify-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"                                >
                                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div
                                    v-if="showStatusDropdown"
                                    class="absolute top-full right-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-30"
                                >
                                    <div class="p-2">
                                        <button
                                            v-for="status in statuses"
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

                            <!-- Sort Buttons -->
                            <div class="flex items-center gap-1 p-1 border border-gray-200 rounded-lg">
                                <button
                                    @click="sortBy = 'latest'"
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                                        sortBy === 'latest' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                                    ]"
                                >
                                    Latest
                                </button>
                                <button
                                    @click="sortBy = 'votes'"
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                                        sortBy === 'votes' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                                    ]"
                                >
                                    Upvoted
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Vote on existing requests or suggest a new feature.</p>
                </div>

                <!-- Feedback List -->
                <div class="space-y-4">
                    <div
                        v-for="feedback in filteredFeedbacks"
                        :key="feedback.id"
                        @click="router.visit(`/feedback/${feedback.slug}`)"
                        class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:shadow-sm transition-all cursor-pointer bg-white"
                    >
                        <div class="flex flex-col items-center gap-1 self-center">
                            <button
                                @click.stop="toggleVote(feedback.id)"
                                :class="[
                                    'w-10 h-10 rounded-lg border-2 flex items-center justify-center transition-all',
                                    isUpvoted(feedback.id) ? 'bg-gray-900 border-gray-900' : 'bg-white border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <svg class="w-6 h-6 transition-colors" :class="isUpvoted(feedback.id) ? 'text-white' : 'text-gray-600'" stroke-width="1.5">
                                    <use href="/images/icons.svg#upvote" />
                                </svg>
                            </button>
                            <span class="text-sm font-semibold text-gray-700">{{ getUpvoteCount(feedback.id) }}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ feedback.title }}</h3>
                                <span
                                    v-if="feedback.status"
                                    :class="['inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md border-[0.25px]', $statusClasses[feedback.status.color]]"
                                >
                                    <span class="w-1.5 h-1.5 bg-current rounded-full"></span>
                                    {{ feedback.status.name }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ shortenDescription(feedback.description) }}</p>
                            <div class="flex items-center gap-5 text-xs text-gray-500">
                                <div class="flex items-center gap-1">
                                    <svg class="w-5 h-5" stroke-width="1.5">
                                        <use href="/images/icons.svg#comment" />
                                    </svg>
                                    <span>{{ feedback.comments_count }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <img
                                        :src="feedback.author?.avatar || 'https://xsgames.co/randomusers/assets/avatars/male/23.jpg'"
                                        :alt="feedback.author?.name"
                                        class="w-5 h-5 rounded-full"
                                    >
                                    <span>{{ feedback.author?.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredFeedbacks.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No feedback found</h3>
                        <p class="text-sm text-gray-500">Try adjusting your search or filters</p>
                    </div>
                </div>
            </main>
        </div>

        <!-- Submit Feedback Modal -->
        <FeedbackModal
            v-model:show="showFeedbackModal"
            :boards="boards"
            :statuses="statuses"
            :feedbacks="feedbacks"
        />
    </MemberLayout>
</template>
