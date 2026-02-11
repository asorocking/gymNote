<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, router } from '@inertiajs/vue3';
    import { ref, computed, watch, nextTick } from 'vue';
    import debounce from 'lodash/debounce';

    const props = defineProps({
        initialRecords: Array,
        settings: Object,
        currentDate: String
    });

    const currentMode = ref('cook');
    const records = ref(props.initialRecords || []);
    const searchQuery = ref('');
    const expandedRecords = ref(new Set());

    const titleInputs = ref([]);

    // Синхронизация локального списка с данными от сервера
    watch(() => props.initialRecords, (newVal) => {
        records.value = newVal;
    }, { deep: true });

    const toggleExpand = (id) => {
        if (expandedRecords.value.has(id)) {
            expandedRecords.value.delete(id);
        } else {
            expandedRecords.value.add(id);
        }
    };

    // Теперь эту функцию можно вызывать в HTML тегах
    const saveRecord = debounce((record) => { // debounce означает, что не нужно моментально сохранять в БД
        router.patch(route('records.update', record.id), {
            description: record.description,
            notes: record.notes,
            mode: record.mode
        }, { preserveScroll: true }); // Не переносить пользователя в верх экрана при обновлении страницы (она постоянно обновляется при вводе)
    }, 500); // Задержка в мс для debounce

    // СОЗДАНИЕ: Отправляем запрос на сервер
    const addRecord = () => {
        // Проверка в консоли перед отправкой? Можно удалять
        console.log("Current Date Prop:", props.currentDate);

        router.post(route('records.store'), {
            mode: currentMode.value,
            date_key: props.currentDate,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Ждем, пока Vue обновит список элементов в DOM
                nextTick(() => {
                    // Берем самый первый инпут в списке (новая запись будет сверху)
                    if (titleInputs.value[0]) {
                        titleInputs.value[0].focus();
                    }
                });
            }
        });
    };

    // УДАЛЕНИЕ: Запрос на сервер
    const deleteRecord = (id) => {
        if (confirm('Удалить этот рецепт?')) {
            router.delete(route('records.destroy', id), {
                preserveScroll: true
            });
        }
    };

    const filteredRecords = computed(() => {
        let result = records.value.filter(r => r.mode === currentMode.value);
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            result = result.filter(r =>
                r.description?.toLowerCase().includes(query) ||
                r.notes?.toLowerCase().includes(query)
            );
        }
        return result;
    });
</script>

<template>
    <Head title="GymNote - Cook" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#FFFDF5] dark:bg-gray-950 pb-10">
            <div class="max-w-md mx-auto shadow-lg bg-white dark:bg-gray-900 min-h-screen">

                <div class="flex border-b border-gray-100 dark:border-gray-800">
                    <button v-for="mode in ['gym', 'shop', '120/80', 'cook', 'кбжу']" :key="mode"
                            @click="currentMode = mode === '120/80' ? 'pressure' : mode"
                            :class="['flex-1 py-3 text-[10px] uppercase font-bold tracking-tighter',
                        (currentMode === mode || (currentMode === 'pressure' && mode === '120/80'))
                        ? 'bg-white text-black border-b-2 border-black' : 'text-gray-400 bg-gray-50']">
                        {{ mode }}
                    </button>
                </div>

                <div class="p-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white capitalize">{{ currentMode }}</h1>
                    <div class="flex gap-3 text-gray-500">
                        <button class="hover:text-black"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></button>
                        <button class="hover:text-black"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg></button>
                        <button class="hover:text-black"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg></button>
                        <button class="hover:text-black"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg></button>
                    </div>
                </div>

                <div class="px-4 mb-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Поиск рецепта..."
                               class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="px-4 space-y-3">
                    <div v-for="record in filteredRecords" :key="record.id"
                         class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

                        <div class="p-4 flex items-center gap-3">
                            <button @click="toggleExpand(record.id)" class="text-gray-400 hover:text-black">
                                <svg :class="{'rotate-180': expandedRecords.has(record.id)}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <input
                                v-model="record.description"
                                @input="saveRecord(record)"
                                ref="titleInputs"
                                class="flex-1 border-none p-0 font-bold text-gray-700 dark:text-white focus:ring-0 bg-transparent"
                                placeholder="Название рецепта"
                            >

                            <button @click="deleteRecord(record.id)" class="text-gray-300 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>

                        <div v-show="expandedRecords.has(record.id)" class="px-4 pb-4">
                            <!-- @input означает, что при событиях на этом элементе, нужно использовать функцию, которая записана -->
                            <!-- в константу saveRecor и передать ей текущую record -->
                            <textarea v-model="record.notes" @input="saveRecord(record)"
                                      class="w-full bg-[#F8F9FF] dark:bg-gray-900 border border-blue-100 dark:border-gray-700 rounded-xl p-3 text-sm text-gray-600 dark:text-gray-300 focus:ring-1 focus:ring-blue-400 min-h-[150px]"
                                      placeholder="Как готовить..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="p-4 mt-4">
                    <button @click="addRecord"
                            class="w-full py-4 bg-[#0F172A] text-white rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-black transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Новый рецепт
                    </button>

                    <button class="w-full mt-3 py-4 bg-white text-gray-400 border border-gray-200 rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-gray-50 uppercase text-xs tracking-widest">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Удалить все записи
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
