<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Edit Category</h2>
      <router-link to="/categories" class="btn btn-secondary">Back to Categories</router-link>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading category information...</p>
    </div>

    <div v-else class="card">
      <div class="card-header">Category Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="updateCategory">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" v-model="form.name" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" v-model="form.description" rows="3"></textarea>
          </div>
          
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" v-model="form.is_active">
            <label class="form-check-label" for="is_active">Active</label>
          </div>
          
          <div class="mb-3">
            <label for="published_at" class="form-label">Published At</label>
            <input type="datetime-local" class="form-control" id="published_at" v-model="form.published_at">
          </div>
          
          <div class="mb-3">
            <label for="metadata" class="form-label">Metadata (JSON)</label>
            <textarea class="form-control" id="metadata" v-model="form.metadata" rows="3" placeholder='{"key": "value"}'></textarea>
            <small class="text-muted">Enter valid JSON data</small>
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Update Category
          </button>
        </form>
      </div>
    </div>

    <div v-if="!loading" class="card mt-4">
      <div class="card-header">Audit History</div>
      <div class="card-body">
        <div v-if="auditsLoading" class="text-center my-3">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading audit history...</p>
        </div>
        <div v-else-if="audits.length === 0" class="alert alert-info">
          No audit history available for this category.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Date</th>
                <th>User</th>
                <th>Event</th>
                <th>Old Values</th>
                <th>New Values</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="audit in audits" :key="audit.id">
                <td>{{ new Date(audit.created_at).toLocaleString() }}</td>
                <td>{{ audit.user ? audit.user.name : 'System' }}</td>
                <td><span :class="getEventBadgeClass(audit.event)">{{ audit.event }}</span></td>
                <td>
                  <pre v-if="audit.old_values" class="mb-0" style="max-height: 100px; overflow-y: auto;">{{ formatJson(audit.old_values) }}</pre>
                  <span v-else>-</span>
                </td>
                <td>
                  <pre v-if="audit.new_values" class="mb-0" style="max-height: 100px; overflow-y: auto;">{{ formatJson(audit.new_values) }}</pre>
                  <span v-else>-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CategoryEdit',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      category: {},
      form: {
        name: '',
        description: '',
        is_active: true,
        published_at: '',
        metadata: ''
      },
      audits: [],
      loading: true,
      auditsLoading: true,
      submitting: false,
      error: null
    };
  },
  methods: {
    async fetchCategory() {
      this.loading = true;
      try {
        await this.$store.dispatch('categories/fetchCategory', this.id);
        this.category = this.$store.state.categories.category;
        
        // Format the data for the form
        this.form.name = this.category.name;
        this.form.description = this.category.description || '';
        this.form.is_active = this.category.is_active;
        
        if (this.category.published_at) {
          // Format datetime-local input
          const date = new Date(this.category.published_at);
          this.form.published_at = date.toISOString().slice(0, 16);
        } else {
          this.form.published_at = '';
        }
        
        if (this.category.metadata) {
          this.form.metadata = JSON.stringify(this.category.metadata, null, 2);
        } else {
          this.form.metadata = '';
        }
      } catch (error) {
        console.error('Error fetching category:', error);
        this.error = 'Failed to load category information. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    async fetchAudits() {
      this.auditsLoading = true;
      try {
        await this.$store.dispatch('categories/fetchAudits', this.id);
        this.audits = this.$store.state.categories.audits;
      } catch (error) {
        console.error('Error fetching audits:', error);
      } finally {
        this.auditsLoading = false;
      }
    },
    async updateCategory() {
      this.submitting = true;
      this.error = null;
      
      try {
        // Validate JSON if provided
        if (this.form.metadata) {
          try {
            JSON.parse(this.form.metadata);
          } catch (e) {
            this.error = 'Invalid JSON in metadata field';
            this.submitting = false;
            return;
          }
        }
        
        const formData = { ...this.form };
        
        // Format the data
        if (formData.metadata) {
          formData.metadata = JSON.parse(formData.metadata);
        }
        
        await this.$store.dispatch('categories/updateCategory', {
          id: this.id,
          data: formData
        });
        
        // Refresh the data
        await this.fetchCategory();
        await this.fetchAudits();
        
        // Show success message
        alert('Category updated successfully');
      } catch (error) {
        console.error('Error updating category:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to update category';
          }
        } else {
          this.error = 'An error occurred while updating the category';
        }
      } finally {
        this.submitting = false;
      }
    },
    formatJson(jsonString) {
      try {
        if (typeof jsonString === 'string') {
          return JSON.stringify(JSON.parse(jsonString), null, 2);
        } else {
          return JSON.stringify(jsonString, null, 2);
        }
      } catch (e) {
        return jsonString;
      }
    },
    getEventBadgeClass(event) {
      switch (event) {
        case 'created':
          return 'badge bg-success';
        case 'updated':
          return 'badge bg-primary';
        case 'deleted':
          return 'badge bg-danger';
        case 'restored':
          return 'badge bg-warning';
        default:
          return 'badge bg-secondary';
      }
    }
  },
  mounted() {
    this.fetchCategory();
    this.fetchAudits();
  }
};
</script>

<style scoped>
pre {
  background-color: #f8f9fa;
  border-radius: 4px;
  padding: 8px;
  font-size: 0.875rem;
}
</style>
