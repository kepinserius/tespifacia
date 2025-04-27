import { createStore } from 'vuex';
import axios from 'axios';

// Create a new store instance
const store = createStore({
    modules: {
        auth: {
            namespaced: true,
            state: {
                user: null,
                authenticated: false
            },
            getters: {
                isAuthenticated: state => state.authenticated,
                isAdmin: state => state.user && state.user.roles && state.user.roles.some(role => role.name === 'admin'),
                user: state => state.user
            },
            mutations: {
                SET_AUTHENTICATED(state, value) {
                    state.authenticated = value;
                },
                SET_USER(state, user) {
                    state.user = user;
                }
            },
            actions: {
                async login({ commit }, credentials) {
                    // Performance optimization: use a single variable for timing
                    const startTime = performance.now();
                    
                    try {
                        // Reset any previous state
                        commit('SET_AUTHENTICATED', false);
                        commit('SET_USER', null);
                        
                        // Get CSRF token first (important for security)
                        await axios.get('/sanctum/csrf-cookie');
                        
                        // Perform login with optimized settings
                        const response = await axios.post('/api/login', credentials, {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            withCredentials: true, // Important for session cookies
                            timeout: 5000 // 5 second timeout for faster feedback
                        });
                        
                        // Process successful login
                        if (response.data && response.data.success) {
                            // Set authenticated status
                            commit('SET_AUTHENTICATED', true);
                            
                            // Set user data from login response
                            if (response.data.user) {
                                commit('SET_USER', response.data.user);
                                
                                // Performance optimization: Only fetch additional user data
                                // if the login response doesn't include roles/permissions
                                if (!response.data.user.roles) {
                                    try {
                                        const userResponse = await axios.get('/api/user', {
                                            withCredentials: true
                                        });
                                        
                                        if (userResponse.data) {
                                            commit('SET_USER', userResponse.data);
                                        }
                                    } catch (userError) {
                                        // Non-critical error, we can proceed with limited user data
                                        if (process.env.NODE_ENV !== 'production') {
                                            console.warn('Could not fetch complete user data');
                                        }
                                    }
                                }
                            }
                            
                            // Performance logging in development only
                            if (process.env.NODE_ENV !== 'production') {
                                console.log(`Login completed in ${performance.now() - startTime}ms`);
                            }
                            
                            return response;
                        } else {
                            throw new Error(response.data.message || 'Login failed');
                        }
                    } catch (error) {
                        // Clear any partial state on error
                        commit('SET_AUTHENTICATED', false);
                        commit('SET_USER', null);
                        
                        // Only log detailed errors in development
                        if (process.env.NODE_ENV !== 'production') {
                            console.error('Login error:', error.message);
                        }
                        
                        throw error;
                    }
                },
                async register({ commit }, userData) {
                    try {
                        const response = await axios.post('/api/register', userData);
                        commit('SET_TOKEN', response.data.token);
                        commit('SET_USER', response.data.user);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async logout({ commit }) {
                    // Performance optimization: use a single variable for timing
                    const startTime = performance.now();
                    
                    try {
                        // First clear local state immediately
                        commit('SET_AUTHENTICATED', false);
                        commit('SET_USER', null);
                        
                        // Get a fresh CSRF token before logout (helps with login after logout)
                        await axios.get('/sanctum/csrf-cookie');
                        
                        // Send logout request to server with credentials
                        await axios.post('/api/logout', {}, {
                            withCredentials: true,
                            timeout: 3000 // 3 second timeout for faster feedback
                        });
                        
                        // Performance logging in development only
                        if (process.env.NODE_ENV !== 'production') {
                            console.log(`Logout completed in ${performance.now() - startTime}ms`);
                        }
                        
                        // Force a complete page reload to clear any cached state
                        // Use location.replace instead of href for better performance
                        window.location.replace('/');
                    } catch (error) {
                        // Even if server logout fails, ensure client state is cleared
                        commit('SET_AUTHENTICATED', false);
                        commit('SET_USER', null);
                        
                        // Only log detailed errors in development
                        if (process.env.NODE_ENV !== 'production') {
                            console.error('Logout error:', error.message);
                        }
                        
                        // Still redirect to home
                        window.location.replace('/');
                    }
                },
                async fetchUser({ commit }) {
                    // Performance optimization: use a single variable for timing
                    const startTime = performance.now();
                    
                    try {
                        const response = await axios.get('/api/user', {
                            withCredentials: true,
                            timeout: 5000, // 5 second timeout for faster feedback
                            headers: {
                                'Cache-Control': 'no-cache' // Ensure we get fresh data
                            }
                        });
                        
                        if (response.data) {
                            commit('SET_USER', response.data);
                            commit('SET_AUTHENTICATED', true);
                            
                            // Performance logging in development only
                            if (process.env.NODE_ENV !== 'production') {
                                console.log(`User data fetched in ${performance.now() - startTime}ms`);
                            }
                            
                            return response;
                        } else {
                            throw new Error('Invalid user data received');
                        }
                    } catch (error) {
                        // Clear auth state on error
                        commit('SET_AUTHENTICATED', false);
                        commit('SET_USER', null);
                        
                        // Only log detailed errors in development
                        if (process.env.NODE_ENV !== 'production') {
                            console.error('Error fetching user:', error.message);
                        }
                        
                        throw error;
                    }
                }
            }
        },
        roles: {
            namespaced: true,
            state: {
                roles: [],
                permissions: []
            },
            mutations: {
                SET_ROLES(state, roles) {
                    state.roles = roles;
                },
                SET_PERMISSIONS(state, permissions) {
                    state.permissions = permissions;
                }
            },
            actions: {
                async fetchRoles({ commit }, params = {}) {
                    try {
                        const response = await axios.get('/api/roles', { params });
                        commit('SET_ROLES', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchPermissions({ commit }) {
                    try {
                        const response = await axios.get('/api/permissions');
                        commit('SET_PERMISSIONS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async createRole({ dispatch }, roleData) {
                    try {
                        const response = await axios.post('/api/roles', roleData);
                        await dispatch('fetchRoles');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async updateRole({ dispatch }, { id, data }) {
                    try {
                        const response = await axios.put(`/api/roles/${id}`, data);
                        await dispatch('fetchRoles');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async deleteRole({ dispatch }, id) {
                    try {
                        const response = await axios.delete(`/api/roles/${id}`);
                        await dispatch('fetchRoles');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                }
            }
        },
        users: {
            namespaced: true,
            state: {
                users: [],
                allRoles: []
            },
            mutations: {
                SET_USERS(state, users) {
                    state.users = users;
                },
                SET_ALL_ROLES(state, roles) {
                    state.allRoles = roles;
                }
            },
            actions: {
                async fetchUsers({ commit }, params = {}) {
                    try {
                        const response = await axios.get('/api/users', { params });
                        commit('SET_USERS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchAllRoles({ commit }) {
                    try {
                        const response = await axios.get('/api/all-roles');
                        commit('SET_ALL_ROLES', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async createUser({ dispatch }, userData) {
                    try {
                        const response = await axios.post('/api/users', userData);
                        await dispatch('fetchUsers');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async updateUser({ dispatch }, { id, data }) {
                    try {
                        const response = await axios.put(`/api/users/${id}`, data);
                        await dispatch('fetchUsers');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async deleteUser({ dispatch }, id) {
                    try {
                        const response = await axios.delete(`/api/users/${id}`);
                        await dispatch('fetchUsers');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                }
            }
        },
        categories: {
            namespaced: true,
            state: {
                categories: [],
                category: null,
                audits: []
            },
            mutations: {
                SET_CATEGORIES(state, categories) {
                    state.categories = categories;
                },
                SET_CATEGORY(state, category) {
                    state.category = category;
                },
                SET_AUDITS(state, audits) {
                    state.audits = audits;
                }
            },
            actions: {
                async fetchCategories({ commit }, params = {}) {
                    try {
                        const response = await axios.get('/api/categories', { params });
                        commit('SET_CATEGORIES', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchCategory({ commit }, id) {
                    try {
                        const response = await axios.get(`/api/categories/${id}`);
                        commit('SET_CATEGORY', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async createCategory({ dispatch }, categoryData) {
                    try {
                        const response = await axios.post('/api/categories', categoryData);
                        await dispatch('fetchCategories');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async updateCategory({ dispatch }, { id, data }) {
                    try {
                        const response = await axios.put(`/api/categories/${id}`, data);
                        await dispatch('fetchCategories');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async deleteCategory({ dispatch }, id) {
                    try {
                        const response = await axios.delete(`/api/categories/${id}`);
                        await dispatch('fetchCategories');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchAudits({ commit }, id) {
                    try {
                        const response = await axios.get(`/api/categories/${id}/audits`);
                        commit('SET_AUDITS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async exportExcel() {
                    try {
                        window.location.href = '/api/categories/export/excel';
                        return { success: true };
                    } catch (error) {
                        throw error;
                    }
                },
                async queueExport() {
                    try {
                        const response = await axios.get('/api/categories/export/queue');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async importExcel({ dispatch }, formData) {
                    try {
                        const response = await axios.post('/api/categories/import/excel', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchCategories');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async queueImport({ dispatch }, formData) {
                    try {
                        const response = await axios.post('/api/categories/import/queue', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchCategories');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                }
            }
        },
        projects: {
            namespaced: true,
            state: {
                projects: [],
                project: null,
                categories: [],
                audits: []
            },
            mutations: {
                SET_PROJECTS(state, projects) {
                    state.projects = projects;
                },
                SET_PROJECT(state, project) {
                    state.project = project;
                },
                SET_CATEGORIES(state, categories) {
                    state.categories = categories;
                },
                SET_AUDITS(state, audits) {
                    state.audits = audits;
                }
            },
            actions: {
                async fetchProjects({ commit }, params = {}) {
                    try {
                        const response = await axios.get('/api/projects', { params });
                        commit('SET_PROJECTS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchProject({ commit }, id) {
                    try {
                        const response = await axios.get(`/api/projects/${id}`);
                        commit('SET_PROJECT', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchCategories({ commit }) {
                    try {
                        const response = await axios.get('/api/categories-for-select');
                        commit('SET_CATEGORIES', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async createProject({ dispatch }, projectData) {
                    try {
                        const formData = new FormData();
                        for (const key in projectData) {
                            if (key === 'document' && projectData[key]) {
                                formData.append(key, projectData[key]);
                            } else if (key === 'metadata' && projectData[key]) {
                                formData.append(key, JSON.stringify(projectData[key]));
                            } else if (projectData[key] !== null && projectData[key] !== undefined) {
                                formData.append(key, projectData[key]);
                            }
                        }
                        
                        const response = await axios.post('/api/projects', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchProjects');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async updateProject({ dispatch }, { id, data }) {
                    try {
                        const formData = new FormData();
                        for (const key in data) {
                            if (key === 'document' && data[key]) {
                                formData.append(key, data[key]);
                            } else if (key === 'metadata' && data[key]) {
                                formData.append(key, JSON.stringify(data[key]));
                            } else if (data[key] !== null && data[key] !== undefined) {
                                formData.append(key, data[key]);
                            }
                        }
                        formData.append('_method', 'PUT');
                        
                        const response = await axios.post(`/api/projects/${id}`, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchProjects');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async deleteProject({ dispatch }, id) {
                    try {
                        const response = await axios.delete(`/api/projects/${id}`);
                        await dispatch('fetchProjects');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchAudits({ commit }, id) {
                    try {
                        const response = await axios.get(`/api/projects/${id}/audits`);
                        commit('SET_AUDITS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async exportExcel() {
                    try {
                        window.location.href = '/api/projects/export/excel';
                        return { success: true };
                    } catch (error) {
                        throw error;
                    }
                },
                async queueExport() {
                    try {
                        const response = await axios.get('/api/projects/export/queue');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async importExcel({ dispatch }, formData) {
                    try {
                        const response = await axios.post('/api/projects/import/excel', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchProjects');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async queueImport({ dispatch }, formData) {
                    try {
                        const response = await axios.post('/api/projects/import/queue', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchProjects');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                }
            }
        },
        tasks: {
            namespaced: true,
            state: {
                tasks: [],
                task: null,
                projects: [],
                audits: []
            },
            mutations: {
                SET_TASKS(state, tasks) {
                    state.tasks = tasks;
                },
                SET_TASK(state, task) {
                    state.task = task;
                },
                SET_PROJECTS(state, projects) {
                    state.projects = projects;
                },
                SET_AUDITS(state, audits) {
                    state.audits = audits;
                }
            },
            actions: {
                async fetchTasks({ commit }, params = {}) {
                    try {
                        const response = await axios.get('/api/tasks', { params });
                        commit('SET_TASKS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchTask({ commit }, id) {
                    try {
                        const response = await axios.get(`/api/tasks/${id}`);
                        commit('SET_TASK', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchProjects({ commit }) {
                    try {
                        const response = await axios.get('/api/projects-for-select');
                        commit('SET_PROJECTS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async createTask({ dispatch }, taskData) {
                    try {
                        if (taskData.metadata) {
                            taskData.metadata = JSON.stringify(taskData.metadata);
                        }
                        
                        const response = await axios.post('/api/tasks', taskData);
                        await dispatch('fetchTasks');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async updateTask({ dispatch }, { id, data }) {
                    try {
                        if (data.metadata) {
                            data.metadata = JSON.stringify(data.metadata);
                        }
                        
                        const response = await axios.put(`/api/tasks/${id}`, data);
                        await dispatch('fetchTasks');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async deleteTask({ dispatch }, id) {
                    try {
                        const response = await axios.delete(`/api/tasks/${id}`);
                        await dispatch('fetchTasks');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async fetchAudits({ commit }, id) {
                    try {
                        const response = await axios.get(`/api/tasks/${id}/audits`);
                        commit('SET_AUDITS', response.data);
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async exportExcel() {
                    try {
                        window.location.href = '/api/tasks/export/excel';
                        return { success: true };
                    } catch (error) {
                        throw error;
                    }
                },
                async queueExport() {
                    try {
                        const response = await axios.get('/api/tasks/export/queue');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async importExcel({ dispatch }, formData) {
                    try {
                        const response = await axios.post('/api/tasks/import/excel', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchTasks');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                },
                async queueImport({ dispatch }, formData) {
                    try {
                        const response = await axios.post('/api/tasks/import/queue', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        await dispatch('fetchTasks');
                        return response;
                    } catch (error) {
                        throw error;
                    }
                }
            }
        }
    }
});

// Set up axios defaults
axios.defaults.baseURL = '/';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Add token to axios if it exists
const token = localStorage.getItem('token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Add response interceptor
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            store.commit('auth/SET_TOKEN', null);
            store.commit('auth/SET_USER', null);
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default store;
