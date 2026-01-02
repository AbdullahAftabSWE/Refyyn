<script setup>
import { ref, computed } from 'vue'
import {Head} from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from '@inertiajs/vue3'

const props = defineProps({
    feedback: Object,
    upvoteCount: Number,
    upvoters: Array,
    totalMrr: Number,
    impactByPlan: Array,
    statuses: Array,
    boards: Array,
    hasUpvoted: Boolean,
})

const goBack = () => {
    router.visit('/admin/dashboard')
}

const deleteFeedback = () => {
    if (!confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) return
    showFeedbackMenu.value = false
    router.delete(`/admin/dashboard/feedback/${props.feedback.id}`)
}

const mainVoted = ref(props.hasUpvoted)
const currentUpvoteCount = ref(props.upvoteCount)
const internalNotes = ref('')
const showStatusDropdown = ref(false)
const showBoardDropdown = ref(false)
const showFeedbackMenu = ref(false)
const isUpvoting = ref(false)
const showUpvoteAnimation = ref(false)
const isSubmittingComment = ref(false)
const openDropdownId = ref(null)
const replyingToId = ref(null)
const replyContent = ref('')

const submitComment = () => {
    if (!internalNotes.value.trim() || isSubmittingComment.value) return
    isSubmittingComment.value = true

    router.post(`/admin/dashboard/feedback/${props.feedback.id}/comment`, {
        content: internalNotes.value,
    }, {
        onSuccess: () => {
            internalNotes.value = ''
        },
        onFinish: () => {
            isSubmittingComment.value = false
        }
    })
}

const submitReply = (parentId) => {
    if (!replyContent.value.trim()) return

    router.post(`/admin/dashboard/feedback/${props.feedback.id}/comment`, {
        content: replyContent.value,
        parent_id: parentId,
    }, {
        onSuccess: () => {
            replyContent.value = ''
            replyingToId.value = null
        }
    })
}

const deleteComment = (commentId) => {
    if (!confirm('Are you sure you want to delete this comment?')) return

    router.delete(`/admin/dashboard/feedback/${props.feedback.id}/comment/${commentId}`)
    openDropdownId.value = null
}

const toggleCommentPin = async (commentId) => {
    openDropdownId.value = null
    try {
        const response = await fetch(`/admin/dashboard/feedback/${props.feedback.id}/comment/${commentId}/pin`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        })

        const data = await response.json()

        if (data.success) {
            router.reload({ only: ['feedback'] })
        }
    } catch (error) {
        console.error('Failed to toggle pin:', error)
    }
}

const toggleMainVote = async () => {
    if (isUpvoting.value) return
    isUpvoting.value = true

    try {
        const response = await fetch(`/admin/dashboard/feedback/${props.feedback.id}/upvote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        })

        const data = await response.json()

        if (data.success) {
            mainVoted.value = data.upvoted
            currentUpvoteCount.value = data.upvoteCount

            if (data.upvoted) {
                showUpvoteAnimation.value = true
                setTimeout(() => {
                    showUpvoteAnimation.value = false
                }, 600)
            }
        }
    } catch (error) {
        console.error('Failed to toggle upvote:', error)
    } finally {
        isUpvoting.value = false
    }
}

// Tier functions
const getTierFromValue = (tierValue) => {
    switch (tierValue) {
        case 3: return 'GOLD'
        case 2: return 'SILVER'
        case 1: return 'BRONZE'
        default: return 'FREE'
    }
}

const getTierClass = (tier) => {
    const classes = {
        GOLD: 'bg-amber-100 text-amber-600',
        SILVER: 'bg-slate-100 text-slate-600',
        BRONZE: 'bg-orange-100 text-orange-600',
        FREE: 'bg-gray-100 text-gray-500',
    }
    return classes[tier] || 'bg-gray-50 text-gray-500'
}

const tierColors = {
    GOLD: 'bg-amber-500',
    SILVER: 'bg-slate-400',
    BRONZE: 'bg-orange-500',
    FREE: 'bg-gray-400',
}

// Computed for tier-grouped upvoters
const upvotersByTier = computed(() => {
    const groups = { GOLD: [], SILVER: [], BRONZE: [], FREE: [] }
    props.upvoters?.forEach(voter => {
        const tier = getTierFromValue(voter.tier)
        groups[tier].push(voter)
    })
    return groups
})

const tierMrr = computed(() => {
    const mrr = { GOLD: 0, SILVER: 0, BRONZE: 0, FREE: 0 }
    props.upvoters?.forEach(voter => {
        const tier = getTierFromValue(voter.tier)
        mrr[tier] += voter.mrr || 0
    })
    return mrr
})

// Update handlers
const updateStatus = (statusId) => {
    showStatusDropdown.value = false
    router.patch(`/admin/dashboard/feedback/${props.feedback.id}`, {
        status_id: statusId
    })
}

const updateBoard = (boardId) => {
    showBoardDropdown.value = false
    router.patch(`/admin/dashboard/feedback/${props.feedback.id}`, {
        board_id: boardId
    })
}

const totalComments = computed(() => {
    if (!props.feedback.comments) return 0
    let count = props.feedback.comments.length
    props.feedback.comments.forEach(c => {
        if (c.replies) count += c.replies.length
    })
    return count
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getRelativeTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now - date) / 1000)

    if (diffInSeconds < 60) return 'just now'
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`
    return formatDate(dateString)
}

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(amount)
}

</script>

<template>
    <Head title="Feature" />

        <div class="min-h-screen bg-[#FDFDFC]">
            <!-- Header -->
            <main class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <a @click.prevent="goBack" href="#" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition-colors py-1 pl-0 pr-3 rounded-md group">
                    <div class="p-1 rounded-md group-hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </div>
                    <span>Back to Feedback</span>
                </a>
                <div class="relative">
                    <button @click="showFeedbackMenu = !showFeedbackMenu" class="p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                    <div v-if="showFeedbackMenu" class="absolute right-0 mt-1 w-28 bg-white border border-gray-200 rounded-lg shadow-lg z-30 py-1">
                        <button @click="deleteFeedback" class="w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">Delete</button>
                    </div>
                    <div v-if="showFeedbackMenu" class="fixed inset-0 z-20" @click="showFeedbackMenu = false"></div>
                </div>
            </main>

            <!-- Main Content -->
            <div class="max-w-5xl mx-auto px-6 pb-4">
                <div class="flex gap-10">
                    <!-- Left Content -->
                    <div class="flex-1 max-w-2xl px-8 py-4 border border-gray-200 shadow-sm rounded-xl bg-white">
                        <!-- Status & Upvote Row -->
                        <div class="flex items-center justify-between mb-4">
                            <span
                                v-if="feedback.status"
                                :class="['inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-md border', $statusClasses[feedback.status?.color] || 'bg-gray-50 text-gray-700']"
                            >
                                <span :class="['w-1.5 h-1.5 rounded-full', $statusDotColors[feedback.status?.color] || 'bg-gray-500']"></span>
                                {{ feedback.status.name }}
                            </span>
                            <span v-else></span>

                            <!-- Upvote Button -->
                            <button
                                @click="toggleMainVote"
                                :disabled="isUpvoting"
                                :class="[
                                    'inline-flex items-center gap-2 px-4 py-2 text-sm border rounded-lg transition-all duration-200 relative overflow-hidden',
                                    mainVoted
                                        ? 'border-gray-900 bg-gray-900 text-white'
                                        : 'border-gray-300 hover:border-gray-400 text-gray-700',
                                    isUpvoting ? 'opacity-70 cursor-not-allowed' : ''
                                ]"
                            >
                                <svg
                                    :class="[
                                        'w-5 h-5 transition-transform duration-300',
                                        showUpvoteAnimation ? 'animate-upvote-bounce' : ''
                                    ]"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    :fill="mainVoted ? 'currentColor' : 'none'"
                                >
                                    <use href="/images/icons.svg#upvote" />
                                </svg>
                                <span class="font-medium">{{ mainVoted ? 'Upvoted' : 'Upvote' }}</span>
                                <span
                                    :class="[
                                        'transition-all duration-300',
                                        mainVoted ? 'text-gray-400' : 'text-gray-400',
                                        showUpvoteAnimation ? 'scale-125 text-emerald-400' : ''
                                    ]"
                                >{{ currentUpvoteCount }}</span>
                                <span
                                    v-if="showUpvoteAnimation"
                                    class="absolute inset-0 bg-emerald-400/20 animate-upvote-ripple rounded-lg"
                                ></span>
                            </button>
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl font-bold text-gray-900 leading-tight mb-2">
                            {{ feedback.title }}
                        </h1>

                        <!-- Date -->
                        <p class="text-sm text-gray-400 mb-6">{{ formatDate(feedback.created_at) }}</p>

                        <!-- Description -->
                        <p class="text-gray-600 leading-relaxed mb-8">
                            {{ feedback.description }}
                        </p>

                        <!-- Admin Comment -->
                        <div class="mb-8 ">
                            <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
                                Comment
                            </h3>
                            <textarea
                                v-model="internalNotes"
                                placeholder="Add to the discussion"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all"
                                rows="3"
                            ></textarea>
                            <div class="flex justify-end mt-3">
                                <button
                                    @click="submitComment"
                                    :disabled="!internalNotes.trim() || isSubmittingComment"
                                    class="px-4 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ isSubmittingComment ? 'Posting...' : 'Comment' }}
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-black/5 my-10"></div>

                        <!-- Discussion Section -->
                        <div class="mt-10">
                            <div class="flex items-center gap-2 mb-6 text-gray-800">
                                <svg class="w-6 h-6" stroke-width="2" stroke="currentColor" fill="none">
                                    <use href="/images/icons.svg#comment" />
                                </svg>
                                <h2 class="text-xl font-semibold">Discussion</h2>
                                <span class="text-sm text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ totalComments }}</span>
                            </div>

                            <!-- Comments List -->
                            <div v-if="feedback.comments && feedback.comments.length > 0" class="divide-y divide-gray-100">
                                <div v-for="comment in feedback.comments" :key="comment.id" class="py-6 first:pt-0">
                                    <!-- Main Comment -->
                                    <div
                                        class="flex gap-3"
                                        :class="comment.pinned ? 'bg-amber-50/50 border border-amber-200 -mx-3 px-3 py-3 rounded-lg' : ''"
                                    >
                                        <img
                                            v-if="comment.user?.avatar"
                                            :src="comment.user.avatar"
                                            :alt="comment.user.name"
                                            class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                        />
                                        <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium text-sm flex-shrink-0">
                                            {{ comment.user?.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-900 text-sm">{{ comment.user?.name || 'Unknown' }}</span>
                                                <span v-if="comment.internal" class="px-1.5 py-0.5 text-[10px] font-semibold text-white bg-blue-500 rounded">TEAM</span>
                                                <span class="text-xs text-gray-400">{{ getRelativeTime(comment.created_at) }}</span>
                                                <div class="flex-1"></div>
                                                <span v-if="comment.pinned" class="inline-flex items-center bg-amber-100 gap-1 text-[10px] font-medium text-amber-600 border border-amber-300 rounded-full px-2 py-0.5">
                                                                    <svg class="w-3 h-3" stroke-width="2" stroke="currentColor" fill="none">
                                                                        <use href="/images/icons.svg#pin" />
                                                                    </svg>
                                                                    Pinned
                                                                </span>
                                                <div class="relative">
                                                    <button
                                                        @click="openDropdownId = openDropdownId === comment.id ? null : comment.id"
                                                        class="p-1 rounded hover:bg-gray-100 transition-colors"
                                                    >
                                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        </svg>
                                                    </button>
                                                    <div v-if="openDropdownId === comment.id" class="absolute right-0 mt-1 w-24 bg-white border border-gray-200 rounded-lg shadow-lg z-30 py-1">
                                                        <button @click="toggleCommentPin(comment.id)" class="w-full text-left px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
                                                            {{ comment.pinned ? 'Unpin' : 'Pin' }}
                                                        </button>
                                                        <button @click="deleteComment(comment.id)" class="w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">
                                                            Delete
                                                        </button>
                                                    </div>
                                                    <div v-if="openDropdownId === comment.id" class="fixed inset-0 z-20" @click="openDropdownId = null"></div>
                                                </div>
                                            </div>
                                            <p class="text-gray-700 text-sm leading-relaxed mt-1">{{ comment.content }}</p>
                                            <button
                                                v-if="!comment.pinned"
                                                @click="replyingToId = replyingToId === comment.id ? null : comment.id"
                                                class="text-xs text-gray-400 hover:text-gray-600 mt-2"
                                            >
                                                Reply
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Replies with vertical line -->
                                    <div v-if="(comment.replies && comment.replies.length > 0) || replyingToId === comment.id" class="ml-4 mt-3 border-l-2 border-gray-200 pl-7">
                                        <!-- Reply Input -->
                                        <div v-if="replyingToId === comment.id" class="flex gap-2 mb-4">
                                            <input
                                                v-model="replyContent"
                                                type="text"
                                                placeholder="Write a reply..."
                                                @keyup.enter="submitReply(comment.id)"
                                                class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                            />
                                            <button @click="replyingToId = null; replyContent = ''" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                                            <button
                                                @click="submitReply(comment.id)"
                                                :disabled="!replyContent.trim()"
                                                class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 disabled:opacity-50"
                                            >
                                                Reply
                                            </button>
                                        </div>

                                        <!-- Replies -->
                                        <div v-for="(reply, rIndex) in comment.replies" :key="reply.id" :class="rIndex > 0 ? 'mt-4' : ''">
                                            <div class="flex gap-3">
                                                <img
                                                    v-if="reply.user?.avatar"
                                                    :src="reply.user.avatar"
                                                    :alt="reply.user.name"
                                                    class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                                />
                                                <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium text-sm flex-shrink-0">
                                                    {{ reply.user?.name?.charAt(0).toUpperCase() || '?' }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-gray-900 text-sm">{{ reply.user?.name || 'Unknown' }}</span>
                                                        <span v-if="reply.internal" class="px-1.5 py-0.5 text-[10px] font-semibold text-white bg-blue-500 rounded">TEAM</span>
                                                        <span class="text-xs text-gray-400">{{ getRelativeTime(reply.created_at) }}</span>
                                                        <div class="flex-1"></div>
                                                        <div class="relative">
                                                            <button
                                                                @click="openDropdownId = openDropdownId === 'reply-' + reply.id ? null : 'reply-' + reply.id"
                                                                class="p-1 rounded hover:bg-gray-100 transition-colors"
                                                            >
                                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                                                </svg>
                                                            </button>
                                                            <div v-if="openDropdownId === 'reply-' + reply.id" class="absolute right-0 mt-1 w-24 bg-white border border-gray-200 rounded-lg shadow-lg z-30 py-1">
                                                                <button @click="deleteComment(reply.id)" class="w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">
                                                                    Delete
                                                                </button>
                                                            </div>
                                                            <div v-if="openDropdownId === 'reply-' + reply.id" class="fixed inset-0 z-20" @click="openDropdownId = null"></div>
                                                        </div>
                                                    </div>
                                                    <p class="text-gray-700 text-sm leading-relaxed mt-1">{{ reply.content }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-400">
                                No comments yet
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar - Admin View -->
                    <div style="width: 280px" class="flex-shrink-0 bg-gray-50/50">
                        <div class="space-y-4 sticky top-8">

                            <!-- Status & Category Card -->
                            <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-white/80">
                                <div>
                                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 block">Requested By</span>
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <img
                                                v-if="feedback.author?.avatar"
                                                :src="feedback.author.avatar"
                                                :alt="feedback.author.name"
                                                class="w-9 h-9 rounded-full object-cover border border-gray-100"
                                            />
                                            <div v-else class="w-9 h-9 rounded-full bg-gray-300 flex items-center justify-center text-white font-semibold border border-gray-100">
                                                {{ feedback.author?.name?.charAt(0).toUpperCase() || '?' }}
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm leading-tight">{{ feedback.author?.name || 'Unknown' }}</p>
                                            <p class="text-xs text-gray-500">{{ feedback.author?.email || '' }}</p>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-100 my-4"></div>
                                </div>

                                <div class="mb-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                                    <div class="relative mt-2">
                                        <button
                                            @click="showStatusDropdown = !showStatusDropdown"
                                            :class="['w-full text-left font-medium text-sm rounded-lg pl-3 pr-4 py-2.5 cursor-pointer flex items-center justify-between border', $statusClasses[feedback.status?.color] || 'bg-gray-50 text-gray-700']"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span :class="['w-2 h-2 rounded-full', $statusDotColors[feedback.status?.color] || 'bg-gray-500']"></span>
                                                {{ feedback.status?.name || 'No Status' }}
                                            </span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <!-- Status Dropdown Menu -->
                                        <div v-if="showStatusDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-30">
                                            <div class="p-2">
                                                <button
                                                    v-for="status in statuses"
                                                    :key="status.id"
                                                    @click="updateStatus(status.id)"
                                                    :class="[
                                                        'w-full flex items-center gap-2 px-3 py-2 text-sm rounded-md transition-colors',
                                                        status.id === feedback.status?.id ? 'bg-gray-100' : 'hover:bg-gray-50'
                                                    ]"
                                                >
                                                    <span :class="['w-2 h-2 rounded-full', $statusDotColors[status.color] || 'bg-gray-500']"></span>
                                                    <span class="text-gray-700">{{ status.name }}</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Click outside to close -->
                                        <div v-if="showStatusDropdown" class="fixed inset-0 z-20" @click="showStatusDropdown = false"></div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Board</label>
                                    <div class="relative mt-2">
                                        <button
                                            @click="showBoardDropdown = !showBoardDropdown"
                                            :class="['w-full text-left font-medium text-sm rounded-lg pl-3 pr-4 py-2.5 flex items-center justify-between transition-colors border', $statusClasses[feedback.board?.color] || 'bg-gray-50 text-gray-700']"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span :class="['w-2 h-2 rounded-full', $statusDotColors[feedback.board?.color] || 'bg-gray-500']"></span>
                                                {{ feedback.board?.emoji || '' }} {{ feedback.board?.name || 'No Board' }}
                                            </span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <!-- Board Dropdown Menu -->
                                        <div v-if="showBoardDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-30">
                                            <div class="p-2">
                                                <button
                                                    v-for="board in boards"
                                                    :key="board.id"
                                                    @click="updateBoard(board.id)"
                                                    :class="[
                                                        'w-full flex items-center gap-2 px-3 py-2 text-sm rounded-md transition-colors',
                                                        board.id === feedback.board?.id ? 'bg-gray-100' : 'hover:bg-gray-50'
                                                    ]"
                                                >
                                                    <span :class="['w-2 h-2 rounded-full', $statusDotColors[board.color] || 'bg-gray-500']"></span>
                                                    <span class="text-gray-700">{{ board.name }}</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Click outside to close -->
                                        <div v-if="showBoardDropdown" class="fixed inset-0 z-20" @click="showBoardDropdown = false"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Impact Card -->
                            <div v-if="totalMrr > 0" class="bg-white/80 border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-gradient-to-br from-emerald-100 to-transparent rounded-full opacity-50 blur-xl"></div>

                                <div class="flex items-center justify-between mb-4 relative z-10">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Business Impact</span>
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full">{{ formatMoney(totalMrr) }}/mo</span>
                                </div>

                                <div class="space-y-3 mb-2 relative z-10">
                                    <template v-for="tier in ['GOLD', 'SILVER', 'BRONZE', 'FREE']" :key="tier">
                                        <div v-if="upvotersByTier[tier].length > 0">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="flex items-center gap-2">
                                                    <span :class="['px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider', getTierClass(tier)]">
                                                        {{ tier }}
                                                    </span>
                                                    <span class="text-gray-400 font-normal">({{ upvotersByTier[tier].length }})</span>
                                                </span>
                                                <span class="font-bold text-gray-900">{{ formatMoney(tierMrr[tier]) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                                <div
                                                    class="h-1.5 rounded-full"
                                                    :class="tierColors[tier]"
                                                    :style="{ width: ((tierMrr[tier] / totalMrr) * 100) + '%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Upvoters Card -->
                            <div v-if="upvoters && upvoters.length > 0" class="bg-white/80 border border-gray-200 rounded-xl p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Upvoters ({{ upvoters.length }})</span>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="voter in upvoters.slice(0, 5)" :key="voter.id" class="flex items-center gap-3 group cursor-pointer">
                                        <img
                                            v-if="voter.avatar"
                                            :src="voter.avatar"
                                            :alt="voter.name"
                                            class="w-7 h-7 rounded-full object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-all"
                                        />
                                        <div v-else class="w-7 h-7 rounded-full bg-gray-300 flex items-center justify-center text-white font-semibold text-xs grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-all">
                                            {{ voter.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <div class="flex-1 flex items-center gap-2">
                                            <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">{{ voter.name }}</span>
                                            <span :class="['px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider', getTierClass(getTierFromValue(voter.tier))]">
                                                {{ getTierFromValue(voter.tier) }}
                                            </span>
                                        </div>
                                        <span v-if="voter.mrr" class="text-xs text-emerald-600">({{ formatMoney(voter.mrr) }}/mo)</span>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<style scoped>
@keyframes upvote-bounce {
    0%, 100% { transform: translateY(0) scale(1); }
    25% { transform: translateY(-4px) scale(1.2); }
    50% { transform: translateY(-2px) scale(1.1); }
    75% { transform: translateY(-1px) scale(1.05); }
}

@keyframes upvote-ripple {
    0% { transform: scale(0); opacity: 1; }
    100% { transform: scale(2); opacity: 0; }
}

.animate-upvote-bounce {
    animation: upvote-bounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.animate-upvote-ripple {
    animation: upvote-ripple 0.6s ease-out forwards;
}
</style>
