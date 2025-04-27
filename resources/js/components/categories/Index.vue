<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Category Management</h2>
      <div>
        <div class="btn-group me-2">
          <button class="btn btn-success" @click="exportExcel">Export Excel</button>
          <button class="btn btn-outline-success" @click="queueExport">Queue Export</button>
        </div>
        <label for="importFile" class="btn btn-info me-2">Import Excel</label>
        <input type="file" id="importFile" class="d-none" @change="importExcel" accept=".xlsx,.xls,.csv">
        <router-link to="/categories/create" class="btn btn-primary">Create New Category</router-link>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-6">
            <div class="input-group">
              <input type="text" class="form-control" v-model="search" placeholder="Search categories..." @input="searchCategories">
              <button class="btn btn-outline-secondary" type="button" @click="searchCategories">Search</button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-end">
              <select class="form-select me-2" style="width: auto;" v-model="perPage" @change="fetchCategories">
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

        <div v-else-if="categories.data && categories.data.length === 0" class="text-center my-5">
          <p>No categories found.</p>
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
                <th>Description</th>
                <th @click="sortBy('is_active')" class="sortable">
                  Status
                  <i v-if="sortColumn === 'is_active'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th @click="sortBy('published_at')" class="sortable">
                  Published At
                  <i v-if="sortColumn === 'published_at'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in categories.data" :key="category.id">
                <td>{{ category.id }}</td>
                <td>{{ category.name }}</td>
                <td>{{ category.description ? (category.description.length > 50 ? category.description.substring(0, 50) + '...' : category.description) : '-' }}</td>
                <td><span :class="'badge ' + (category.is_active ? 'bg-success' : 'bg-danger')">{{ category.is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>{{ category.published_at ? new Date(category.published_at).toLocaleDateString() : '-' }}</td>
                <td>
                  <router-link :to="{ name: 'categories.view', params: { id: category.id } }" class="btn btn-sm btn-info me-1">View</router-link>
                  <router-link :to="{ name: 'categories.edit', params: { id: category.id } }" class="btn btn-sm btn-primary me-1">Edit</router-link>
                  <button @click="confirmDelete(category)" class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="categories.data && categories.data.length > 0" class="d-flex justify-content-center mt-4">
          <nav aria-label="Page navigation">
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: categories.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(1)">First</a>
              </li>
              <li class="page-item" :class="{ disabled: categories.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(categories.current_page - 1)">Previous</a>
              </li>
              <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: categories.current_page === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: categories.current_page === categories.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(categories.current_page + 1)">Next</a>
              </li>
              <li class="page-item" :class="{ disabled: categories.current_page === categories.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(categories.last_page)">Last</a>
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
            Are you sure you want to delete the category "{{ selectedCategory ? selectedCategory.name : '' }}"?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteCategory">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'CategoryIndex',
  data() {
    return {
      categories: {
        data: []
      },
      loading: true,
      search: '',
      perPage: 10,
      currentPage: 1,
      sortColumn: 'name',
      sortOrder: 'asc',
      selectedCategory: null,
      deleteModal: null
    };
  },
  computed: {
    paginationPages() {
      if (!this.categories.last_page) return [];
      
      const pages = [];
      let startPage = Math.max(1, this.categories.current_page - 2);
      let endPage = Math.min(this.categories.last_page, startPage + 4);
      
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
    async fetchCategories() {
      this.loading = true;
      try {
        await this.$store.dispatch('categories/fetchCategories', {
          page: this.currentPage,
          per_page: this.perPage,
          search: this.search,
          sort_by: this.sortColumn,
          sort_order: this.sortOrder
        });
        this.categories = this.$store.state.categories.categories;
      } catch (error) {
        console.error('Error fetching categories:', error);
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      if (page !== this.currentPage) {
        this.currentPage = page;
        this.fetchCategories();
      }
    },
    searchCategories() {
      this.currentPage = 1;
      this.fetchCategories();
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
      this.fetchCategories();
    },
    confirmDelete(category) {
      this.selectedCategory = category;
      this.deleteModal.show();
    },
    async deleteCategory() {
      if (!this.selectedCategory) return;
      
      try {
        await this.$store.dispatch('categories/deleteCategory', this.selectedCategory.id);
        this.deleteModal.hide();
        this.fetchCategories();
      } catch (error) {
        console.error('Error deleting category:', error);
        alert('Failed to delete category. ' + (error.response?.data?.message || ''));
      }
    },
    async exportExcel() {
      try {
        await this.$store.dispatch('categories/exportExcel');
      } catch (error) {
        console.error('Error exporting categories:', error);
        alert('Failed to export categories. ' + (error.response?.data?.message || ''));
      }
    },
    async queueExport() {
      try {
        const response = await this.$store.dispatch('categories/queueExport');
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
        const response = await this.$store.dispatch('categories/importExcel', formData);
        alert(response.data.message || 'Categories imported successfully');
        this.fetchCategories();
      } catch (error) {
        console.error('Error importing categories:', error);
        alert('Failed to import categories. ' + (error.response?.data?.message || ''));
      }
      
      // Reset file input
      event.target.value = '';
    }
  },
  mounted() {
    this.fetchCategories();
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
