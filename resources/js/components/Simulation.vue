<template>

  <div class="row m-4 ">
      <div class="col-5">
          <!-- League Table -->
          <div class="table-responsive">
              <table class="table">
                  <thead class="bg-dark text-white">
                      <tr>
                          <th>Team Name</th>
                          <th>P</th>
                          <th>W</th>
                          <th>D</th>
                          <th>L</th>
                          <th>GF</th>
                          <th>GA</th>
                          <th>GD</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr v-for="team in teams" :key="team.id">
                          <td>{{ team.name }}</td>
                          <td>{{ team.league_table.points ??  0 }}</td>
                          <td>{{ team.wins ??  0  }}</td>
                          <td>{{ team.draws ??  0  }}</td>
                          <td>{{ team.losses ??  0  }}</td>
                          <td>{{ team.league_table.goals_scored ??  0  }}</td>
                          <td>{{ team.league_table.goals_conceded ??  0  }}</td>
                          <td>{{ team.league_table.goal_difference ??  0  }}</td>
                      </tr>
                  </tbody>
              </table>
          </div>
      </div>
      <div class="col-3">
        <!-- Current Week Matches -->
        <div class="table-responsive">
            <table class="table">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Week {{ week }}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="match in fixtures" :key="match.id">
                        <td>{{ match.home_team.name }}</td>
                        <td>vs</td>
                        <td>{{ match.away_team.name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
      <div class="col-4">
          <!-- Championship Predictions -->
          <div class="table-responsive">
            <table class="table">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Championship Predictions</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="team in teams" :key="team.id">
                        <td>{{ team.name }}</td>
                        <td>{{ team.prediction ?? 0 }} %</td>
                    </tr>
                </tbody>
            </table>
          </div>
      </div>

  </div>
    



    <!-- Simulation Buttons -->
    <div class="m-5 d-flex justify-content-between">
        <button :class="{'btn btn-primary': weekallow === 0, 'btn btn-info': weekallow !== 0}" @click="playAllWeeks">{{ weekallow == 0 ? "Play All Weeks" : "All Weeks Played" }}</button>
        <button :class="{'btn btn-primary': weekallow === 0, 'btn btn-info': weekallow !== 0}" @click="playNextWeek">{{ weekallow == 0 ? "Play Next Week" : "Fixtures done" }} </button>
        <button class="btn btn-danger" @click="resetData">Reset Data</button>
    </div>

  </template>
  
<script>
import Swal from 'sweetalert2';
export default {
  props: {
    teams: {
        type: Array,
        required: true
    },
    fixtures: {
        type: Array,
        required: true
    },
    week:{
        type: Number,
        required: true
    },
    weekallow:{
        type: Number,
        required: true
    },
  },
  mounted() {
    if(this.week == 1){
      Swal.fire('Welcome!', 'Your welcome in league simulation. You can start the simulation.', 'success');
    }
  },
  methods: {
    playAllWeeks() {
      var allow = this.weekallow;
      if(allow){
        Swal.fire({
          icon: 'error',
          title: "No more weeks to play!",
        });
        return;
      }
      axios.post('play-all-weeks')
        .then(response => {
          Swal.fire({
            icon: 'success',
            title: "Weeks played successfully!",
          }).then(() => {
            window.location.reload();
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    playNextWeek() {
      var allow = this.weekallow;
      if(allow){
        Swal.fire({
          icon: 'error',
          title: "No more weeks to play!",
        });
        return;
      }
      axios.post('/play-next-week')
        .then(response => {
          Swal.fire({
            icon: 'success',
            title: "Week played successfully!",
          }).then(() => {
            window.location.reload();
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    resetData() {
      axios.post('/reset')
        .then(response => {
          Swal.fire({
            icon: 'success',
            title: 'Reset Successful',
            text: response.data.message
          });
          window.location.href = '/';
        })
        .catch(error => {
          console.log(error);
        });
    },
  },
};
</script>

  
  <style scoped>
  .card {
    margin-bottom: 20px;
  }
  </style>
  