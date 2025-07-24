import { initializeDataTables } from './datatables.js';
import { initializeFormHandler } from './form-handler.js';

$(document).ready(function() {
    initializeDataTables();
    initializeFormHandler();
});
