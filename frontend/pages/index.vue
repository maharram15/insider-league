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
import LeagueTable from '../components/LeagueTable.vue';
import axios from 'axios';
import { API_BASE_URL } from '../config/apiConfig';
export default {
  name: 'App',
  components: {
    LeagueTable
  },
  data() {
    return {
      tableData: [],
      playedMatches: [],
      isLoading: false,
      error: null,
    }
  },
  methods: {
    async playNextWeek() {
      await axios.post(`${API_BASE_URL}/api/v1/league/simulate/next`);

      await this.loadLeagueTable();
    },
    async playAllWeek() {
      await axios.post(`${API_BASE_URL}/api/v1/league/simulate/all`);

      await this.loadLeagueTable();
    },
    async reset() {
      if (confirm('Are you sure you want to reset the league data?')) {
        await axios.post(`${API_BASE_URL}/api/v1/league/reset`);

        await this.loadLeagueTable();
      }
    },
    async loadLeagueTable() {
      this.isLoading = true;
      this.error = null;
      this.tableData = null;

      try {
        const apiUrl = `${API_BASE_URL}/api/v1/league/results`;
        const response = await axios.get(apiUrl);

        if (response && response.data) {
          this.tableData = response.data.current_standings || [];
          this.playedMatches = response.data.played_matches || [];
        } else {
          this.error = 'Received unexpected data format from API.';
        }

      } catch (err) {
        this.error = err.data?.message || err.message || 'Failed to load league table.';
        this.tableData = [];
      } finally {
        this.isLoading = false;
      }
    },
  },
  mounted() {
    this.loadLeagueTable().catch(err => {
      this.error = err.message || 'Failed to load league table.';
    });
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
