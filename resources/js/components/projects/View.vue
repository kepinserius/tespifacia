<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>View Project</h2>
      <div>
        <router-link :to="{ name: 'projects.edit', params: { id } }" class="btn btn-primary me-2">Edit</router-link>
        <router-link to="/projects" class="btn btn-secondary">Back to Projects</router-link>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading project information...</p>
    </div>

    <div v-else class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Project Information</div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 150px;">ID</th>
                  <td>{{ project.id }}</td>
                </tr>
                <tr>
                  <th>UUID</th>
                  <td>{{ project.uuid }}</td>
                </tr>
                <tr>
                  <th>Name</th>
                  <td>{{ project.name }}</td>
                </tr>
                <tr>
                  <th>Category</th>
                  <td>
                    <router-link :to="{ name: 'categories.view', params: { id: project.category.id } }">
                      {{ project.category.name }}
                    </router-link>
                  </td>
                </tr>
                <tr>
                  <th>Description</th>
                  <td>{{ project.description || '-' }}</td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><span :class="'badge ' + (project.is_active ? 'bg-success' : 'bg-danger')">{{ project.is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                <tr>
                  <th>Start Date</th>
                  <td>{{ project.start_date ? new Date(project.start_date).toLocaleDateString() : '-' }}</td>
                </tr>
                <tr>
                  <th>End Date</th>
                  <td>{{ project.end_date ? new Date(project.end_date).toLocaleDateString() : '-' }}</td>
                </tr>
                <tr>
                  <th>Document</th>
                  <td>
                    <a v-if="project.document_path" :href="'/storage/' + project.document_path" target="_blank" class="btn btn-sm btn-outline-primary">
                      View Document
                    </a>
                    <span v-else>No document</span>
                  </td>
                </tr>
                <tr>
                  <th>Created At</th>
                  <td>{{ new Date(project.created_at).toLocaleString() }}</td>
                </tr>
                <tr>
                  <th>Updated At</th>
                  <td>{{ new Date(project.updated_at).toLocaleString() }}</td>
                </tr>
                <tr v-if="project.metadata">
                  <th>Metadata</th>
                  <td>
                    <pre class="mb-0">{{ formatJson(project.metadata) }}</pre>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Tasks</span>
            <router-link :to="{ name: 'tasks.create', query: { project_id: project.id } }" class="btn btn-sm btn-primary">
              Add Task
            </router-link>
          </div>
          <div class="card-body">
            <div v-if="!project.tasks || project.tasks.length === 0" class="alert alert-info">
              No tasks associated with this project.
            </div>
            <div v-else class="list-group">
              <router-link 
                v-for="task in project.tasks" 
                :key="task.id" 
                :to="{ name: 'tasks.view', params: { id: task.id } }" 
                class="list-group-item list-group-item-action"
              >
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1">{{ task.title }}</h5>
                  <small :class="getStatusBadgeClass(task.status)">{{ formatStatus(task.status) }}</small>
                </div>
                <p class="mb-1">{{ task.description ? (task.description.length > 100 ? task.description.substring(0, 100) + '...' : task.description) : 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center">
                  <small>Due: {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Not set' }}</small>
                  <span v-if="task.is_priority" class="badge bg-danger">Priority</span>
                </div>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 mt-4">
        <div class="card">
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
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProjectView',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      project: {},
      audits: [],
      loading: true,
      auditsLoading: true
    };
  },
  methods: {
    async fetchProject() {
      this.loading = true;
      try {
        await this.$store.dispatch('projects/fetchProject', this.id);
        this.project = this.$store.state.projects.project;
      } catch (error) {
        console.error('Error fetching project:', error);
      } finally {
        this.loading = false;
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
    },
    getStatusBadgeClass(status) {
      switch (status) {
        case 'pending':
          return 'badge bg-secondary';
        case 'in_progress':
          return 'badge bg-primary';
        case 'completed':
          return 'badge bg-success';
        case 'cancelled':
          return 'badge bg-danger';
        default:
          return 'badge bg-secondary';
      }
    },
    formatStatus(status) {
      return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }
  },
  mounted() {
    this.fetchProject();
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
