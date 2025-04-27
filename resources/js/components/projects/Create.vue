<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Create Project</h2>
      <router-link to="/projects" class="btn btn-secondary">Back to Projects</router-link>
    </div>

    <div class="card">
      <div class="card-header">Project Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="createProject" enctype="multipart/form-data">
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
            Create Project
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProjectCreate',
  data() {
    return {
      form: {
        category_id: '',
        name: '',
        description: '',
        is_active: true,
        start_date: '',
        end_date: '',
        metadata: '',
        document: null
      },
      categories: [],
      categoriesLoading: true,
      submitting: false,
      error: null
    };
  },
  methods: {
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
        this.error = null;
      }
    },
    async createProject() {
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
          } else if (this.form[key] !== null && this.form[key] !== '') {
            formData.append(key, this.form[key]);
          }
        }
        
        await this.$store.dispatch('projects/createProject', formData);
        this.$router.push({ name: 'projects.index' });
      } catch (error) {
        console.error('Error creating project:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to create project';
          }
        } else {
          this.error = 'An error occurred while creating the project';
        }
      } finally {
        this.submitting = false;
      }
    }
  },
  mounted() {
    this.fetchCategories();
  }
};
</script>
