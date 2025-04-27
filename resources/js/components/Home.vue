<template>
  <div class="home">
    <div class="jumbotron py-5 mb-4 bg-light rounded-3">
      <div class="container">
        <h1 class="display-4">Welcome to Laravel Vue Coding Test</h1>
        <p class="lead">A comprehensive application demonstrating Laravel and Vue.js integration with advanced features.</p>
        <hr class="my-4">
        <p>This application showcases authentication, role management, CRUD operations with relationships, auditing, and Excel export/import functionality.</p>
        <p class="lead">
          <router-link v-if="!isAuthenticated" to="/login" class="btn btn-primary btn-lg me-2">Login</router-link>
          <router-link v-if="!isAuthenticated" to="/register" class="btn btn-success btn-lg">Register</router-link>
          <router-link v-if="isAuthenticated" to="/dashboard" class="btn btn-primary btn-lg">Go to Dashboard</router-link>
        </p>
      </div>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">Authentication</h5>
              <p class="card-text">Secure login and registration system with role-based access control.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">CRUD Operations</h5>
              <p class="card-text">Complete CRUD functionality for categories, projects, and tasks with relationships.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">Auditing</h5>
              <p class="card-text">Track all changes to your data with comprehensive audit trails.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">Role Management</h5>
              <p class="card-text">Create and manage user roles with specific permissions.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">File Uploads</h5>
              <p class="card-text">Upload and manage PDF documents with size validation.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title">Excel Import/Export</h5>
              <p class="card-text">Export and import data with Excel files, including background processing with queues.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'Home',
  computed: {
    ...mapGetters('auth', ['isAuthenticated'])
  }
};
</script>
