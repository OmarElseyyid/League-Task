<template>
    <div class="container">
      <h2>Tournament Teams</h2>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="bg-dark text-white">
            <tr>
              <th>Team Name</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="team in teams" :key="team.id">
              <td>{{ team.name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="text-left">
        <button class="btn btn-primary" @click="generateFixtures">Generate Fixtures</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      teams: {
        type: Array,
        required: true,
      },
    },
    methods: {
        generateFixtures() {
        axios.post("/generate-fixtures")
            .then(response => {
                window.location.href = "/fixtures";
            })
            .catch(error => {
                console.error(error);
            });
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
  
  .table {
    margin-top: 30px;
  }
  
  </style>
  