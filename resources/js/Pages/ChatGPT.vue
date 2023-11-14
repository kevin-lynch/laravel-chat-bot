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
                        <h1 class="text-2xl font-semibold">Chat with GPT-3</h1>

                        <div class="space-y-2">
                            <template v-for="(reply, index) in questions" :key="index">
                                <h6>You</h6>
                                <div class="bg-gray-100 p-2 rounded-lg dark:bg-gray-600">
                                    {{ reply.question }}
                                </div>
                                <h6>GPT</h6>
                                <div v-if="reply.answer" class="bg-blue-100 p-2 rounded-lg dark:bg-gray-600">
                                    {{ reply.answer }}
                                </div>
                            </template>
                        </div>

                        <div class="relative">
              <textarea
                  v-model="message"
                  class="w-full h-32 p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white"
                  placeholder="Type your message here..."
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
const questions = ref([]);
const responseObj = ref({});
const isLoading = ref(false);

const sendMessage = () => {
    if (isLoading.value) {
        isLoading.value = false;
        return;
    }
    questions.value.push({question: message.value});

    isLoading.value = true;
    let msg = message.value;
    message.value = ''
    axios
    .post('/chat', { message: msg })
    .then((response) => {
        responseObj.value = response;
        console.log('response', response);
        // Assuming the response contains the message from OpenAI
        // questions.value.push({answer: response.data.content});
        questions.value[questions.value.length - 1].answer = response.data.content;

        message.value = ''; // Clear the input field after sending
    })
    .catch((error) => {
        console.error('Error:', error);
        // Handle error (e.g., show an error message)
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
