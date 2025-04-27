<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Project Management</h2>
      <div>
        <div class="btn-group me-2">
          <button class="btn btn-success" @click="exportExcel">Export Excel</button>
          <button class="btn btn-outline-success" @click="queueExport">Queue Export</button>
        </div>
        <label for="importFile" class="btn btn-info me-2">Import Excel</label>
        <input type="file" id="importFile" class="d-none" @change="importExcel" accept=".xlsx,.xls,.csv">
        <router-link to="/projects/create" class="btn btn-primary">Create New Project</router-link>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-3">
            <div class="input-group">
              <input type="text" class="form-control" v-model="search" placeholder="Search projects..." @input="searchProjects">
              <button class="btn btn-outline-secondary" type="button" @click="searchProjects">Search</button>
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="categoryFilter" @change="searchProjects">
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-end">
              <select class="form-select me-2" style="width: auto;" v-model="perPage" @change="fetchProjects">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="projects.data && projects.data.length === 0" class="text-center my-5">
          <p>No projects found.</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th @click="sortBy('id')" class="sortable">
                  ID
                  <i v-if="sortColumn === 'id'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('name')" class="sortable">
                  Name
                  <i v-if="sortColumn === 'name'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('category_id')" class="sortable">
                  Category
                  <i v-if="sortColumn === 'category_id'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th>Description</th>
                <th @click="sortBy('is_active')" class="sortable">
                  Status
                  <i v-if="sortColumn === 'is_active'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('start_date')" class="sortable">
                  Start Date
                  <i v-if="sortColumn === 'start_date'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th>Document</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="project in projects.data" :key="project.id">
                <td>{{ project.id }}</td>
                <td>{{ project.name }}</td>
                <td>{{ project.category ? project.category.name : '-' }}</td>
                <td>{{ project.description ? (project.description.length > 50 ? project.description.substring(0, 50) + '...' : project.description) : '-' }}</td>
                <td><span :class="'badge ' + (project.is_active ? 'bg-success' : 'bg-danger')">{{ project.is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>{{ project.start_date ? new Date(project.start_date).toLocaleDateString() : '-' }}</td>
                <td>
                  <a v-if="project.document_path" :href="'/storage/' + project.document_path" target="_blank" class="btn btn-sm btn-outline-primary">
                    View Document
                  </a>
                  <span v-else>-</span>
                </td>
                <td>
                  <router-link :to="{ name: 'projects.view', params: { id: project.id } }" class="btn btn-sm btn-info me-1">View</router-link>
                  <router-link :to="{ name: 'projects.edit', params: { id: project.id } }" class="btn btn-sm btn-primary me-1">Edit</router-link>
                  <button @click="confirmDelete(project)" class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="projects.data && projects.data.length > 0" class="d-flex justify-content-center mt-4">
          <nav aria-label="Page navigation">
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: projects.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(1)">First</a>
              </li>
              <li class="page-item" :class="{ disabled: projects.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(projects.current_page - 1)">Previous</a>
              </li>
              <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: projects.current_page === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: projects.current_page === projects.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(projects.current_page + 1)">Next</a>
              </li>
              <li class="page-item" :class="{ disabled: projects.current_page === projects.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(projects.last_page)">Last</a>
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
            Are you sure you want to delete the project "{{ selectedProject ? selectedProject.name : '' }}"?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteProject">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'ProjectIndex',
  data() {
    return {
      projects: {
        data: []
      },
      categories: [],
      loading: true,
      search: '',
      categoryFilter: '',
      perPage: 10,
      currentPage: 1,
      sortColumn: 'name',
      sortOrder: 'asc',
      selectedProject: null,
      deleteModal: null
    };
  },
  computed: {
    paginationPages() {
      if (!this.projects.last_page) return [];
      
      const pages = [];
      let startPage = Math.max(1, this.projects.current_page - 2);
      let endPage = Math.min(this.projects.last_page, startPage + 4);
      
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
    async fetchProjects() {
      this.loading = true;
      try {
        await this.$store.dispatch('projects/fetchProjects', {
          page: this.currentPage,
          per_page: this.perPage,
          search: this.search,
          category_id: this.categoryFilter,
          sort_by: this.sortColumn,
          sort_order: this.sortOrder
        });
        this.projects = this.$store.state.projects.projects;
      } catch (error) {
        console.error('Error fetching projects:', error);
      } finally {
        this.loading = false;
      }
    },
    async fetchCategories() {
      try {
        await this.$store.dispatch('projects/fetchCategories');
        this.categories = this.$store.state.projects.categories;
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    },
    changePage(page) {
      if (page !== this.currentPage) {
        this.currentPage = page;
        this.fetchProjects();
      }
    },
    searchProjects() {
      this.currentPage = 1;
      this.fetchProjects();
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
      this.fetchProjects();
    },
    confirmDelete(project) {
      this.selectedProject = project;
      this.deleteModal.show();
    },
    async deleteProject() {
      if (!this.selectedProject) return;
      
      try {
        await this.$store.dispatch('projects/deleteProject', this.selectedProject.id);
        this.deleteModal.hide();
        this.fetchProjects();
      } catch (error) {
        console.error('Error deleting project:', error);
        alert('Failed to delete project. ' + (error.response?.data?.message || ''));
      }
    },
    async exportExcel() {
      try {
        await this.$store.dispatch('projects/exportExcel');
      } catch (error) {
        console.error('Error exporting projects:', error);
        alert('Failed to export projects. ' + (error.response?.data?.message || ''));
      }
    },
    async queueExport() {
      try {
        const response = await this.$store.dispatch('projects/queueExport');
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
        const response = await this.$store.dispatch('projects/importExcel', formData);
        alert(response.data.message || 'Projects imported successfully');
        this.fetchProjects();
      } catch (error) {
        console.error('Error importing projects:', error);
        alert('Failed to import projects. ' + (error.response?.data?.message || ''));
      }
      
      // Reset file input
      event.target.value = '';
    }
  },
  mounted() {
    this.fetchCategories();
    this.fetchProjects();
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
