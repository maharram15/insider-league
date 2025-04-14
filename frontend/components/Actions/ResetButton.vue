<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: "ResetButton"
})
</script>

<template>
  <button class="reset-button" @click="$emit('reset')">Reset</button>
</template>

<style scoped>
.reset-button {
  padding: 8px 20px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}
</style>
