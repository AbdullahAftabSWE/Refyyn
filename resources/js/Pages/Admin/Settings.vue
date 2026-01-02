<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from "@/Layouts/AdminLayout.vue"

const props = defineProps({
    boards: Array,
    statuses: Array,
    sidebarStats: Object,
})

const boardColors = ['blue', 'red', 'purple', 'green', 'yellow', 'emerald', 'gray', 'pink']
const statusColors = ['gray', 'yellow', 'blue', 'purple', 'pink', 'emerald', 'green', 'red', 'orange']

// Board editing
const editingBoardId = ref(null)
const boardForm = useForm({
    name: '',
    color: 'blue',
})

const newBoardForm = useForm({
    name: '',
    color: 'blue',
})

const startEditBoard = (board) => {
    editingBoardId.value = board.id
    boardForm.name = board.name
    boardForm.color = board.color
}

const cancelEditBoard = () => {
    editingBoardId.value = null
    boardForm.reset()
}

const saveBoard = (board) => {
    boardForm.patch(`/admin/settings/boards/${board.id}`, {
        onSuccess: () => {
            editingBoardId.value = null
            boardForm.reset()
        }
    })
}

const addBoard = () => {
    newBoardForm.post('/admin/settings/boards', {
        onSuccess: () => newBoardForm.reset()
    })
}

const deleteBoard = (board) => {
    if (confirm(`Delete "${board.name}"?`)) {
        router.delete(`/admin/settings/boards/${board.id}`)
    }
}

// Status editing
const editingStatusId = ref(null)
const statusForm = useForm({
    name: '',
    color: 'gray',
})

const newStatusForm = useForm({
    name: '',
    color: 'gray',
})

const startEditStatus = (status) => {
    editingStatusId.value = status.id
    statusForm.name = status.name
    statusForm.color = status.color
}

const cancelEditStatus = () => {
    editingStatusId.value = null
    statusForm.reset()
}

const saveStatus = (status) => {
    statusForm.patch(`/admin/settings/statuses/${status.id}`, {
        onSuccess: () => {
            editingStatusId.value = null
            statusForm.reset()
        }
    })
}

const addStatus = () => {
    newStatusForm.post('/admin/settings/statuses', {
        onSuccess: () => newStatusForm.reset()
    })
}

const deleteStatus = (status) => {
    if (confirm(`Delete "${status.name}"?`)) {
        router.delete(`/admin/settings/statuses/${status.id}`)
    }
}
</script>

<template>
    <Head title="Settings" />

    <AdminLayout>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="min-h-[100px] flex items-center px-8 py-6 border-b border-gray-100 flex-shrink-0">
                <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
            </header>

            <div class="flex-1 overflow-y-auto p-8 space-y-10">
                <!-- Boards Section -->
                <section class="max-w-2xl">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Boards</h2>
                        <p class="text-sm text-gray-500">Organize feedback into categories</p>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="board in boards"
                            :key="board.id"
                            class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg"
                        >
                            <template v-if="editingBoardId === board.id">
                                <input
                                    v-model="boardForm.name"
                                    type="text"
                                    class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                                />
                                <div class="flex gap-1">
                                    <button
                                        v-for="color in boardColors"
                                        :key="color"
                                        type="button"
                                        @click="boardForm.color = color"
                                        :class="[
                                            'w-4 h-4 rounded-full transition-transform',
                                            $boardColorClasses[color],
                                            boardForm.color === color ? 'ring-2 ring-offset-1 ring-gray-400 scale-110' : 'hover:scale-110'
                                        ]"
                                    ></button>
                                </div>
                                <button @click="saveBoard(board)" class="px-3 py-1.5 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">Save</button>
                                <button @click="cancelEditBoard" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                            </template>
                            <template v-else>
                                <span class="w-3 h-3 rounded-full" :class="$boardColorClasses[board.color]"></span>
                                <span class="flex-1 text-sm font-medium text-gray-900">{{ board.name }}</span>
                                <span class="text-xs text-gray-400">{{ board.feedback_count }} feedback</span>
                                <button @click="startEditBoard(board)" class="p-1.5 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteBoard(board)"
                                    :disabled="board.feedback_count > 0"
                                    :class="['p-1.5', board.feedback_count > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-red-600']"
                                    :title="board.feedback_count > 0 ? 'Cannot delete board with feedback' : 'Delete'"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </template>
                        </div>

                        <!-- Add Board -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-dashed border-gray-300 rounded-lg">
                            <input
                                v-model="newBoardForm.name"
                                type="text"
                                placeholder="New board name..."
                                class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-white"
                                @keyup.enter="addBoard"
                            />
                            <div class="flex gap-1">
                                <button
                                    v-for="color in boardColors"
                                    :key="color"
                                    type="button"
                                    @click="newBoardForm.color = color"
                                    :class="[
                                        'w-4 h-4 rounded-full transition-transform',
                                        $boardColorClasses[color],
                                        newBoardForm.color === color ? 'ring-2 ring-offset-1 ring-gray-400 scale-110' : 'hover:scale-110'
                                    ]"
                                ></button>
                            </div>
                            <button
                                @click="addBoard"
                                :disabled="!newBoardForm.name || newBoardForm.processing"
                                class="px-3 py-1.5 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Statuses Section -->
                <section class="max-w-2xl">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Statuses</h2>
                        <p class="text-sm text-gray-500">Track the progress of feedback items</p>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg"
                        >
                            <template v-if="editingStatusId === status.id">
                                <input
                                    v-model="statusForm.name"
                                    type="text"
                                    class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                                />
                                <div class="flex gap-1">
                                    <button
                                        v-for="color in statusColors"
                                        :key="color"
                                        type="button"
                                        @click="statusForm.color = color"
                                        :class="[
                                            'w-4 h-4 rounded-full transition-transform',
                                            $statusDotColors[color],
                                            statusForm.color === color ? 'ring-2 ring-offset-1 ring-gray-400 scale-110' : 'hover:scale-110'
                                        ]"
                                    ></button>
                                </div>
                                <button @click="saveStatus(status)" class="px-3 py-1.5 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">Save</button>
                                <button @click="cancelEditStatus" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                            </template>
                            <template v-else>
                                <span class="w-3 h-3 rounded-full" :class="$statusDotColors[status.color]"></span>
                                <span class="flex-1 text-sm font-medium text-gray-900">{{ status.name }}</span>
                                <span class="text-xs text-gray-400">{{ status.feedback_count }} feedback</span>
                                <button @click="startEditStatus(status)" class="p-1.5 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteStatus(status)"
                                    :disabled="status.feedback_count > 0"
                                    :class="['p-1.5', status.feedback_count > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-red-600']"
                                    :title="status.feedback_count > 0 ? 'Cannot delete status with feedback' : 'Delete'"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </template>
                        </div>

                        <!-- Add Status -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-dashed border-gray-300 rounded-lg">
                            <input
                                v-model="newStatusForm.name"
                                type="text"
                                placeholder="New status name..."
                                class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-white"
                                @keyup.enter="addStatus"
                            />
                            <div class="flex gap-1">
                                <button
                                    v-for="color in statusColors"
                                    :key="color"
                                    type="button"
                                    @click="newStatusForm.color = color"
                                    :class="[
                                        'w-4 h-4 rounded-full transition-transform',
                                        $statusDotColors[color],
                                        newStatusForm.color === color ? 'ring-2 ring-offset-1 ring-gray-400 scale-110' : 'hover:scale-110'
                                    ]"
                                ></button>
                            </div>
                            <button
                                @click="addStatus"
                                :disabled="!newStatusForm.name || newStatusForm.processing"
                                class="px-3 py-1.5 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
