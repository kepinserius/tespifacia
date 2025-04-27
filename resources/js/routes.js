// Import components
import Home from './components/Home.vue';
import Login from './components/auth/Login.vue';
import Register from './components/auth/Register.vue';
import Dashboard from './components/Dashboard.vue';
import RoleIndex from './components/roles/Index.vue';
import RoleCreate from './components/roles/Create.vue';
import RoleEdit from './components/roles/Edit.vue';
import UserIndex from './components/users/Index.vue';
import UserCreate from './components/users/Create.vue';
import UserEdit from './components/users/Edit.vue';
import CategoryIndex from './components/categories/Index.vue';
import CategoryCreate from './components/categories/Create.vue';
import CategoryEdit from './components/categories/Edit.vue';
import CategoryView from './components/categories/View.vue';
import ProjectIndex from './components/projects/Index.vue';
import ProjectCreate from './components/projects/Create.vue';
import ProjectEdit from './components/projects/Edit.vue';
import ProjectView from './components/projects/View.vue';
import TaskIndex from './components/tasks/Index.vue';
import TaskCreate from './components/tasks/Create.vue';
import TaskEdit from './components/tasks/Edit.vue';
import TaskView from './components/tasks/View.vue';

const routes = [
    // Public routes
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    {
        path: '/register',
        name: 'register',
        component: Register
    },
    
    // Protected routes
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    
    // Role management routes
    {
        path: '/roles',
        name: 'roles.index',
        component: RoleIndex,
        meta: { requiresAuth: true }
    },
    {
        path: '/roles/create',
        name: 'roles.create',
        component: RoleCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/roles/:id/edit',
        name: 'roles.edit',
        component: RoleEdit,
        meta: { requiresAuth: true },
        props: true
    },
    
    // User management routes (admin only)
    {
        path: '/users',
        name: 'users.index',
        component: UserIndex,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/users/create',
        name: 'users.create',
        component: UserCreate,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/users/:id/edit',
        name: 'users.edit',
        component: UserEdit,
        meta: { requiresAuth: true, requiresAdmin: true },
        props: true
    },
    
    // Category routes
    {
        path: '/categories',
        name: 'categories.index',
        component: CategoryIndex,
        meta: { requiresAuth: true }
    },
    {
        path: '/categories/create',
        name: 'categories.create',
        component: CategoryCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/categories/:id',
        name: 'categories.view',
        component: CategoryView,
        meta: { requiresAuth: true },
        props: true
    },
    {
        path: '/categories/:id/edit',
        name: 'categories.edit',
        component: CategoryEdit,
        meta: { requiresAuth: true },
        props: true
    },
    
    // Project routes
    {
        path: '/projects',
        name: 'projects.index',
        component: ProjectIndex,
        meta: { requiresAuth: true }
    },
    {
        path: '/projects/create',
        name: 'projects.create',
        component: ProjectCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/projects/:id',
        name: 'projects.view',
        component: ProjectView,
        meta: { requiresAuth: true },
        props: true
    },
    {
        path: '/projects/:id/edit',
        name: 'projects.edit',
        component: ProjectEdit,
        meta: { requiresAuth: true },
        props: true
    },
    
    // Task routes
    {
        path: '/tasks',
        name: 'tasks.index',
        component: TaskIndex,
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/create',
        name: 'tasks.create',
        component: TaskCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/:id',
        name: 'tasks.view',
        component: TaskView,
        meta: { requiresAuth: true },
        props: true
    },
    {
        path: '/tasks/:id/edit',
        name: 'tasks.edit',
        component: TaskEdit,
        meta: { requiresAuth: true },
        props: true
    },
    
    // Catch-all route
    {
        path: '/:pathMatch(.*)*',
        redirect: { name: 'dashboard' }
    }
];

export default routes;
