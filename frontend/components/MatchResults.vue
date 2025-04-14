<template>
  <div class="week-result">
    <div class="week-header">{{ currentWeek }}th Week Match Result</div>
    <table class="match-table">
      <thead>
      <tr>
        <th>Team 1</th>
        <th>Score</th>
        <th>Team 2</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(match, index) in playedMatches">
        <td>{{ match.home_team.name }}</td>
        <td class="center">{{ match.home_goals }} - {{ match.away_goals }}</td>
        <td>{{ match.away_team.name }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  props: {
    currentWeek: {
      type: Number,
      default: 1
    },
    playedMatches: {
      type: Array,
      default: () => []
    }
  },
}
</script>
<style scoped>
.match-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.match-table th,
.match-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.match-table th {
  background-color: #f5f5f5;
}

.center {
  text-align: center;
}
</style>
