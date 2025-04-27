<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Register</div>
        <div class="card-body">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>
          
          <form @submit.prevent="register">
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <input type="text" class="form-control" id="name" v-model="form.name" required>
            </div>
            
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" v-model="form.email" required>
            </div>
            
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" v-model="form.password" required>
            </div>
            
            <div class="mb-3">
              <label for="password_confirmation" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="password_confirmation" v-model="form.password_confirmation" required>
            </div>
            
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Register
            </button>
            
            <div class="mt-3">
              <p>Already have an account? <router-link to="/login">Login</router-link></p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      error: null,
      loading: false
    };
  },
  methods: {
    async register() {
      this.loading = true;
      this.error = null;
      
      try {
        await this.$store.dispatch('auth/register', this.form);
        this.$router.push({ name: 'dashboard' });
      } catch (error) {
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Registration failed';
          }
        } else {
          this.error = 'An error occurred during registration';
        }
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
