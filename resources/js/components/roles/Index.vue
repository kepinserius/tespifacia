<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Role Management</h2>
      <router-link to="/roles/create" class="btn btn-primary">Create New Role</router-link>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-6">
            <div class="input-group">
              <input type="text" class="form-control" v-model="search" placeholder="Search roles..." @input="searchRoles">
              <button class="btn btn-outline-secondary" type="button" @click="searchRoles">Search</button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-end">
              <select class="form-select me-2" style="width: auto;" v-model="perPage" @change="fetchRoles">
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

        <div v-else-if="roles.data && roles.data.length === 0" class="text-center my-5">
          <p>No roles found.</p>
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
                <th>Permissions</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="role in roles.data" :key="role.id">
                <td>{{ role.id }}</td>
                <td>{{ role.name }}</td>
                <td>
                  <span v-for="(permission, index) in role.permissions" :key="permission.id" class="badge bg-info me-1 mb-1">
                    {{ permission.name }}
                  </span>
                  <span v-if="role.permissions.length === 0">No permissions</span>
                </td>
                <td>
                  <router-link :to="{ name: 'roles.edit', params: { id: role.id } }" class="btn btn-sm btn-primary me-1">Edit</router-link>
                  <button @click="confirmDelete(role)" class="btn btn-sm btn-danger" :disabled="role.name === 'admin'">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="roles.data && roles.data.length > 0" class="d-flex justify-content-center mt-4">
          <nav aria-label="Page navigation">
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: roles.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(1)">First</a>
              </li>
              <li class="page-item" :class="{ disabled: roles.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(roles.current_page - 1)">Previous</a>
              </li>
              <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: roles.current_page === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: roles.current_page === roles.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(roles.current_page + 1)">Next</a>
              </li>
              <li class="page-item" :class="{ disabled: roles.current_page === roles.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(roles.last_page)">Last</a>
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
            Are you sure you want to delete the role "{{ selectedRole ? selectedRole.name : '' }}"?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteRole">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'RoleIndex',
  data() {
    return {
      roles: {
        data: []
      },
      loading: true,
      search: '',
      perPage: 10,
      currentPage: 1,
      sortColumn: 'name',
      sortOrder: 'asc',
      selectedRole: null,
      deleteModal: null
    };
  },
  computed: {
    paginationPages() {
      if (!this.roles.last_page) return [];
      
      const pages = [];
      let startPage = Math.max(1, this.roles.current_page - 2);
      let endPage = Math.min(this.roles.last_page, startPage + 4);
      
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
    async fetchRoles() {
      this.loading = true;
      try {
        await this.$store.dispatch('roles/fetchRoles', {
          page: this.currentPage,
          per_page: this.perPage,
          search: this.search,
          sort_by: this.sortColumn,
          sort_order: this.sortOrder
        });
        this.roles = this.$store.state.roles.roles;
      } catch (error) {
        console.error('Error fetching roles:', error);
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      if (page !== this.currentPage) {
        this.currentPage = page;
        this.fetchRoles();
      }
    },
    searchRoles() {
      this.currentPage = 1;
      this.fetchRoles();
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
      this.fetchRoles();
    },
    confirmDelete(role) {
      this.selectedRole = role;
      this.deleteModal.show();
    },
    async deleteRole() {
      if (!this.selectedRole) return;
      
      try {
        await this.$store.dispatch('roles/deleteRole', this.selectedRole.id);
        this.deleteModal.hide();
        this.fetchRoles();
      } catch (error) {
        console.error('Error deleting role:', error);
        alert('Failed to delete role. ' + (error.response?.data?.message || ''));
      }
    }
  },
  mounted() {
    this.fetchRoles();
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
