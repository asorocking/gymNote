<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Инициализируем форму Inertia
const form = useForm({
    json_file: null,
});

const upload = () => {
    // Отправляем POST запрос на роут 'import'
    form.post(route('import'), {
        onSuccess: () => {
            alert('Данные успешно загружены!');
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                GymNote Control Panel
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="mb-8 p-4 border border-gray-700 rounded-lg">
                        <h3 class="text-lg font-medium text-white mb-4">Импорт данных из JSON</h3>
                        <div class="flex items-center gap-4">
                            <input
                                type="file"
                                @input="form.json_file = $event.target.files[0]"
                                class="text-gray-300 block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <button
                                @click="upload"
                                :disabled="form.processing"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Загрузка...' : 'Импортировать' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
