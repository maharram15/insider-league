<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: "PlayAllButton"
})
</script>

<template>
  <button class="play-all" @click="$emit('play-all-week')">Play All</button>
</template>

<style scoped>
.play-all {
  padding: 8px 20px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}
</style>
