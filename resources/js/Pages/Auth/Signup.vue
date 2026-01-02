<script setup>
import { ref, computed} from 'vue'

import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false)

const form = useForm({
    email: '',
    fullName: '',
    password: '',
})

const passwordStrength = computed(() => {
    const p = form.password
    let strength = 0
    if (p.length >= 8) strength++
    if (/[A-Z]/.test(p)) strength++
    if (/[0-9]/.test(p)) strength++
    return strength
})

const strengthColor = computed(() => {
    const colors = ['bg-orange-500', 'bg-yellow-500', 'bg-green-500']
    return colors[passwordStrength.value - 1] || 'bg-gray-200'
})

const submit = () => {
    form.post(route('signup'), {
        onFinish: () => form.reset('password')
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Sign up" />
        <!-- Form Container -->
        <div class="flex-1 flex items-center justify-center">
            <div class="w-full max-w-md">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Create your account</h1>
                <p class="text-gray-500 mb-8">Start managing feedback seamlessly.</p>

                <div class="space-y-4">
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <TextInput
                                v-model="form.fullName"
                                type="text"
                                placeholder="Full name"
                                autofocus
                                required
                            />
                        </div>
                        <InputError v-if="form.errors.fullName">{{ form.errors.fullName }}</InputError>
                    </div>

                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <TextInput
                                v-model="form.email"
                                type="email"
                                placeholder="Email"
                                required
                            />
                        </div>
                        <InputError v-if="form.errors.email">{{ form.errors.email }}</InputError>
                    </div>

                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <TextInput
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Password"
                                required
                                @keyup.enter="submit"
                            />
                            <button
                                @click="showPassword = !showPassword"
                                type="button"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <InputError v-if="form.errors.password">{{ form.errors.password }}</InputError>

                        <!-- Password Strength Indicator -->
                        <div class="mt-3 space-y-2">
                            <div class="flex gap-1">
                                <div
                                    v-for="i in 3"
                                    :key="i"
                                    class="h-1 flex-1 rounded-full transition-all duration-300"
                                    :class="i <= passwordStrength ? strengthColor : 'bg-gray-200'"
                                ></div>
                            </div>
                            <p class="text-xs text-gray-500">
                                <span :class="form.password.length >= 8 ? 'text-green-600' : ''">
                                  {{ form.password.length >= 8 ? '✓' : '○' }} At least 8 characters
                                </span>
                                <br>
                                <span :class="(/[A-Z]/.test(form.password) && /[a-z]/.test(form.password)) ? 'text-green-600' : ''">
                                  {{ (/[A-Z]/.test(form.password) && /[a-z]/.test(form.password)) ? '✓' : '○' }} Contains lowercase & uppercase
                                </span>
                                <br>
                                <span :class="/[0-9]/.test(form.password) ? 'text-green-600' : ''">
                                  {{ /[0-9]/.test(form.password) ? '✓' : '○' }} Contains number
                                </span>
                            </p>
                        </div>
                    </div>

                    <PrimaryButton
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full"
                    >
                        {{ form.processing ? 'Creating account...' : 'Create account' }}
                    </PrimaryButton>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-400">Or sign up with</span>
                        </div>
                    </div>

                    <a
                        :href="route('auth.redirect', 'google')"
                        class="w-full py-3 border border-gray-300 rounded-lg font-medium flex items-center justify-center gap-3 hover:bg-gray-50 transition-all">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Google</span>
                    </a>

                    <p class="text-center text-sm text-gray-400 mt-6">
                        Already have an account?
                        <Link :href="route('login')" class="text-blue-500 font-medium hover:text-blue-600">Log in</Link>
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
