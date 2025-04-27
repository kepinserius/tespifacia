<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>User Management</h2>
      <router-link to="/users/create" class="btn btn-primary">Create New User</router-link>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-6">
            <div class="input-group">
              <input type="text" class="form-control" v-model="search" placeholder="Search users..." @input="searchUsers">
              <button class="btn btn-outline-secondary" type="button" @click="searchUsers">Search</button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-end">
              <select class="form-select me-2" style="width: auto;" v-model="perPage" @change="fetchUsers">
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

        <div v-else-if="users.data && users.data.length === 0" class="text-center my-5">
          <p>No users found.</p>
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
                <th @click="sortBy('email')" class="sortable">
                  Email
                  <i v-if="sortColumn === 'email'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                </th>
                <th>Roles</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users.data" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span v-for="(role, index) in user.roles" :key="role.id" class="badge bg-info me-1 mb-1">
                    {{ role.name }}
                  </span>
                  <span v-if="user.roles.length === 0">No roles</span>
                </td>
                <td>
                  <router-link :to="{ name: 'users.edit', params: { id: user.id } }" class="btn btn-sm btn-primary me-1">Edit</router-link>
                  <button @click="confirmDelete(user)" class="btn btn-sm btn-danger" :disabled="isLastAdmin(user)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="users.data && users.data.length > 0" class="d-flex justify-content-center mt-4">
          <nav aria-label="Page navigation">
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: users.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(1)">First</a>
              </li>
              <li class="page-item" :class="{ disabled: users.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(users.current_page - 1)">Previous</a>
              </li>
              <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: users.current_page === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: users.current_page === users.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(users.current_page + 1)">Next</a>
              </li>
              <li class="page-item" :class="{ disabled: users.current_page === users.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(users.last_page)">Last</a>
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
            Are you sure you want to delete the user "{{ selectedUser ? selectedUser.name : '' }}"?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteUser">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'UserIndex',
  data() {
    return {
      users: {
        data: []
      },
      loading: true,
      search: '',
      perPage: 10,
      currentPage: 1,
      sortColumn: 'name',
      sortOrder: 'asc',
      selectedUser: null,
      deleteModal: null
    };
  },
  computed: {
    paginationPages() {
      if (!this.users.last_page) return [];
      
      const pages = [];
      let startPage = Math.max(1, this.users.current_page - 2);
      let endPage = Math.min(this.users.last_page, startPage + 4);
      
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
    async fetchUsers() {
      this.loading = true;
      try {
        await this.$store.dispatch('users/fetchUsers', {
          page: this.currentPage,
          per_page: this.perPage,
          search: this.search,
          sort_by: this.sortColumn,
          sort_order: this.sortOrder
        });
        this.users = this.$store.state.users.users;
      } catch (error) {
        console.error('Error fetching users:', error);
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      if (page !== this.currentPage) {
        this.currentPage = page;
        this.fetchUsers();
      }
    },
    searchUsers() {
      this.currentPage = 1;
      this.fetchUsers();
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
      this.fetchUsers();
    },
    isLastAdmin(user) {
      // Check if this is the last admin user
      const hasAdminRole = user.roles.some(role => role.name === 'admin');
      if (!hasAdminRole) return false;
      
      // Count admin users
      const adminUsers = this.users.data.filter(u => 
        u.roles.some(role => role.name === 'admin')
      );
      
      return adminUsers.length <= 1;
    },
    confirmDelete(user) {
      this.selectedUser = user;
      this.deleteModal.show();
    },
    async deleteUser() {
      if (!this.selectedUser) return;
      
      try {
        await this.$store.dispatch('users/deleteUser', this.selectedUser.id);
        this.deleteModal.hide();
        this.fetchUsers();
      } catch (error) {
        console.error('Error deleting user:', error);
        alert('Failed to delete user. ' + (error.response?.data?.message || ''));
      }
    }
  },
  mounted() {
    this.fetchUsers();
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
