<template>
    <div>
        <pre>{{ responseObj }}</pre>
        <h1>Chat with GPT-3</h1>
        <textarea v-model="message"></textarea>
        <button @click="sendMessage">Send</button>
        <div v-for="(reply, index) in replies" :key="index">
            {{ reply }}
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const message = ref('');
const replies = ref([]);
const responseObj = ref({});

const sendMessage = () => {
    axios.post('/chat', { message: message.value })
    .then(response => {
        responseObj.value = response;
        console.log('response', response)
        // Assuming the response contains the message from OpenAI
        replies.value.push(response.data.content);
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle error (e.g., show an error message)
    });
};
</script>
