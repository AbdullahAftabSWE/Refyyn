<script setup>
import Modal from '../../Components/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { computed, ref, watch, nextTick } from 'vue';

const props = defineProps({
    show: Boolean,
    boards: {
        type: Array,
        default: () => []
    },
    statuses: {
        type: Array,
        default: () => []
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    feedbacks: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:show'])

// Dropdown visibility
const showBoardDropdown = ref(false)
const showStatusDropdown = ref(false)

// Input ref
const titleInput = ref(null)

// Default to first board/status if available
const defaultBoardId = computed(() => props.boards[0]?.id || null)
const defaultStatusId = computed(() => props.statuses[0]?.id || null)

const form = useForm({
    title: '',
    description: '',
    board_id: null,
    status_id: null,
})

// Get selected items
const selectedBoard = computed(() => props.boards.find(b => b.id === form.board_id))
const selectedStatus = computed(() => props.statuses.find(s => s.id === form.status_id))

// Select handlers
const selectBoard = (board) => {
    form.board_id = board.id
    showBoardDropdown.value = false
}

const selectStatus = (status) => {
    form.status_id = status.id
    showStatusDropdown.value = false
}

// Similar requests search
const debouncedQuery = ref('')

// Set defaults when modal opens
const initForm = () => {
    form.board_id = defaultBoardId.value
    form.status_id = defaultStatusId.value
    showBoardDropdown.value = false
    showStatusDropdown.value = false
    debouncedQuery.value = ''
}

const submit = () => {
    form.post(route('feedback.store'), {
        onSuccess: (page) => {
            form.reset()
            emit('update:show', false)
        },
    })
}

const closeModal = () => {
    emit('update:show', false)
}

// Initialize form when props change
watch(() => props.show, (newVal) => {
    if (newVal) {
        initForm()
        nextTick(() => titleInput.value?.focus())
    }
})

// Color classes mapping
const colorClasses = {
    blue: 'bg-blue-500',
    red: 'bg-red-500',
    purple: 'bg-purple-500',
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    amber: 'bg-amber-500',
    emerald: 'bg-emerald-500',
    gray: 'bg-gray-500',
}

// Similar requests search (continued)
const searchQuery = ref('')
let debounceTimer = null

watch(() => form.title, (newTitle) => {
    searchQuery.value = newTitle
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        debouncedQuery.value = newTitle
    }, 300)
})

const similarRequests = computed(() => {
    const query = debouncedQuery.value?.toLowerCase().trim()
    if (!query || query.length < 3) return []

    const words = query.split(/\s+/).filter(w => w.length > 2)
    if (words.length === 0) return []

    return props.feedbacks
        .filter(f => {
            const title = f.title?.toLowerCase() || ''
            const desc = f.description?.toLowerCase() || ''
            return words.some(word => title.includes(word) || desc.includes(word))
        })
        .slice(0, 5)
})

const getRequestUrl = (feedback) => {
    if (props.isAdmin) {
        return `/admin/feedback/${feedback.slug}`
    }
    return `/feedback/${feedback.slug}`
}

// Track expanded similar requests
const expandedRequests = ref(new Set())

const toggleExpand = (requestId) => {
    if (expandedRequests.value.has(requestId)) {
        expandedRequests.value.delete(requestId)
    } else {
        expandedRequests.value.add(requestId)
    }
    expandedRequests.value = new Set(expandedRequests.value) // trigger reactivity
}

const isExpanded = (requestId) => expandedRequests.value.has(requestId)

const truncateDescription = (text, maxLength = 120) => {
    if (!text) return ''
    if (text.length <= maxLength) return text
    return text.substring(0, maxLength) + '...'
}
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="lg">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Submit Idea</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel>Title</InputLabel>
                    <TextInput
                        ref="titleInput"
                        v-model="form.title"
                        type="text"
                        placeholder="Title for your idea"
                        required
                        class="pl-4"
                    />
                </div>

                <!-- Similar Requests -->
                <div v-if="similarRequests.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-blue-800" stroke-width="2">
                            <use href="/images/icons.svg#bars" />
                        </svg>
                        <span class="text-sm font-medium text-blue-800">Similar Requests</span>
                    </div>
                    <p class="text-xs text-blue-600 mb-2">If your request matches one below, consider upvoting or commenting instead.</p>
                    <div class="space-y-2">
                        <div
                            v-for="request in similarRequests"
                            :key="request.id"
                            class="bg-white rounded border border-blue-100 hover:border-blue-300 transition-colors overflow-hidden"
                        >
                            <div class="flex items-center justify-between p-2">
                                <a
                                    :href="getRequestUrl(request)"
                                    class="text-sm text-gray-700 hover:text-blue-600 truncate flex-1 mr-3"
                                >
                                    {{ request.title }}
                                </a>
                                <div class="flex items-center gap-3 text-xs text-gray-500 flex-shrink-0">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" stroke-width="1.5">
                                            <use href="/images/icons.svg#upvote" />
                                        </svg>
                                        {{ request.upvotes_count || 0 }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" stroke-width="1.5">
                                            <use href="/images/icons.svg#comment" />
                                        </svg>
                                        {{ request.comments_count || 0 }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="toggleExpand(request.id)"
                                        class="p-1 hover:bg-gray-100 rounded transition-colors"
                                    >
                                        <svg
                                            class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': isExpanded(request.id) }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div
                                v-if="isExpanded(request.id) && request.description"
                                class="px-2 pb-2"
                            >
                                <p class="text-xs text-gray-500 bg-gray-50 rounded p-2">
                                    {{ truncateDescription(request.description) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <InputLabel class="block text-sm font-medium text-gray-700 mb-1">Description</InputLabel>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent resize-none"
                        placeholder="Add details about your suggestion or issue"
                        required
                    ></textarea>
                </div>
                <div :class="isAdmin ? 'grid grid-cols-2 gap-4' : ''">
                    <!-- Board Dropdown -->
                    <div class="relative">
                        <InputLabel class="block text-sm font-medium text-gray-700 mb-1">Board</InputLabel>
                        <button
                            type="button"
                            @click="showBoardDropdown = !showBoardDropdown"
                            class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-white flex items-center justify-between"
                        >
                            <span class="flex items-center gap-2">
                                <span
                                    v-if="selectedBoard"
                                    class="w-2 h-2 rounded-full"
                                    :class="colorClasses[selectedBoard.color] || 'bg-gray-500'"
                                ></span>
                                <span>{{ selectedBoard?.name || 'Select board' }}</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div
                            v-if="showBoardDropdown"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
                        >
                            <button
                                v-for="board in boards"
                                :key="board.id"
                                type="button"
                                @click="selectBoard(board)"
                                :class="[
                                    'w-full px-4 py-2 text-sm text-left flex items-center gap-2 hover:bg-gray-50',
                                    form.board_id === board.id ? 'bg-gray-100' : ''
                                ]"
                            >
                                <span
                                    class="w-2 h-2 rounded-full"
                                    :class="colorClasses[board.color] || 'bg-gray-500'"
                                ></span>
                                {{ board.name }}
                            </button>
                        </div>
                        <div v-if="showBoardDropdown" class="fixed inset-0 z-0" @click="showBoardDropdown = false"></div>
                    </div>

                    <!-- Status Dropdown (Admin only) -->
                    <div v-if="isAdmin" class="relative">
                        <InputLabel class="block text-sm font-medium text-gray-700 mb-1">Status</InputLabel>
                        <button
                            type="button"
                            @click="showStatusDropdown = !showStatusDropdown"
                            class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-white flex items-center justify-between"
                        >
                            <span class="flex items-center gap-2">
                                <span
                                    v-if="selectedStatus"
                                    class="w-2 h-2 rounded-full"
                                    :class="colorClasses[selectedStatus.color] || 'bg-gray-500'"
                                ></span>
                                <span>{{ selectedStatus?.name || 'Select status' }}</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div
                            v-if="showStatusDropdown"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
                        >
                            <button
                                v-for="status in statuses"
                                :key="status.id"
                                type="button"
                                @click="selectStatus(status)"
                                :class="[
                                    'w-full px-4 py-2 text-sm text-left flex items-center gap-2 hover:bg-gray-50',
                                    form.status_id === status.id ? 'bg-gray-100' : ''
                                ]"
                            >
                                <span
                                    class="w-2.5 h-2.5 rounded-full"
                                    :class="colorClasses[status.color] || 'bg-gray-500'"
                                ></span>
                                {{ status.name }}
                            </button>
                        </div>
                        <div v-if="showStatusDropdown" class="fixed inset-0 z-0" @click="showStatusDropdown = false"></div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button
                        type="button"
                        @click="closeModal"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Submitting...' : 'Submit' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
