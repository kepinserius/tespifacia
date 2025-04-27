<template>
  <div class="dashboard">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Dashboard</h1>
    </div>
    
    <h1>Dashboard</h1>
    <div class="alert alert-success" role="alert">
      Welcome back, {{ user ? user.name : 'User' }}!
    </div>

    <div class="row mt-4">
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Categories</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Manage your categories</p>
            <router-link to="/categories" class="btn btn-outline-primary">View Categories</router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Projects</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Manage your projects</p>
            <router-link to="/projects" class="btn btn-outline-success">View Projects</router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Tasks</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Manage your tasks</p>
            <router-link to="/tasks" class="btn btn-outline-info">View Tasks</router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0">Roles</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Manage user roles</p>
            <router-link to="/roles" class="btn btn-outline-secondary">View Roles</router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-4 mb-4" v-if="isAdmin">
        <div class="card">
          <div class="card-header bg-dark text-white">
            <h5 class="card-title mb-0">Users</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Manage user accounts</p>
            <router-link to="/users" class="btn btn-outline-dark">View Users</router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header bg-warning">
            <h5 class="card-title mb-0">Excel Export/Import</h5>
          </div>
          <div class="card-body">
            <p class="card-text">Export or import data</p>
            <div class="btn-group">
              <button class="btn btn-outline-warning dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Export Options
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="#" @click.prevent="exportCategories">Export Categories</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="exportProjects">Export Projects</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="exportTasks">Export Tasks</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Recent Projects</h5>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="recentProjects.length === 0" class="text-center">
              <p>No projects found</p>
            </div>
            <ul v-else class="list-group">
              <li v-for="project in recentProjects" :key="project.id" class="list-group-item">
                <router-link :to="'/projects/' + project.uuid">{{ project.name }}</router-link>
                <span class="badge bg-secondary float-end">{{ project.category.name }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Recent Tasks</h5>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="recentTasks.length === 0" class="text-center">
              <p>No tasks found</p>
            </div>
            <ul v-else class="list-group">
              <li v-for="task in recentTasks" :key="task.id" class="list-group-item">
                <router-link :to="'/tasks/' + task.uuid">{{ task.title }}</router-link>
                <span :class="'badge float-end ' + getStatusBadgeClass(task.status)">{{ task.status }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import axios from 'axios';

export default {
  name: 'Dashboard',
  data() {
    return {
      loading: true,
      recentProjects: [],
      recentTasks: []
    };
  },
  computed: {
    ...mapGetters('auth', ['user', 'isAdmin'])
  },
  methods: {
    logout() {
      this.$store.dispatch('auth/logout')
        .then(() => {
          this.$router.push('/login');
        })
        .catch(error => {
          console.error('Logout error:', error);
        });
    },
    async fetchRecentData() {
      this.loading = true;
      try {
        const [projectsResponse, tasksResponse] = await Promise.all([
          axios.get('/api/projects', { params: { per_page: 5 } }),
          axios.get('/api/tasks', { params: { per_page: 5 } })
        ]);
        
        this.recentProjects = projectsResponse.data.data;
        this.recentTasks = tasksResponse.data.data;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        this.loading = false;
      }
    },
    exportCategories() {
      window.location.href = '/api/categories/export/excel';
    },
    exportProjects() {
      window.location.href = '/api/projects/export/excel';
    },
    exportTasks() {
      window.location.href = '/api/tasks/export/excel';
    },
    getStatusBadgeClass(status) {
      switch (status) {
        case 'pending':
          return 'bg-warning';
        case 'in_progress':
          return 'bg-primary';
        case 'completed':
          return 'bg-success';
        case 'cancelled':
          return 'bg-danger';
        default:
          return 'bg-secondary';
      }
    }
  },
  mounted() {
    this.fetchRecentData();
  }
};
</script>
