<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Create User</h2>
      <router-link to="/users" class="btn btn-secondary">Back to Users</router-link>
    </div>

    <div class="card">
      <div class="card-header">User Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="createUser">
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
          
          <div class="mb-3">
            <label class="form-label">Roles</label>
            <div v-if="loading" class="text-center my-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="ms-2">Loading roles...</span>
            </div>
            <div v-else class="row">
              <div v-for="role in roles" :key="role.id" class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" :id="'role-' + role.id" 
                         :value="role.id" v-model="form.roles">
                  <label class="form-check-label" :for="'role-' + role.id">
                    {{ role.name }}
                  </label>
                </div>
              </div>
            </div>
            <div v-if="roles.length === 0" class="alert alert-info mt-2">
              No roles available.
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Create User
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserCreate',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: []
      },
      roles: [],
      loading: true,
      submitting: false,
      error: null
    };
  },
  methods: {
    async fetchRoles() {
      this.loading = true;
      try {
        await this.$store.dispatch('users/fetchAllRoles');
        this.roles = this.$store.state.users.allRoles;
      } catch (error) {
        console.error('Error fetching roles:', error);
        this.error = 'Failed to load roles. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    async createUser() {
      this.submitting = true;
      this.error = null;
      
      try {
        await this.$store.dispatch('users/createUser', this.form);
        this.$router.push({ name: 'users.index' });
      } catch (error) {
        console.error('Error creating user:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to create user';
          }
        } else {
          this.error = 'An error occurred while creating the user';
        }
      } finally {
        this.submitting = false;
      }
    }
  },
  mounted() {
    this.fetchRoles();
  }
};
</script>
