<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Task Management</h2>
      <div>
        <div class="btn-group me-2">
          <button class="btn btn-success" @click="exportExcel">Export Excel</button>
          <button class="btn btn-outline-success" @click="queueExport">Queue Export</button>
        </div>
        <label for="importFile" class="btn btn-info me-2">Import Excel</label>
        <input type="file" id="importFile" class="d-none" @change="importExcel" accept=".xlsx,.xls,.csv">
        <router-link to="/tasks/create" class="btn btn-primary">Create New Task</router-link>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-3">
            <div class="input-group">
              <input type="text" class="form-control" v-model="search" placeholder="Search tasks..." @input="searchTasks">
              <button class="btn btn-outline-secondary" type="button" @click="searchTasks">Search</button>
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="projectFilter" @change="searchTasks">
              <option value="">All Projects</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.name }}
              </option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="statusFilter" @change="searchTasks">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="priorityFilter" @change="searchTasks">
              <option value="">All Priority</option>
              <option value="1">Priority</option>
              <option value="0">Normal</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="perPage" @change="fetchTasks">
              <option value="10">10 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
              <option value="100">100 per page</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="tasks.data && tasks.data.length === 0" class="text-center my-5">
          <p>No tasks found.</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th @click="sortBy('id')" class="sortable">
                  ID
                  <i v-if="sortColumn === 'id'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('title')" class="sortable">
                  Title
                  <i v-if="sortColumn === 'title'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('project_id')" class="sortable">
                  Project
                  <i v-if="sortColumn === 'project_id'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('status')" class="sortable">
                  Status
                  <i v-if="sortColumn === 'status'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('is_priority')" class="sortable">
                  Priority
                  <i v-if="sortColumn === 'is_priority'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('due_date')" class="sortable">
                  Due Date
                  <i v-if="sortColumn === 'due_date'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="task in tasks.data" :key="task.id">
                <td>{{ task.id }}</td>
                <td>{{ task.title }}</td>
                <td>
                  <router-link :to="{ name: 'projects.view', params: { id: task.project.id } }">
                    {{ task.project.name }}
                  </router-link>
                </td>
                <td><span :class="getStatusBadgeClass(task.status)">{{ formatStatus(task.status) }}</span></td>
                <td>
                  <span v-if="task.is_priority" class="badge bg-danger">Priority</span>
                  <span v-else>Normal</span>
                </td>
                <td>{{ task.due_date ? new Date(task.due_date).toLocaleDateString() : '-' }}</td>
                <td>
                  <router-link :to="{ name: 'tasks.view', params: { id: task.id } }" class="btn btn-sm btn-info me-1">View</router-link>
                  <router-link :to="{ name: 'tasks.edit', params: { id: task.id } }" class="btn btn-sm btn-primary me-1">Edit</router-link>
                  <button @click="confirmDelete(task)" class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="tasks.data && tasks.data.length > 0" class="d-flex justify-content-center mt-4">
          <nav aria-label="Page navigation">
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: tasks.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(1)">First</a>
              </li>
              <li class="page-item" :class="{ disabled: tasks.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(tasks.current_page - 1)">Previous</a>
              </li>
              <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: tasks.current_page === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: tasks.current_page === tasks.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(tasks.current_page + 1)">Next</a>
              </li>
              <li class="page-item" :class="{ disabled: tasks.current_page === tasks.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(tasks.last_page)">Last</a>
              </li>
            </ul>
          </nav>
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
            Are you sure you want to delete the task "{{ selectedTask ? selectedTask.title : '' }}"?
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
  name: 'TaskIndex',
  data() {
    return {
      tasks: {
        data: []
      },
      projects: [],
      loading: true,
      search: '',
      projectFilter: '',
      statusFilter: '',
      priorityFilter: '',
      perPage: 10,
      currentPage: 1,
      sortColumn: 'due_date',
      sortOrder: 'asc',
      selectedTask: null,
      deleteModal: null
    };
  },
  computed: {
    paginationPages() {
      if (!this.tasks.last_page) return [];
      
      const pages = [];
      let startPage = Math.max(1, this.tasks.current_page - 2);
      let endPage = Math.min(this.tasks.last_page, startPage + 4);
      
      if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
      }
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      return pages;
    }
  },
  methods: {
    async fetchTasks() {
      this.loading = true;
      try {
        await this.$store.dispatch('tasks/fetchTasks', {
          page: this.currentPage,
          per_page: this.perPage,
          search: this.search,
          project_id: this.projectFilter,
          status: this.statusFilter,
          is_priority: this.priorityFilter,
          sort_by: this.sortColumn,
          sort_order: this.sortOrder
        });
        this.tasks = this.$store.state.tasks.tasks;
      } catch (error) {
        console.error('Error fetching tasks:', error);
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
    changePage(page) {
      if (page !== this.currentPage) {
        this.currentPage = page;
        this.fetchTasks();
      }
    },
    searchTasks() {
      this.currentPage = 1;
      this.fetchTasks();
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
      this.fetchTasks();
    },
    confirmDelete(task) {
      this.selectedTask = task;
      this.deleteModal.show();
    },
    async deleteTask() {
      if (!this.selectedTask) return;
      
      try {
        await this.$store.dispatch('tasks/deleteTask', this.selectedTask.id);
        this.deleteModal.hide();
        this.fetchTasks();
      } catch (error) {
        console.error('Error deleting task:', error);
        alert('Failed to delete task. ' + (error.response?.data?.message || ''));
      }
    },
    async exportExcel() {
      try {
        await this.$store.dispatch('tasks/exportExcel');
      } catch (error) {
        console.error('Error exporting tasks:', error);
        alert('Failed to export tasks. ' + (error.response?.data?.message || ''));
      }
    },
    async queueExport() {
      try {
        const response = await this.$store.dispatch('tasks/queueExport');
        alert(response.data.message || 'Export queued successfully');
      } catch (error) {
        console.error('Error queuing export:', error);
        alert('Failed to queue export. ' + (error.response?.data?.message || ''));
      }
    },
    async importExcel(event) {
      if (!event.target.files.length) return;
      
      const file = event.target.files[0];
      const formData = new FormData();
      formData.append('file', file);
      
      try {
        const response = await this.$store.dispatch('tasks/importExcel', formData);
        alert(response.data.message || 'Tasks imported successfully');
        this.fetchTasks();
      } catch (error) {
        console.error('Error importing tasks:', error);
        alert('Failed to import tasks. ' + (error.response?.data?.message || ''));
      }
      
      // Reset file input
      event.target.value = '';
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
    this.fetchProjects();
    this.fetchTasks();
    this.deleteModal = new Modal(this.$refs.deleteModal);
  }
};
</script>

<style scoped>
.sortable {
  cursor: pointer;
}
.sortable:hover {
  background-color: #f8f9fa;
}
</style>
