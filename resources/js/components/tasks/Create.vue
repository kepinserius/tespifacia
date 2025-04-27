<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Create New Task</h2>
      <router-link to="/tasks" class="btn btn-secondary">Back to Tasks</router-link>
    </div>

    <div class="card">
      <div class="card-header">Task Information</div>
      <div class="card-body">
        <form @submit.prevent="saveTask">
          <div class="mb-3">
            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" v-model="task.title" required>
            <div v-if="errors.title" class="text-danger mt-1">{{ errors.title[0] }}</div>
          </div>

          <div class="mb-3">
            <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
            <select class="form-select" id="project_id" v-model="task.project_id" required>
              <option value="" disabled>Select a project</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.name }}
              </option>
            </select>
            <div v-if="errors.project_id" class="text-danger mt-1">{{ errors.project_id[0] }}</div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" v-model="task.description" rows="3"></textarea>
            <div v-if="errors.description" class="text-danger mt-1">{{ errors.description[0] }}</div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" v-model="task.status" required>
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <div v-if="errors.status" class="text-danger mt-1">{{ errors.status[0] }}</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" v-model="task.due_date">
                <div v-if="errors.due_date" class="text-danger mt-1">{{ errors.due_date[0] }}</div>
              </div>
            </div>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_priority" v-model="task.is_priority">
            <label class="form-check-label" for="is_priority">Priority Task</label>
            <div v-if="errors.is_priority" class="text-danger mt-1">{{ errors.is_priority[0] }}</div>
          </div>

          <div class="mb-3">
            <label for="metadata" class="form-label">Metadata (JSON)</label>
            <textarea class="form-control" id="metadata" v-model="metadataJson" rows="3" placeholder='{"key": "value"}'></textarea>
            <div v-if="errors.metadata" class="text-danger mt-1">{{ errors.metadata[0] }}</div>
            <div v-if="metadataError" class="text-danger mt-1">{{ metadataError }}</div>
            <small class="form-text text-muted">Enter valid JSON metadata for this task</small>
          </div>

          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-2" @click="$router.push('/tasks')">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Save Task
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskCreate',
  data() {
    return {
      task: {
        title: '',
        project_id: this.$route.query.project_id || '',
        description: '',
        status: 'pending',
        due_date: '',
        is_priority: false,
        metadata: {}
      },
      metadataJson: '',
      projects: [],
      loading: false,
      errors: {},
      metadataError: ''
    };
  },
  methods: {
    async fetchProjects() {
      try {
        await this.$store.dispatch('tasks/fetchProjects');
        this.projects = this.$store.state.tasks.projects;
      } catch (error) {
        console.error('Error fetching projects:', error);
      }
    },
    validateMetadata() {
      if (!this.metadataJson.trim()) {
        this.task.metadata = {};
        return true;
      }
      
      try {
        this.task.metadata = JSON.parse(this.metadataJson);
        this.metadataError = '';
        return true;
      } catch (error) {
        this.metadataError = 'Invalid JSON format';
        return false;
      }
    },
    async saveTask() {
      if (!this.validateMetadata()) {
        return;
      }
      
      this.loading = true;
      this.errors = {};
      
      try {
        await this.$store.dispatch('tasks/createTask', this.task);
        this.$router.push('/tasks');
      } catch (error) {
        console.error('Error creating task:', error);
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          alert('Failed to create task. ' + (error.response?.data?.message || ''));
        }
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.fetchProjects();
  }
};
</script>
