/**
 * Core Application Initialization
 */

import { initApi } from './api.js';
import { debounce } from './utils.js';

document.addEventListener('DOMContentLoaded', () => {
    console.log('App initialized.');
    initApi();
    
    // Add event listeners here
});
