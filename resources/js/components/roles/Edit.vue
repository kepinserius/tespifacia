<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Edit Role</h2>
      <router-link to="/roles" class="btn btn-secondary">Back to Roles</router-link>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading role information...</p>
    </div>

    <div v-else class="card">
      <div class="card-header">Role Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="updateRole">
          <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="name" v-model="form.name" required :disabled="role.name === 'admin'">
            <small v-if="role.name === 'admin'" class="text-muted">The admin role name cannot be changed.</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div v-if="permissionsLoading" class="text-center my-3">
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
            Update Role
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RoleEdit',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      role: {},
      form: {
        name: '',
        permissions: []
      },
      permissions: [],
      loading: true,
      permissionsLoading: true,
      submitting: false,
      error: null
    };
  },
  methods: {
    async fetchRole() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/roles/${this.id}`);
        this.role = response.data;
        this.form.name = this.role.name;
        this.form.permissions = this.role.permissions.map(p => p.id);
      } catch (error) {
        console.error('Error fetching role:', error);
        this.error = 'Failed to load role information. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    async fetchPermissions() {
      this.permissionsLoading = true;
      try {
        await this.$store.dispatch('roles/fetchPermissions');
        this.permissions = this.$store.state.roles.permissions;
      } catch (error) {
        console.error('Error fetching permissions:', error);
        this.error = 'Failed to load permissions. Please try again.';
      } finally {
        this.permissionsLoading = false;
      }
    },
    async updateRole() {
      this.submitting = true;
      this.error = null;
      
      try {
        await this.$store.dispatch('roles/updateRole', {
          id: this.id,
          data: this.form
        });
        this.$router.push({ name: 'roles.index' });
      } catch (error) {
        console.error('Error updating role:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to update role';
          }
        } else {
          this.error = 'An error occurred while updating the role';
        }
      } finally {
        this.submitting = false;
      }
    }
  },
  mounted() {
    this.fetchRole();
    this.fetchPermissions();
  }
};
</script>
