<template>
    <Head title="ChatGPT Custom Model" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ChatGPT Custom Model #{{ threadId }}#
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 space-y-4">
                        <select v-model="model" class="block max-w-xs py-2 pl-3 pr-10 border border-white bg-white dark:bg-gray-800 rounded-md focus:outline-none focus:border-none focus:ring-0 sm:text-sm appearance-none">
                            <option value="gpt-3.5-turbo-1106">GPT-3.5-Turbo</option>
                            <option value="gpt-4-1106-preview">GPT-4-1106-Preview</option>
                        </select>

                        <div class="space-y-2">
                            <template v-for="(reply, index) in questions" :key="index">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full border border-gray-400 px-3 py-2 flex items-center">
                                        <font-awesome-icon :icon="reply.role === 'user' ? 'user' : 'robot'" class="w-full h-auto mx-auto my-auto" />
                                    </div>
                                    <div class="ml-4">{{ reply.role === 'user' ? 'You' : 'GPT' }}</div>
                                </div>
                                <div class="ml-16 p-2 rounded-lg" v-html="processResponse(reply.content)"></div>
                            </template>
                        </div>

<!--                        <div class="space-y-2">
                            <template v-for="(reply, index) in questions" :key="index">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full border border-gray-400 px-3 py-2 flex items-center">
                                        <font-awesome-icon icon="user" class="w-full h-auto mx-auto my-auto" />
                                    </div>
                                    <div class="ml-4">You</div>
                                </div>
                                <div class="ml-16 p-2 rounded-lg" v-html="processResponse(reply.question)"></div>
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full border border-gray-400 px-2 py-1 flex items-center">
                                        <font-awesome-icon icon="robot" class="w-full h-auto mx-auto my-auto" />
                                    </div>
                                    <div class="ml-4">GPT</div>
                                </div>
                                <div v-if="reply.answer" class="ml-16  p-2 rounded-lg" v-html="processResponse(reply.answer)"></div>
                            </template>
                        </div>-->
                        <div class="relative pl-16">
                              <textarea
                                  ref="textarea"
                                  v-model="message"
                                  class="w-full h-32 p-2 border rounded-lg focus:outline-none dark:bg-gray-700 dark:text-white focus:ring-0"
                                  placeholder="Type your question here..."
                                  @keydown="handleKeyPress"></textarea>
                            <button
                                @click="sendMessage"
                                :class="['absolute bottom-4 right-2 px-2 py-2', isLoading ? 'busy-svg rounded-full bg-white text-black' : 'bg-black text-white rounded-lg']"
                                :disabled="isLoading">
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
import { ref, onMounted } from 'vue';
import axios from 'axios';

const threadId = ref('');
const message = ref('write console log in es6');
const model = ref('gpt-4-1106-preview');
const questions = ref([]);
const responseObj = ref({});
const isLoading = ref(false);

onMounted(() => {
    const queryParams = new URLSearchParams(window.location.search);
    const threadIdFromUrl = queryParams.get('threadId');
    if (threadIdFromUrl) {
        threadId.value = threadIdFromUrl;
        fetchConversation(threadId.value);
    }
});

const handleKeyPress = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

const processResponse = (text) => {
    const codeSections = [];
    text = text.replace(/```(\w+)?\n([\s\S]+?)```/g, (match, lang, code) => {
        codeSections.push(`<pre><code class="language-${lang || ''}">${code.trim()}</code></pre>`);
        return `CODESECTION-${codeSections.length - 1}`;
    });

    text = text.replace(/\n/g, '<br>');

    // Replace back the code sections
    text = text.replace(/CODESECTION-(\d+)/g, (match, index) => codeSections[index]);

    return text;
};

const sendMessage = () => {
    if (isLoading.value) return;

    if (!threadId.value) {
        threadId.value = generateThreadId();
        const newUrl = `${window.location.pathname}?threadId=${threadId.value}`;
        window.history.pushState({ threadId: threadId.value }, '', newUrl);
    }

    let currentMessage = { question: message.value.trim() };
    questions.value.push(currentMessage);
    message.value = '';
    isLoading.value = true;

    // Extract the last two Q/A pairs
    const lastTwoQA = questions.value.slice(-2).flatMap(q => [
        { role: 'user', content: q.question },
        q.answer ? { role: 'system', content: q.answer } : null
    ]).filter(Boolean);

    console.log('threadId.value', threadId.value)

    axios.post('/chat', {
        message: currentMessage.question,
        model: model.value,
        conversation: lastTwoQA,
        thread_id: threadId.value
    })
    .then((response) => {
        responseObj.value = response;
        questions.value[questions.value.length - 1].answer = response.data.content;
    })
    .catch((error) => {
        questions.value[questions.value.length - 1].answer = 'Error: No Response';
        console.error('Error:', error);
    })
    .finally(() => {
        isLoading.value = false;
    });
};

const generateThreadId = () => {
    return Date.now().toString();
};

const fetchConversation = (threadId) => {
    isLoading.value = true;
    axios.get(`/api/chat/conversation/${threadId}`)
    .then((response) => {
        console.log('response', response)
        questions.value = response.data.conversation.map(item => ({
            content: item.message,
            role: item.role,
        }));
    })
    .catch((error) => {
        console.error('Error fetching conversation:', error);
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
