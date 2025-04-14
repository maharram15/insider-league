<template>
  <div class="container">
    <div class="league-section">
      <h1>League Table</h1>
      <table class="league-table">
        <thead>
        <tr>
          <th>Teams</th>
          <th>PTS</th>
          <th>P</th>
          <th>W</th>
          <th>D</th>
          <th>L</th>
          <th>GD</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="item in tableData" :key="item?.team?.id || item?.team?.name || Math.random()">
          <td>{{ item?.team?.name }}</td>
          <td class="center">{{ item?.points }}</td>
          <td class="center">{{ item?.played }}</td>
          <td class="center">{{ item?.won }}</td>
          <td class="center">{{ item?.drawn }}</td>
          <td class="center">{{ item?.lost }}</td>
          <td class="center">{{ item?.goal_difference }}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <MatchResults :current-week="currentWeek" :playedMatches="playedMatches.filter((item) => item.week === currentWeek)" />

    <div class="predictions-section">
      <div class="week-header">{{ currentWeek }}™ Week Predictions of Championship</div>
      <div class="prediction-grid">
        <div v-for="(prediction, index) in dynamicPredictions"
             :key="index"
             class="prediction-row">
          <span class="team-name">{{ prediction.team }}</span>
          <span class="percentage">%{{ prediction.percentage }}</span>
        </div>
        <div v-if="!dynamicPredictions || dynamicPredictions.length === 0">
          No predictions available yet.
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <button class="play-all" @click="$emit('play-all-week')">Play All</button>
      <div class="separator"></div>
      <button class="reset" @click="$emit('reset')">Reset</button>
      <div class="separator"></div>
      <button class="next-week" @click="$emit('play-next-week')">Next Week</button>
    </div>
  </div>
</template>

<script>
import MatchResults from "~/pages/MatchResults.vue";

export default {
  components: {MatchResults},
  props: {
    tableData: {
      type: Array,
      default: () => []
    },
    playedMatches: {
      type: Array,
      default: () => []
    },
    currentWeek: {
      type: Number,
      required: true
    }
  },
  computed: {
    dynamicPredictions() {
      if (!this.tableData || !Array.isArray(this.tableData) || this.tableData.length === 0 || !this.tableData[0]?.team) {
        return [];
      }

      const totalWeeks = 6;
      const currentWeek = this.tableData[0]?.played ?? 0;

      if (currentWeek === 0) {
        const numTeams = this.tableData.length;
        const initialPercentage = numTeams > 0 ? 100 / numTeams : 0;
        const roundedPercentage = Math.round(initialPercentage);
        const predictions = this.tableData.map(item => ({
          team: item.team.name,
          percentage: roundedPercentage
        }));
        this.adjustSumTo100(predictions);
        predictions.sort((a, b) => a.team.localeCompare(b.team));
        return predictions;
      }

      if (currentWeek >= totalWeeks) {
        let winner = this.tableData[0];
        for (let i = 1; i < this.tableData.length; i++) {
          const currentTeam = this.tableData[i];
          if (currentTeam.points > winner.points) {
            winner = currentTeam;
          }
        }
        const finalPredictions = this.tableData.map(item => ({
          team: item.team.name,
          percentage: item.team.id === winner.team.id ? 100 : 0
        }));
        finalPredictions.sort((a, b) => a.team.localeCompare(b.team));
        return finalPredictions;
      }

      let minPoints = Infinity;
      this.tableData.forEach(item => {
        const points = Number(item.points);
        if (!isNaN(points) && points < minPoints) {
          minPoints = points;
        }
      });
      minPoints = minPoints === Infinity ? 0 : Math.max(0, minPoints);

      let totalWeight = 0;
      const teamWeights = this.tableData.map(item => {
        const points = Number(item.points) || 0;
        const baseWeight = points + 1;
        const boostFactor = (points - minPoints) * (currentWeek / totalWeeks);
        const weight = Math.max(0.1, baseWeight + boostFactor);
        totalWeight += weight;
        return { team: item.team.name, weight: weight };
      });

      if (totalWeight <= 0) {
        const numTeams = this.tableData.length;
        const equalPercentage = numTeams > 0 ? Math.round(100 / numTeams) : 0;
        const predictions = this.tableData.map(item => ({ team: item.team.name, percentage: equalPercentage }));
        this.adjustSumTo100(predictions);
        predictions.sort((a, b) => a.team.localeCompare(b.team));
        return predictions;
      }

      let calculatedPredictions = teamWeights.map(item => ({
        team: item.team,
        percentage: Math.round((item.weight / totalWeight) * 100)
      }));

      this.adjustSumTo100(calculatedPredictions);
      calculatedPredictions.sort((a, b) => a.team.localeCompare(b.team));

      return calculatedPredictions;
    }
  },
  methods: {
    adjustSumTo100(predictions) {
      if (!predictions || predictions.length === 0) return;

      let currentTotalPercentage = predictions.reduce((sum, p) => sum + p.percentage, 0);
      let diff = 100 - currentTotalPercentage;

      if (diff !== 0) {
        predictions.sort((a, b) => b.percentage - a.percentage);

        for (let i = 0; i < Math.abs(diff); i++) {
          const predictionIndex = i % predictions.length;
          const adjustment = Math.sign(diff);

          if (predictions[predictionIndex].percentage + adjustment >= 0) {
            predictions[predictionIndex].percentage += adjustment;
          } else {
            let foundAlternative = false;
            for (let j = 1; j < predictions.length; j++) {
              const alternativeIndex = (predictionIndex + j) % predictions.length;
              if (predictions[alternativeIndex].percentage + adjustment >= 0) {
                predictions[alternativeIndex].percentage += adjustment;
                foundAlternative = true;
                break;
              }
            }
            if (!foundAlternative) {
              break;
            }
          }
        }

        predictions.forEach(p => { p.percentage = Math.max(0, p.percentage); });

        currentTotalPercentage = predictions.reduce((sum, p) => sum + p.percentage, 0);
        diff = 100 - currentTotalPercentage;
        if (diff !== 0 && predictions.length > 0) {
          predictions[0].percentage = Math.max(0, predictions[0].percentage + diff);
        }
      }
    }
  },
}
</script>

<style scoped>
.container {
  font-family: Arial, sans-serif;
  max-width: 800px;
  margin: 20px auto;
  padding: 20px;
  border: 1px solid #ddd;
}

h1, h2 {
  color: #333;
  margin-bottom: 15px;
}

.league-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 25px;
}

.league-table th,
.league-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.league-table th {
  background-color: #f5f5f5;
}

.center {
  text-align: center;
}

.week-header {
  font-weight: bold;
  margin: 10px 0;
  color: #666;
}

.match-row {
  display: flex;
  align-items: center;
  margin: 8px 0;
  gap: 15px;
}

.score {
  font-weight: bold;
  margin: 0 10px;
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

.action-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 20px 0;
}

.play-all, .next-week, .reset {
  padding: 8px 20px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}

.separator {
  flex-grow: 1;
  border-top: 1px solid #ddd;
  height: 1px;
}

.caption {
  color: #666;
  font-size: 0.9em;
  margin-top: 15px;
}
</style>
