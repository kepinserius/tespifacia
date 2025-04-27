<template>
  <div class="flex justify-center items-center">
    <div class="w-full max-w-md">
      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white text-xl font-bold py-4 px-6">Login</div>
        <div class="p-6">
          <div v-if="errors.email" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ errors.email[0] }}
          </div>
          
          <form @submit.prevent="login">
            <div class="mb-4">
              <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
              <input 
                type="email" 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                id="email" 
                v-model="form.email" 
                required
              >
            </div>
            
            <div class="mb-4">
              <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
              <input 
                type="password" 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                id="password" 
                v-model="form.password" 
                required
              >
            </div>
            
            <div class="mb-4">
              <label class="flex items-center">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600" v-model="form.remember">
                <span class="ml-2 text-gray-700">Remember Me</span>
              </label>
            </div>
            
            <div class="flex items-center justify-between">
              <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                :disabled="loading"
              >
                <span v-if="loading" class="inline-block animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                Login
              </button>
            </div>
            
            <div class="mt-6 text-center">
              <p class="text-gray-600">Don't have an account? 
                <router-link to="/register" class="text-blue-600 hover:text-blue-800 font-semibold">Register</router-link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      },
      errors: {},
      loading: false,
      debugInfo: ''
    }
  },
  mounted() {
    // Clear any previous auth state on login page load
    this.$store.commit('auth/SET_AUTHENTICATED', false);
    this.$store.commit('auth/SET_USER', null);
    
    // Add debug info
    this.debugInfo = 'Login page loaded. Auth state cleared.';
    console.log('Login page loaded. Auth state cleared.');
  },
  methods: {
    async login() {
      this.loading = true;
      this.errors = {};
      this.debugInfo = 'Login attempt started...';
      console.log('Login attempt started with:', this.form.email);
      
      try {
        // Ensure we're using the latest CSRF token
        await axios.get('/sanctum/csrf-cookie');
        this.debugInfo += '\nCSRF token refreshed.';
        console.log('CSRF token refreshed');
        
        // Attempt login
        await this.$store.dispatch('auth/login', this.form);
        this.debugInfo += '\nLogin successful, redirecting...';
        console.log('Login successful, redirecting to dashboard');
        this.$router.push({ name: 'dashboard' });
      } catch (error) {
        console.error('Login error:', error);
        this.debugInfo += '\nLogin error: ' + (error.message || 'Unknown error');
        
        if (error.response && error.response.data) {
          console.error('Server response:', error.response.data);
          this.debugInfo += '\nServer response: ' + JSON.stringify(error.response.data);
          
          if (error.response.data.errors) {
            this.errors = error.response.data.errors;
          } else if (error.response.data.message) {
            this.errors = {
              email: [error.response.data.message]
            };
          } else {
            this.errors = {
              email: ['An error occurred during login.']
            };
          }
        } else {
          this.errors = {
            email: ['An error occurred during login. Please try again.']
          };
        }
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
