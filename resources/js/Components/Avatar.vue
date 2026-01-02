<template>
    <div
        class="w-9 h-9 rounded-full flex items-center justify-center border border-gray-200 bg-gray-500 text-white"
    >
        <img
            v-if="$page.props.auth.user?.avatar"
            :src="$page.props.auth.user.avatar"
            class="w-full h-full rounded-full object-cover"
        />
        <div v-else class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
    </div>
</template>
