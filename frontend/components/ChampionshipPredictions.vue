<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: "ChampionshipPredictions",
  props: {
    predictions: {
      type: Array,
      default: () => []
    },
    currentWeek: {
      type: Number,
      required: true
    }
  },
})
</script>

<template>
  <div class="predictions-section">
    <div class="week-header">{{ currentWeek }}™ Week Predictions of Championship</div>
    <div class="prediction-grid">
      <div v-for="(prediction, index) in predictions"
           :key="prediction.team"
           class="prediction-row">
        <span class="team-name">{{ prediction.team }}</span>
        <span class="percentage">%{{ prediction.percentage }}</span>
      </div>
      <div v-if="predictions.length === 0">
        No predictions available yet.
      </div>
    </div>
  </div>
</template>

<style scoped>
.week-header {
  font-weight: bold;
  margin: 10px 0;
  color: #666;
}


.prediction-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  margin: 15px 0;
}

.prediction-row {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
  border-bottom: 1px dotted #ddd;
}
</style>
