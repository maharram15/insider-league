<template>
  <div class="app">
    <div v-if="isLoading">Loading league data...</div>
    <div v-else-if="error" class="error-message">
      Error: {{ error }}
    </div>

    <div v-else-if="tableData && tableData.length">
      <div v-for="(leagueData, index) in tableData" :key="index" class="league-table-wrapper">
        <LeagueTable
            :tableData="leagueData"
            :playedMatches="playedMatches"
            :currentWeek="index"
            @play-all-week="playAllWeek"
            @reset="reset"
            @play-next-week="playNextWeek" />
        <hr v-if="index < tableData.length - 1 && index !== 0"> </div>
    </div>

    <div v-else>No league table data available.</div>
  </div>

</template>

<script>
import LeagueTable from './LeagueTable.vue';
import axios from 'axios';

const API_BASE_URL = 'http://localhost:8080';

export default {
  name: 'App',
  components: {
    LeagueTable
  },
  data() {
    return {
      tableData: null,
      playedMatches: [],
      isLoading: false,
      error: null,
    }
  },
  methods: {
    async playNextWeek() {
      const response = await axios.post(`${API_BASE_URL}/api/v1/league/simulate/next`);

      await this.loadLeagueTable();

      console.log('Play next week response:', response);
    },
    async playAllWeek() {
      const response = await axios.post(`${API_BASE_URL}/api/v1/league/simulate/all`);

      await this.loadLeagueTable();

      console.log('Play all week response:', response);
    },
    async reset() {
      if (confirm('Are you sure you want to reset the league data?')) {
        const response = await axios.post(`${API_BASE_URL}/api/v1/league/reset`);

        await this.loadLeagueTable();
        console.log('Reset response:', response);
      }
    },
    async loadLeagueTable() {
      this.isLoading = true;
      this.error = null;
      this.tableData = null;

      try {
        const apiUrl = `${API_BASE_URL}/api/v1/league/results`;
        const response = await axios.get(apiUrl);
        console.log('Fetching league table from:', response);

        if (response && response.data) {
          console.log(response.data.current_standings)
          this.tableData = response.data.current_standings || [];
          this.playedMatches = response.data.played_matches || [];
        } else {
          this.error = 'Received unexpected data format from API.';
        }

      } catch (err) {
        console.error('Error fetching league table data:', err);
        this.error = err.data?.message || err.message || 'Failed to load league table.';
        this.tableData = [];
      } finally {
        this.isLoading = false;
      }
    },
  },
  mounted() {
    this.loadLeagueTable();
  }
}
</script>

<style>
.error-message {
  color: red;
  padding: 10px;
  border: 1px solid red;
}

.app {

}
</style>
