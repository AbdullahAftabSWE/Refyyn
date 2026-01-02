<template>
    <button
        class="w-full py-3 bg-blue-700 text-white rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-blue-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed group mt-6">
        <span><slot /></span>
        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
        </svg>
    </button>
</template>
