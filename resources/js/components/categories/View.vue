<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>View Category</h2>
      <div>
        <router-link :to="{ name: 'categories.edit', params: { id } }" class="btn btn-primary me-2">Edit</router-link>
        <router-link to="/categories" class="btn btn-secondary">Back to Categories</router-link>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading category information...</p>
    </div>

    <div v-else class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Category Information</div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 150px;">ID</th>
                  <td>{{ category.id }}</td>
                </tr>
                <tr>
                  <th>UUID</th>
                  <td>{{ category.uuid }}</td>
                </tr>
                <tr>
                  <th>Name</th>
                  <td>{{ category.name }}</td>
                </tr>
                <tr>
                  <th>Description</th>
                  <td>{{ category.description || '-' }}</td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><span :class="'badge ' + (category.is_active ? 'bg-success' : 'bg-danger')">{{ category.is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                <tr>
                  <th>Published At</th>
                  <td>{{ category.published_at ? new Date(category.published_at).toLocaleString() : '-' }}</td>
                </tr>
                <tr>
                  <th>Created At</th>
                  <td>{{ new Date(category.created_at).toLocaleString() }}</td>
                </tr>
                <tr>
                  <th>Updated At</th>
                  <td>{{ new Date(category.updated_at).toLocaleString() }}</td>
                </tr>
                <tr v-if="category.metadata">
                  <th>Metadata</th>
                  <td>
                    <pre class="mb-0">{{ formatJson(category.metadata) }}</pre>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Related Projects</div>
          <div class="card-body">
            <div v-if="!category.projects || category.projects.length === 0" class="alert alert-info">
              No projects associated with this category.
            </div>
            <div v-else class="list-group">
              <router-link 
                v-for="project in category.projects" 
                :key="project.id" 
                :to="{ name: 'projects.view', params: { id: project.id } }" 
                class="list-group-item list-group-item-action"
              >
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1">{{ project.name }}</h5>
                  <small>{{ project.is_active ? 'Active' : 'Inactive' }}</small>
                </div>
                <p class="mb-1">{{ project.description ? (project.description.length > 100 ? project.description.substring(0, 100) + '...' : project.description) : 'No description' }}</p>
                <small>Tasks: {{ project.tasks ? project.tasks.length : 0 }}</small>
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
              No audit history available for this category.
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
  name: 'CategoryView',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      category: {},
      audits: [],
      loading: true,
      auditsLoading: true
    };
  },
  methods: {
    async fetchCategory() {
      this.loading = true;
      try {
        await this.$store.dispatch('categories/fetchCategory', this.id);
        this.category = this.$store.state.categories.category;
      } catch (error) {
        console.error('Error fetching category:', error);
      } finally {
        this.loading = false;
      }
    },
    async fetchAudits() {
      this.auditsLoading = true;
      try {
        await this.$store.dispatch('categories/fetchAudits', this.id);
        this.audits = this.$store.state.categories.audits;
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
    }
  },
  mounted() {
    this.fetchCategory();
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
