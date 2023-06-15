<template>
  <div class="container">
    <h2>Generated Fixtures</h2>
    <div class="row">
      <div class="col-md-6" v-for="(week, index) in fixtures" :key="index">
        <div class="card mb-3">
          <div class="card-header bg-dark text-white">
            Week {{ index }}
          </div>
          <div class="card-body">
            <ul class="list-group">
              <li class="list-group-item" v-for="matche in week" :key="matche.id">
                  <div class="row">
                    <div class="col-5">{{ matche.home_team.name }}</div>
                    <div class="col-2"> vs </div>
                    <div class="col-5">{{ matche.away_team.name }}</div>
                  </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- start simulation button -->
      <div class="col-md-3">
        <button class="btn btn-primary btn-block" @click="navigateToSimulation">Start Simulation</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    fixtures: {
      type: Array,
      required: true,
    },
  },
  methods: {
      navigateToSimulation() {
        window.location.href= '/simulation';
    },
  },
  
};
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  flex-direction: column;
  min-height: 100vh;
}

.card {
  margin-bottom: 20px;
}
</style>
