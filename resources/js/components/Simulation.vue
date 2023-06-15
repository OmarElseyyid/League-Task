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
                            <th>GD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="team in teams" :key="team.id">
                            <td>{{ team.name }}</td>
                            <td>{{ team.points ??  0 }}</td>
                            <td>{{ team.won ??  0  }}</td>
                            <td>{{ team.drawn ??  0  }}</td>
                            <td>{{ team.lost ??  0  }}</td>
                            <td>{{ team.goalDifference ??  0  }}</td>
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
                          <td>{{ team.prediction ?? 0 }}</td>
                      </tr>
                  </tbody>
              </table>
            </div>
        </div>

    </div>
    



    <!-- Simulation Buttons -->
    <div class="m-5 d-flex justify-content-between">
        <button class="btn btn-primary" @click="playAllWeeks">Play All Weeks</button>
        <button class="btn btn-primary" @click="playNextWeek">Play Next Week</button>
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
    }
  },
  mounted() {
    if(this.week == 1){
      Swal.fire('Welcome!', 'Your welcome in league simulation. You can start the simulation.', 'success');
    }
  },
  methods: {
    playAllWeeks() {
      axios.post('play-all-weeks')
        .then(response => {
          
        })
        .catch(error => {
          console.log(error);
        });
    },
    playNextWeek() {
      axios.post('play-next-week')
        .then(response => {
          
        })
        .catch(error => {
          console.log(error);
        });
    },
    // done
    resetData() {
      axios.post('/api/reset')
        .then(response => {
          alert(response.data.message);
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
  