<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import { useForm } from '@inertiajs/vue3'
import { onBeforeUnmount, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from "@/Layouts/AdminLayout.vue"
import InputLabel from "@/Components/InputLabel.vue"
import TextInput from "@/Components/TextInput.vue"

const props = defineProps({
    changelog: Object,
    boards: Array,
    sidebarStats: Object,
})

const form = useForm({
    title: props.changelog.title,
    description: props.changelog.description,
    image: null,
    remove_image: false,
})

const imagePreview = ref(props.changelog.image ? `/storage/${props.changelog.image}` : null)
const isDragging = ref(false)
const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']

const editor = useEditor({
    extensions: [
        StarterKit,
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-blue-600 underline',
            },
        }),
    ],
    content: props.changelog.description,
    onUpdate: ({ editor }) => {
        form.description = editor.getHTML()
    },
})

const isValidImage = (file) => {
    return file && allowedTypes.includes(file.type)
}

const processFile = (file) => {
    if (isValidImage(file)) {
        form.image = file
        form.remove_image = false
        imagePreview.value = URL.createObjectURL(file)
    }
}

const handleImageChange = (e) => {
    const file = e.target.files[0]
    processFile(file)
}

const handleDrop = (e) => {
    e.preventDefault()
    isDragging.value = false
    const file = e.dataTransfer.files[0]
    processFile(file)
}

const handleDragOver = (e) => {
    e.preventDefault()
    isDragging.value = true
}

const handleDragLeave = () => {
    isDragging.value = false
}

const removeImage = () => {
    form.image = null
    form.remove_image = true
    imagePreview.value = null
}

const submit = () => {
    form.post(`/admin/dashboard/changelog/${props.changelog.id}`, {
        preserveScroll: true,
        forceFormData: true,
        headers: {
            'X-HTTP-Method-Override': 'PATCH',
        },
    })
}

const goBack = () => {
    router.visit(`/admin/dashboard/changelog/${props.changelog.slug}`)
}

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy()
    }
})

const toggleBold = () => editor.value?.chain().focus().toggleBold().run()
const toggleItalic = () => editor.value?.chain().focus().toggleItalic().run()
const toggleStrike = () => editor.value?.chain().focus().toggleStrike().run()
const toggleH2 = () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run()
const toggleH3 = () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run()
const toggleBulletList = () => editor.value?.chain().focus().toggleBulletList().run()
const toggleOrderedList = () => editor.value?.chain().focus().toggleOrderedList().run()
const toggleBlockquote = () => editor.value?.chain().focus().toggleBlockquote().run()
const toggleCodeBlock = () => editor.value?.chain().focus().toggleCodeBlock().run()
</script>

<template>
    <AdminLayout>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="min-h-[90px] flex items-center justify-between px-8 py-6 border-b border-gray-100 flex-shrink-0">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Changelog</h1>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="goBack"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </header>

            <!-- Main content area -->
            <div class="flex-1 overflow-y-auto px-8 py-6">
                <form @submit.prevent="submit" class="max-w-4xl mx-auto space-y-6">
                    <!-- Title input -->
                    <div>
                        <InputLabel>Title</InputLabel>
                        <TextInput
                            v-model="form.title"
                            type="text"
                            placeholder="What's new?"
                            required
                            class="pl-4"
                        />
                        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
                    </div>

                    <!-- Hero Image upload -->
                    <div class="relative">
                        <InputLabel>Hero Image (optional)</InputLabel>
                        <div
                            v-if="!imagePreview"
                            @drop="handleDrop"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            :class="[
                                'border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer',
                                isDragging ? 'border-gray-400 bg-gray-50' : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <input
                                type="file"
                                accept=".jpg,.jpeg,.png,.gif,.webp"
                                @change="handleImageChange"
                                class="hidden"
                                id="image-upload"
                            />
                            <label for="image-upload" class="cursor-pointer">
                                <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Drop an image here or click to upload</p>
                            </label>
                        </div>
                        <div v-else class="relative">
                            <img :src="imagePreview" class="w-full h-40 object-cover rounded-lg" />
                            <button
                                type="button"
                                @click="removeImage"
                                class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-md hover:bg-red-600"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</p>
                    </div>

                    <!-- Rich text editor -->
                    <div>
                        <InputLabel>Description</InputLabel>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Toolbar -->
                            <div class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-1">
                                <button
                                    @click.prevent="toggleBold"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('bold') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Bold"
                                >
                                    <strong>B</strong>
                                </button>
                                <button
                                    @click.prevent="toggleItalic"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('italic') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Italic"
                                >
                                    <em>I</em>
                                </button>
                                <button
                                    @click.prevent="toggleStrike"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('strike') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Strikethrough"
                                >
                                    <s>S</s>
                                </button>

                                <div class="border-l border-gray-300 mx-1"></div>

                                <button
                                    @click.prevent="toggleH2"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('heading', { level: 2 }) ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Heading 2"
                                >
                                    H2
                                </button>
                                <button
                                    @click.prevent="toggleH3"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('heading', { level: 3 }) ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Heading 3"
                                >
                                    H3
                                </button>

                                <div class="border-l border-gray-300 mx-1"></div>

                                <button
                                    @click.prevent="toggleBulletList"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('bulletList') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Bullet List"
                                >
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                    </svg>
                                </button>
                                <button
                                    @click.prevent="toggleOrderedList"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('orderedList') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Ordered List"
                                >
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                </button>

                                <div class="border-l border-gray-300 mx-1"></div>

                                <button
                                    @click.prevent="toggleBlockquote"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('blockquote') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Blockquote"
                                >
                                    <svg class="w-5 h-5" stroke-width="2">
                                        <use href="/images/icons.svg#quote" />
                                    </svg>
                                </button>
                                <button
                                    @click.prevent="toggleCodeBlock"
                                    :class="[
                                        'p-2 rounded hover:bg-gray-200 transition-colors',
                                        editor?.isActive('codeBlock') ? 'bg-gray-200 text-gray-900' : 'text-gray-600'
                                    ]"
                                    title="Code Block"
                                >
                                    <svg class="w-5 h-5" stroke-width="2">
                                        <use href="/images/icons.svg#code" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Editor content -->
                            <EditorContent :editor="editor" class="prose prose-gray max-w-none min-h-[400px] p-2 focus:outline-none" />
                        </div>
                        <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
:deep(.ProseMirror) {
    outline: none;
}

:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    color: #d1d5db;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

:deep(.ProseMirror) {
    @apply prose prose-gray max-w-none;
}
</style>
