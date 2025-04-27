// This file is a placeholder for any custom JavaScript that needs to be loaded outside of the Vue application
// Most of the application's JavaScript is handled by the Vue.js components and Vite bundling

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any non-Vue components here
    console.log('Laravel Vue App loaded successfully');
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
