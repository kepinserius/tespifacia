<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-gradient-to-r from-blue-700 to-indigo-800 shadow-lg">
      <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
          <router-link class="text-white font-bold text-xl" to="/">Laravel Vue Coding Test</router-link>
          
          <div class="hidden md:block">
            <div class="flex items-center space-x-4">
              <template v-if="isAuthenticated">
                <router-link class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium" to="/dashboard">Dashboard</router-link>
                
                <!-- Categories Dropdown -->
                <div class="relative group">
                  <button class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                    Categories
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/categories">List Categories</router-link>
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/categories/create">Create Category</router-link>
                  </div>
                </div>
                
                <!-- Projects Dropdown -->
                <div class="relative group">
                  <button class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                    Projects
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/projects">List Projects</router-link>
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/projects/create">Create Project</router-link>
                  </div>
                </div>
                
                <!-- Tasks Dropdown -->
                <div class="relative group">
                  <button class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                    Tasks
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/tasks">List Tasks</router-link>
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/tasks/create">Create Task</router-link>
                  </div>
                </div>
                
                <!-- Roles Dropdown -->
                <div class="relative group">
                  <button class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                    Roles
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/roles">List Roles</router-link>
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/roles/create">Create Role</router-link>
                  </div>
                </div>
                
                <!-- Users Dropdown (Admin Only) -->
                <div class="relative group" v-if="isAdmin">
                  <button class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                    Users
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/users">List Users</router-link>
                    <router-link class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" to="/users/create">Create User</router-link>
                  </div>
                </div>
                
                <!-- Logout Button (Always Visible) -->
                <a href="#" @click.prevent="logout" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md text-sm ml-4 inline-block">
                  <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                  </span>
                </a>
              </template>
              <template v-else>
                <router-link class="text-white hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium" to="/login">Login</router-link>
                <router-link class="bg-white text-blue-700 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium" to="/register">Register</router-link>
              </template>
            </div>
          </div>
          
          <!-- Mobile menu button -->
          <div class="md:hidden flex items-center">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white hover:text-white focus:outline-none">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="md:hidden" v-show="mobileMenuOpen">
          <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <template v-if="isAuthenticated">
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/dashboard">Dashboard</router-link>
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/categories">Categories</router-link>
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/projects">Projects</router-link>
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/tasks">Tasks</router-link>
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/roles">Roles</router-link>
              <router-link v-if="isAdmin" class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/users">Users</router-link>
              <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-5">
                  <div class="flex-shrink-0">
                    <svg class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <div class="text-base font-medium text-white">{{ user ? user.name : 'User' }}</div>
                    <div class="text-sm font-medium text-gray-300">{{ user ? user.email : '' }}</div>
                  </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                  <button @click.prevent="logout" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-white bg-red-600 hover:bg-red-700">
                    Logout
                  </button>
                </div>
              </div>
            </template>
            <template v-else>
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/login">Login</router-link>
              <router-link class="text-white block px-3 py-2 rounded-md text-base font-medium" to="/register">Register</router-link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
  name: 'App',
  data() {
    return {
      mobileMenuOpen: false
    };
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'isAdmin', 'user'])
  },
  methods: {
    ...mapActions('auth', ['logout']),
  },
  created() {
    if (this.isAuthenticated) {
      this.$store.dispatch('auth/fetchUser').catch(() => {
        this.logout();
      });
    }
  }
};
</script>

<style>
body {
  background-color: #f8f9fa;
}
</style>
