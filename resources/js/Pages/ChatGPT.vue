<template>
    <Head title="ChatGPT Custom Model" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ChatGPT Custom Model
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 space-y-4">
                        <select v-model="model" class="block max-w-xs py-2 pl-3 pr-10 border border-white bg-white dark:bg-gray-800 rounded-md focus:outline-none focus:border-none focus:ring-0 sm:text-sm appearance-none">
                            <option value="gpt-3.5-turbo">GPT-3.5-Turbo</option>
                            <option value="code-davinci-edit-001">Davinci Codex</option>
                        </select>


                        <div class="space-y-2">
                            <template v-for="(reply, index) in questions" :key="index">
                                <h6>You</h6>
                                <div class="bg-gray-100 p-2 rounded-lg dark:bg-gray-600">{{ reply.question }}</div>
                                <h6>GPT</h6>
                                <div v-if="reply.answer" class="bg-blue-100 p-2 rounded-lg dark:bg-gray-600">{{ reply.answer }}</div>
                            </template>
                        </div>

                        <div class="relative">
              <textarea
                  ref="textarea"
                  v-model="message"
                  class="w-full h-32 p-2 border rounded-lg focus:outline-none dark:bg-gray-700 dark:text-white focus:ring-0"
                  placeholder="Type your question here..."
                  @keydown="handleKeyPress"
              ></textarea>

                            <button
                                @click="sendMessage"
                                :class="['absolute bottom-4 right-2 px-2 py-2', isLoading ? 'busy-svg rounded-full bg-white text-black' : 'bg-black text-white rounded-lg']"
                                :disabled="isLoading"
                            >
                                <svg v-if="isLoading" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-2 w-2 text-gizmo-gray-950 dark:text-gray-200" height="16" width="16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2z" stroke-width="0"></path></svg>
                                <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" class="text-white dark:text-black"><path d="M7 11L12 6L17 11M12 18V7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const message = ref('');
const model = ref('gpt-3.5-turbo');
const questions = ref([]);
const responseObj = ref({});
const isLoading = ref(false);

const handleKeyPress = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

const sendMessage = () => {
    if (isLoading.value) return;

    let currentMessage = { question: message.value.trim() };
    questions.value.push(currentMessage);
    message.value = '';
    isLoading.value = true;

    // Extract the last two Q/A pairs
    const lastTwoQA = questions.value.slice(-2).flatMap(q => [
        { role: 'user', content: q.question },
        q.answer ? { role: 'system', content: q.answer } : null
    ]).filter(Boolean);

    axios.post('/chat', {
        message: currentMessage.question,
        model: model.value,
        conversation: lastTwoQA
    })
    .then((response) => {
        responseObj.value = response;
        questions.value[questions.value.length - 1].answer = response.data.content;
    })
    .catch((error) => {
        questions.value[questions.value.length - 1].answer = 'No Response';
        console.error('Error:', error);
    })
    .finally(() => {
        isLoading.value = false;
    });
};

</script>

<style scoped>
.busy-svg {
    border: 3px solid black;
}
</style>
