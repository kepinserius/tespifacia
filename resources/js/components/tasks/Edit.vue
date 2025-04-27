<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Edit Task</h2>
      <div>
        <router-link :to="{ name: 'tasks.view', params: { id } }" class="btn btn-info me-2">View Task</router-link>
        <router-link to="/tasks" class="btn btn-secondary">Back to Tasks</router-link>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading task information...</p>
    </div>

    <div v-else class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Task Information</div>
          <div class="card-body">
            <form @submit.prevent="updateTask">
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
                <button type="submit" class="btn btn-primary" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  Update Task
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-header">Task Details</div>
          <div class="card-body">
            <p><strong>ID:</strong> {{ task.id }}</p>
            <p><strong>UUID:</strong> {{ task.uuid }}</p>
            <p><strong>Created:</strong> {{ task.created_at ? new Date(task.created_at).toLocaleString() : '-' }}</p>
            <p><strong>Updated:</strong> {{ task.updated_at ? new Date(task.updated_at).toLocaleString() : '-' }}</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">Audit History</div>
          <div class="card-body">
            <div v-if="auditsLoading" class="text-center my-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading audit history...</p>
            </div>
            <div v-else-if="audits.length === 0" class="alert alert-info">
              No audit history available for this task.
            </div>
            <div v-else>
              <div v-for="(audit, index) in audits" :key="audit.id" class="mb-3">
                <div class="d-flex justify-content-between">
                  <span :class="getEventBadgeClass(audit.event)">{{ audit.event }}</span>
                  <small>{{ new Date(audit.created_at).toLocaleString() }}</small>
                </div>
                <div v-if="audit.user" class="small text-muted">By: {{ audit.user.name }}</div>
                <div class="mt-2">
                  <div v-if="audit.old_values && Object.keys(audit.old_values).length > 0">
                    <strong>Changed from:</strong>
                    <pre class="small">{{ formatJson(audit.old_values) }}</pre>
                  </div>
                  <div v-if="audit.new_values && Object.keys(audit.new_values).length > 0">
                    <strong>Changed to:</strong>
                    <pre class="small">{{ formatJson(audit.new_values) }}</pre>
                  </div>
                </div>
                <hr v-if="index < audits.length - 1">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskEdit',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      task: {
        title: '',
        project_id: '',
        description: '',
        status: '',
        due_date: '',
        is_priority: false,
        metadata: {}
      },
      metadataJson: '',
      projects: [],
      audits: [],
      loading: true,
      saving: false,
      auditsLoading: true,
      errors: {},
      metadataError: ''
    };
  },
  methods: {
    async fetchTask() {
      this.loading = true;
      try {
        await this.$store.dispatch('tasks/fetchTask', this.id);
        this.task = { ...this.$store.state.tasks.task };
        
        // Format date for input
        if (this.task.due_date) {
          const date = new Date(this.task.due_date);
          this.task.due_date = date.toISOString().split('T')[0];
        }
        
        // Format metadata for editing
        if (this.task.metadata) {
          this.metadataJson = JSON.stringify(this.task.metadata, null, 2);
        }
      } catch (error) {
        console.error('Error fetching task:', error);
      } finally {
        this.loading = false;
      }
    },
    async fetchProjects() {
      try {
        await this.$store.dispatch('tasks/fetchProjects');
        this.projects = this.$store.state.tasks.projects;
      } catch (error) {
        console.error('Error fetching projects:', error);
      }
    },
    async fetchAudits() {
      this.auditsLoading = true;
      try {
        await this.$store.dispatch('tasks/fetchAudits', this.id);
        this.audits = this.$store.state.tasks.audits;
      } catch (error) {
        console.error('Error fetching audits:', error);
      } finally {
        this.auditsLoading = false;
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
    async updateTask() {
      if (!this.validateMetadata()) {
        return;
      }
      
      this.saving = true;
      this.errors = {};
      
      try {
        await this.$store.dispatch('tasks/updateTask', {
          id: this.id,
          task: this.task
        });
        this.$router.push({ name: 'tasks.view', params: { id: this.id } });
      } catch (error) {
        console.error('Error updating task:', error);
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          alert('Failed to update task. ' + (error.response?.data?.message || ''));
        }
      } finally {
        this.saving = false;
      }
    },
    formatJson(jsonData) {
      try {
        if (typeof jsonData === 'string') {
          return JSON.stringify(JSON.parse(jsonData), null, 2);
        } else {
          return JSON.stringify(jsonData, null, 2);
        }
      } catch (e) {
        return jsonData;
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
    this.fetchTask();
    this.fetchProjects();
    this.fetchAudits();
  }
};
</script>

<style scoped>
pre {
  background-color: #f8f9fa;
  border-radius: 4px;
  padding: 8px;
  font-size: 0.75rem;
  max-height: 150px;
  overflow-y: auto;
}
</style>
