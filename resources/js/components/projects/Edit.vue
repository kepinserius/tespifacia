<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Edit Project</h2>
      <router-link to="/projects" class="btn btn-secondary">Back to Projects</router-link>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading project information...</p>
    </div>

    <div v-else class="card">
      <div class="card-header">Project Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="updateProject" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-select" id="category_id" v-model="form.category_id" required>
              <option value="" disabled>Select a category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <div v-if="categoriesLoading" class="text-center mt-2">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading categories...</span>
              </div>
              <span class="ms-2">Loading categories...</span>
            </div>
          </div>
          
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
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" v-model="form.start_date">
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="end_date" v-model="form.end_date">
            </div>
          </div>
          
          <div class="mb-3">
            <label for="document" class="form-label">Document (PDF only, 100KB - 500KB)</label>
            <div v-if="project.document_path" class="mb-2">
              <a :href="'/storage/' + project.document_path" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                View Current Document
              </a>
              <button type="button" class="btn btn-sm btn-outline-danger" @click="removeDocument">
                Remove Document
              </button>
            </div>
            <input type="file" class="form-control" id="document" @change="handleFileUpload" accept="application/pdf">
            <small class="text-muted">Upload a PDF document between 100KB and 500KB in size</small>
          </div>
          
          <div class="mb-3">
            <label for="metadata" class="form-label">Metadata (JSON)</label>
            <textarea class="form-control" id="metadata" v-model="form.metadata" rows="3" placeholder='{"key": "value"}'></textarea>
            <small class="text-muted">Enter valid JSON data</small>
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Update Project
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
          No audit history available for this project.
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
  name: 'ProjectEdit',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      project: {},
      form: {
        category_id: '',
        name: '',
        description: '',
        is_active: true,
        start_date: '',
        end_date: '',
        metadata: '',
        document: null,
        remove_document: false
      },
      categories: [],
      audits: [],
      loading: true,
      categoriesLoading: true,
      auditsLoading: true,
      submitting: false,
      error: null
    };
  },
  methods: {
    async fetchProject() {
      this.loading = true;
      try {
        await this.$store.dispatch('projects/fetchProject', this.id);
        this.project = this.$store.state.projects.project;
        
        // Format the data for the form
        this.form.category_id = this.project.category_id;
        this.form.name = this.project.name;
        this.form.description = this.project.description || '';
        this.form.is_active = this.project.is_active;
        
        if (this.project.start_date) {
          // Format date input
          const startDate = new Date(this.project.start_date);
          this.form.start_date = startDate.toISOString().split('T')[0];
        } else {
          this.form.start_date = '';
        }
        
        if (this.project.end_date) {
          // Format date input
          const endDate = new Date(this.project.end_date);
          this.form.end_date = endDate.toISOString().split('T')[0];
        } else {
          this.form.end_date = '';
        }
        
        if (this.project.metadata) {
          this.form.metadata = JSON.stringify(this.project.metadata, null, 2);
        } else {
          this.form.metadata = '';
        }
      } catch (error) {
        console.error('Error fetching project:', error);
        this.error = 'Failed to load project information. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    async fetchCategories() {
      this.categoriesLoading = true;
      try {
        await this.$store.dispatch('projects/fetchCategories');
        this.categories = this.$store.state.projects.categories;
      } catch (error) {
        console.error('Error fetching categories:', error);
        this.error = 'Failed to load categories. Please try again.';
      } finally {
        this.categoriesLoading = false;
      }
    },
    async fetchAudits() {
      this.auditsLoading = true;
      try {
        await this.$store.dispatch('projects/fetchAudits', this.id);
        this.audits = this.$store.state.projects.audits;
      } catch (error) {
        console.error('Error fetching audits:', error);
      } finally {
        this.auditsLoading = false;
      }
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        // Check file type
        if (file.type !== 'application/pdf') {
          this.error = 'Only PDF files are allowed';
          event.target.value = '';
          this.form.document = null;
          return;
        }
        
        // Check file size (100KB - 500KB)
        const fileSize = file.size / 1024; // Convert to KB
        if (fileSize < 100 || fileSize > 500) {
          this.error = 'File size must be between 100KB and 500KB';
          event.target.value = '';
          this.form.document = null;
          return;
        }
        
        this.form.document = file;
        this.form.remove_document = false;
        this.error = null;
      }
    },
    removeDocument() {
      this.form.document = null;
      this.form.remove_document = true;
    },
    async updateProject() {
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
        
        const formData = new FormData();
        
        // Add all form fields to FormData
        for (const key in this.form) {
          if (key === 'document' && this.form[key]) {
            formData.append(key, this.form[key]);
          } else if (key === 'metadata' && this.form[key]) {
            formData.append(key, this.form[key]);
          } else if (key === 'remove_document') {
            formData.append(key, this.form[key]);
          } else if (this.form[key] !== null && this.form[key] !== '') {
            formData.append(key, this.form[key]);
          }
        }
        
        await this.$store.dispatch('projects/updateProject', {
          id: this.id,
          data: formData
        });
        
        // Refresh the data
        await this.fetchProject();
        await this.fetchAudits();
        
        // Show success message
        alert('Project updated successfully');
      } catch (error) {
        console.error('Error updating project:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to update project';
          }
        } else {
          this.error = 'An error occurred while updating the project';
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
    this.fetchProject();
    this.fetchCategories();
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
