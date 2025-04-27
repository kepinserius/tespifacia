<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Create Role</h2>
      <router-link to="/roles" class="btn btn-secondary">Back to Roles</router-link>
    </div>

    <div class="card">
      <div class="card-header">Role Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="createRole">
          <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="name" v-model="form.name" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div v-if="loading" class="text-center my-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="ms-2">Loading permissions...</span>
            </div>
            <div v-else class="row">
              <div v-for="permission in permissions" :key="permission.id" class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" :id="'permission-' + permission.id" 
                         :value="permission.id" v-model="form.permissions">
                  <label class="form-check-label" :for="'permission-' + permission.id">
                    {{ permission.name }}
                  </label>
                </div>
              </div>
            </div>
            <div v-if="permissions.length === 0" class="alert alert-info mt-2">
              No permissions available.
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Create Role
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RoleCreate',
  data() {
    return {
      form: {
        name: '',
        permissions: []
      },
      permissions: [],
      loading: true,
      submitting: false,
      error: null
    };
  },
  methods: {
    async fetchPermissions() {
      this.loading = true;
      try {
        await this.$store.dispatch('roles/fetchPermissions');
        this.permissions = this.$store.state.roles.permissions;
      } catch (error) {
        console.error('Error fetching permissions:', error);
        this.error = 'Failed to load permissions. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    async createRole() {
      this.submitting = true;
      this.error = null;
      
      try {
        await this.$store.dispatch('roles/createRole', this.form);
        this.$router.push({ name: 'roles.index' });
      } catch (error) {
        console.error('Error creating role:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to create role';
          }
        } else {
          this.error = 'An error occurred while creating the role';
        }
      } finally {
        this.submitting = false;
      }
    }
  },
  mounted() {
    this.fetchPermissions();
  }
};
</script>
