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
                        <div class="relative">
              <textarea
                  v-model="message"
                  class="w-full h-32 p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300 dark:bg-gray-700 dark:text-white"
                  placeholder="Type your message here..."
              ></textarea>

                            <button v-if="!isLoading"
                                @click="sendMessage"
                                class="absolute bottom-4 right-2 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600"
                                :disabled="isLoading"
                            >
                                Send
                            </button>
                            <span
                                v-else
                                class="absolute bottom-4 right-2 flex items-center space-x-1"
                            >
                  <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                  <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                  <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                </span>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(reply, index) in replies"
                                :key="index"
                                class="bg-blue-100 p-2 rounded-lg dark:bg-gray-600"
                            >
                                {{ reply }}
                            </div>
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
const replies = ref([]);
const responseObj = ref({});
const isLoading = ref(false);

const sendMessage = () => {
    isLoading.value = true;
    axios
    .post('/chat', { message: message.value })
    .then((response) => {
        responseObj.value = response;
        console.log('response', response);
        // Assuming the response contains the message from OpenAI
        replies.value.push(response.data.content);
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
/* Add custom styles here if needed */
</style>
