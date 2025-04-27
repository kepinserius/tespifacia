<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Edit User</h2>
      <router-link to="/users" class="btn btn-secondary">Back to Users</router-link>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading user information...</p>
    </div>

    <div v-else class="card">
      <div class="card-header">User Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="updateUser">
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
            <input type="password" class="form-control" id="password" v-model="form.password" placeholder="Leave blank to keep current password">
            <small class="text-muted">Leave blank to keep current password</small>
          </div>
          
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" v-model="form.password_confirmation" placeholder="Leave blank to keep current password">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Roles</label>
            <div v-if="rolesLoading" class="text-center my-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="ms-2">Loading roles...</span>
            </div>
            <div v-else class="row">
              <div v-for="role in roles" :key="role.id" class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" :id="'role-' + role.id" 
                         :value="role.id" v-model="form.roles"
                         :disabled="isLastAdminUser && role.name === 'admin'">
                  <label class="form-check-label" :for="'role-' + role.id">
                    {{ role.name }}
                  </label>
                </div>
              </div>
            </div>
            <div v-if="isLastAdminUser" class="alert alert-warning mt-2">
              This is the last admin user. The admin role cannot be removed.
            </div>
            <div v-if="roles.length === 0" class="alert alert-info mt-2">
              No roles available.
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Update User
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserEdit',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      user: {},
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: []
      },
      roles: [],
      loading: true,
      rolesLoading: true,
      submitting: false,
      error: null,
      isLastAdminUser: false
    };
  },
  methods: {
    async fetchUser() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/users/${this.id}`);
        this.user = response.data;
        this.form.name = this.user.name;
        this.form.email = this.user.email;
        this.form.roles = this.user.roles.map(r => r.id);
        
        // Check if this is the last admin user
        if (this.user.roles.some(role => role.name === 'admin')) {
          const usersResponse = await axios.get('/api/users');
          const adminUsers = usersResponse.data.data.filter(u => 
            u.roles.some(role => role.name === 'admin')
          );
          this.isLastAdminUser = adminUsers.length <= 1;
        }
      } catch (error) {
        console.error('Error fetching user:', error);
        this.error = 'Failed to load user information. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    async fetchRoles() {
      this.rolesLoading = true;
      try {
        await this.$store.dispatch('users/fetchAllRoles');
        this.roles = this.$store.state.users.allRoles;
      } catch (error) {
        console.error('Error fetching roles:', error);
        this.error = 'Failed to load roles. Please try again.';
      } finally {
        this.rolesLoading = false;
      }
    },
    async updateUser() {
      this.submitting = true;
      this.error = null;
      
      // If this is the last admin user, ensure the admin role is included
      if (this.isLastAdminUser) {
        const adminRole = this.roles.find(role => role.name === 'admin');
        if (adminRole && !this.form.roles.includes(adminRole.id)) {
          this.form.roles.push(adminRole.id);
        }
      }
      
      try {
        await this.$store.dispatch('users/updateUser', {
          id: this.id,
          data: this.form
        });
        this.$router.push({ name: 'users.index' });
      } catch (error) {
        console.error('Error updating user:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to update user';
          }
        } else {
          this.error = 'An error occurred while updating the user';
        }
      } finally {
        this.submitting = false;
      }
    }
  },
  mounted() {
    this.fetchUser();
    this.fetchRoles();
  }
};
</script>
