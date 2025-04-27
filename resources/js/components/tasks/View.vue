<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>View Task</h2>
      <div>
        <router-link :to="{ name: 'tasks.edit', params: { id } }" class="btn btn-primary me-2">Edit</router-link>
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
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 150px;">ID</th>
                  <td>{{ task.id }}</td>
                </tr>
                <tr>
                  <th>UUID</th>
                  <td>{{ task.uuid }}</td>
                </tr>
                <tr>
                  <th>Title</th>
                  <td>{{ task.title }}</td>
                </tr>
                <tr>
                  <th>Project</th>
                  <td>
                    <router-link :to="{ name: 'projects.view', params: { id: task.project.id } }">
                      {{ task.project.name }}
                    </router-link>
                  </td>
                </tr>
                <tr>
                  <th>Description</th>
                  <td>{{ task.description || '-' }}</td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><span :class="getStatusBadgeClass(task.status)">{{ formatStatus(task.status) }}</span></td>
                </tr>
                <tr>
                  <th>Priority</th>
                  <td>
                    <span v-if="task.is_priority" class="badge bg-danger">Priority</span>
                    <span v-else>Normal</span>
                  </td>
                </tr>
                <tr>
                  <th>Due Date</th>
                  <td>{{ task.due_date ? new Date(task.due_date).toLocaleDateString() : '-' }}</td>
                </tr>
                <tr>
                  <th>Created At</th>
                  <td>{{ new Date(task.created_at).toLocaleString() }}</td>
                </tr>
                <tr>
                  <th>Updated At</th>
                  <td>{{ new Date(task.updated_at).toLocaleString() }}</td>
                </tr>
                <tr v-if="task.metadata">
                  <th>Metadata</th>
                  <td>
                    <pre class="mb-0">{{ formatJson(task.metadata) }}</pre>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card mt-4">
          <div class="card-header">Task Actions</div>
          <div class="card-body">
            <div class="d-flex">
              <button 
                class="btn btn-outline-primary me-2" 
                @click="updateStatus('pending')" 
                :disabled="task.status === 'pending'"
              >
                Mark as Pending
              </button>
              <button 
                class="btn btn-outline-primary me-2" 
                @click="updateStatus('in_progress')" 
                :disabled="task.status === 'in_progress'"
              >
                Mark as In Progress
              </button>
              <button 
                class="btn btn-outline-success me-2" 
                @click="updateStatus('completed')" 
                :disabled="task.status === 'completed'"
              >
                Mark as Completed
              </button>
              <button 
                class="btn btn-outline-danger" 
                @click="updateStatus('cancelled')" 
                :disabled="task.status === 'cancelled'"
              >
                Mark as Cancelled
              </button>
            </div>
            <div class="mt-3">
              <button 
                class="btn btn-outline-warning me-2" 
                @click="togglePriority" 
              >
                {{ task.is_priority ? 'Remove Priority' : 'Mark as Priority' }}
              </button>
              <button class="btn btn-danger" @click="confirmDelete">
                Delete Task
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" ref="deleteModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this task? This action cannot be undone.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteTask">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'TaskView',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      task: {
        project: {}
      },
      audits: [],
      loading: true,
      auditsLoading: true,
      deleteModal: null
    };
  },
  methods: {
    async fetchTask() {
      this.loading = true;
      try {
        await this.$store.dispatch('tasks/fetchTask', this.id);
        this.task = this.$store.state.tasks.task;
      } catch (error) {
        console.error('Error fetching task:', error);
      } finally {
        this.loading = false;
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
    },
    async updateStatus(status) {
      try {
        const updatedTask = { ...this.task, status };
        await this.$store.dispatch('tasks/updateTask', {
          id: this.id,
          task: updatedTask
        });
        this.fetchTask();
      } catch (error) {
        console.error('Error updating task status:', error);
        alert('Failed to update task status. ' + (error.response?.data?.message || ''));
      }
    },
    async togglePriority() {
      try {
        const updatedTask = { ...this.task, is_priority: !this.task.is_priority };
        await this.$store.dispatch('tasks/updateTask', {
          id: this.id,
          task: updatedTask
        });
        this.fetchTask();
      } catch (error) {
        console.error('Error updating task priority:', error);
        alert('Failed to update task priority. ' + (error.response?.data?.message || ''));
      }
    },
    confirmDelete() {
      this.deleteModal.show();
    },
    async deleteTask() {
      try {
        await this.$store.dispatch('tasks/deleteTask', this.id);
        this.deleteModal.hide();
        this.$router.push('/tasks');
      } catch (error) {
        console.error('Error deleting task:', error);
        alert('Failed to delete task. ' + (error.response?.data?.message || ''));
      }
    }
  },
  mounted() {
    this.fetchTask();
    this.fetchAudits();
    this.deleteModal = new Modal(this.$refs.deleteModal);
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
