<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Create Category</h2>
      <router-link to="/categories" class="btn btn-secondary">Back to Categories</router-link>
    </div>

    <div class="card">
      <div class="card-header">Category Information</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        
        <form @submit.prevent="createCategory">
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
            Create Category
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CategoryCreate',
  data() {
    return {
      form: {
        name: '',
        description: '',
        is_active: true,
        published_at: '',
        metadata: ''
      },
      submitting: false,
      error: null
    };
  },
  methods: {
    async createCategory() {
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
        
        await this.$store.dispatch('categories/createCategory', formData);
        this.$router.push({ name: 'categories.index' });
      } catch (error) {
        console.error('Error creating category:', error);
        if (error.response && error.response.data) {
          if (error.response.data.errors) {
            // Format validation errors
            const errors = Object.values(error.response.data.errors).flat();
            this.error = errors.join('<br>');
          } else {
            this.error = error.response.data.message || 'Failed to create category';
          }
        } else {
          this.error = 'An error occurred while creating the category';
        }
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>
