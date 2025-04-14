<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: "PlayNextButton"
})
</script>

<template>
  <button class="next-week" @click="$emit('play-next-week')">Next Week</button>
</template>

<style scoped>
.next-week {
  padding: 8px 20px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}
</style>
