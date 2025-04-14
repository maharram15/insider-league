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
        <tr v-for="item in tableData" :key="item.team.id">
          <td>{{ item.team.name }}</td>
          <td class="center">{{ item.points }}</td>
          <td class="center">{{ item.played }}</td>
          <td class="center">{{ item.won }}</td>
          <td class="center">{{ item.drawn }}</td>
          <td class="center">{{ item.lost }}</td>
          <td class="center">{{ item.goal_difference }}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <MatchResults :current-week="currentWeek" :playedMatches="playedMatchesCurentWeek" />

    <ChampionshipPredictions :current-week="currentWeek" :predictions="dynamicPredictions" />

    <div class="action-buttons">
      <PlayAllButton @play-all-week="$emit('play-all-week')" />
      <div class="separator"></div>
      <ResetButton @reset="$emit('reset')" />
      <div class="separator"></div>
      <PlayNextButton @play-next-week="$emit('play-next-week')"/>
    </div>
  </div>
</template>

<script>
import MatchResults from "~/components/MatchResults.vue";
import PlayAllButton from "~/components/Actions/PlayAllButton.vue";
import ResetButton from "~/components/Actions/ResetButton.vue";
import PlayNextButton from "~/components/Actions/PlayNextButton.vue";

export default {
  components: { PlayNextButton, ResetButton, PlayAllButton, MatchResults },
  props: {
    tableData: {
      type: Array,
      required: true
    },
    playedMatches: {
      type: Array,
      required: true
    },
    currentWeek: {
      type: Number,
      required: true
    }
  },
  computed: {
    playedMatchesCurentWeek() {
      return this.playedMatches.filter(match => match.week === this.currentWeek);
    },
    dynamicPredictions() {
      if (!this.isValidTableData()) {
        return [];
      }

      const TOTAL_WEEKS = 6;

      const currentWeek = this.tableData[0]?.week ?? 0;

      if (currentWeek === 0) {
        return this.calculateWeekZeroPredictions(this.tableData);
      }
      if (currentWeek >= TOTAL_WEEKS) {
        return this.calculateFinalWeekPredictions(this.tableData);
      }

      return this.calculateIntermediatePredictions(this.tableData, currentWeek, TOTAL_WEEKS);
    }
  },
  methods: {
    isValidTableData() {
      return this.tableData && this.tableData.length > 0;
    },

    finalizePredictions(predictions) {
      this.adjustSumTo100(predictions);
      predictions.sort((a, b) => a.team.localeCompare(b.team));
      return predictions;
    },

    calculateWeekZeroPredictions(tableData) {
      const numTeams = tableData.length;
      const initialPercentage = numTeams > 0 ? 100 / numTeams : 0;
      const roundedPercentage = Math.round(initialPercentage);
      const predictions = tableData.map(item => ({
        team: item.team.name,
        percentage: roundedPercentage
      }));
      return this.finalizePredictions(predictions);
    },

    calculateFinalWeekPredictions(tableData) {
      let winner = tableData[0];
      for (let i = 1; i < tableData.length; i++) {
        if (tableData[i].points > winner.points) {
          winner = tableData[i];
        }
      }
      const predictions = tableData.map(item => ({
        team: item.team.name,
        percentage: item.team.id === winner.team.id ? 100 : 0
      }));
      predictions.sort((a, b) => a.team.localeCompare(b.team));
      return predictions;
    },

    calculateIntermediatePredictions(tableData, currentWeek, totalWeeks) {
      const pointsList = tableData.map(item => Number(item.points) || 0);
      const minPoints = pointsList.length > 0 ? Math.max(0, Math.min(...pointsList)) : 0;

      let totalWeight = 0;
      const teamWeights = tableData.map(item => {
        const weight = this.calculateTeamWeight(item, minPoints, currentWeek, totalWeeks);
        totalWeight += weight;
        return { team: item.team.name, weight: weight };
      });

      if (totalWeight <= 0) {
        const numTeams = tableData.length;
        const equalPercentage = numTeams > 0 ? Math.round(100 / numTeams) : 0;
        const predictions = tableData.map(item => ({ team: item.team.name, percentage: equalPercentage }));
        return this.finalizePredictions(predictions);
      }

      const predictions = teamWeights.map(item => ({
        team: item.team,
        percentage: Math.round((item.weight / totalWeight) * 100)
      }));

      return this.finalizePredictions(predictions);
    },

    calculateTeamWeight(teamItem, minPoints, currentWeek, totalWeeks) {
      const points = Number(teamItem.points) || 0;
      const baseWeight = points + 1;
      const boostFactor = (points - minPoints) * (currentWeek / totalWeeks);

      return Math.max(0.1, baseWeight + boostFactor);
    },
    adjustSumTo100(predictions) {
      if (!predictions || predictions.length === 0) {
        return;
      }

      let currentTotalPercentage = predictions.reduce((sum, p) => sum + p.percentage, 0);
      let diff = 100 - currentTotalPercentage;

      if (diff === 0) {
        return;
      }

      predictions.sort((a, b) => b.percentage - a.percentage);

      if (diff > 0) {
        for (let i = 0; i < diff; i++) {
          predictions[i % predictions.length].percentage++;
        }
      } else {
        let remainingToSubtract = Math.abs(diff);
        for (let i = 0; i < predictions.length && remainingToSubtract > 0; i++) {
          if (predictions[i].percentage > 0) {
            predictions[i].percentage--;
            remainingToSubtract--;
          }
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


.action-buttons {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 20px 0;
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
