// Function to load a specific directory using AJAX
function loadDirectory(dir) {
    loadPage(1, dir); // Load the first page of the directory
}

// Function to upload a file with progress tracking
function uploadFile() {
    const formData = new FormData(document.getElementById('upload-form'));
    const xhr = new XMLHttpRequest();
    
    // Show the progress bar
    $('#upload-progress-container').show();
    $('#upload-progress').css('width', '0%').removeClass('bg-danger').addClass('bg-info');
    $('#upload-feedback').text('');

    // Track the upload progress
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            $('#upload-progress').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete);
        }
    });

    // Handle successful completion
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            $('#upload-feedback').text(xhr.responseText).removeClass('text-danger').addClass('text-success');
            loadPage(1);  // Reload the page if needed
        } else {
            $('#upload-feedback').text('Failed to upload file.').removeClass('text-success').addClass('text-danger');
            $('#upload-progress').addClass('bg-danger');
        }
        $('#upload-progress-container').fadeOut(1500);  // Hide the progress bar after a delay
    });

    // Handle errors
    xhr.addEventListener('error', function() {
        $('#upload-feedback').text('Failed to upload file.').removeClass('text-success').addClass('text-danger');
        $('#upload-progress').addClass('bg-danger');
        $('#upload-progress-container').fadeOut(1500);  // Hide the progress bar after a delay
    });

    // Open the request and send the form data
    xhr.open('POST', 'upload.php');
    xhr.send(formData);
}

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Function to apply or remove dark mode class
    const applyDarkMode = (isDarkMode) => {
        if (isDarkMode) {
            body.classList.add('dark-mode');
        } else {
            body.classList.remove('dark-mode');
        }
    };

    // Check localStorage for theme preference
    const currentTheme = localStorage.getItem('theme');
    const isDarkMode = currentTheme === 'dark';
    applyDarkMode(isDarkMode);
    toggle.checked = isDarkMode;

    // Toggle dark mode on switch click
    toggle.addEventListener('change', () => {
        const isDarkMode = toggle.checked;
        applyDarkMode(isDarkMode);
        // Store preference in localStorage
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
    });
});


// Function to show file content in modal
function showContent(filePath, fileType) {
    // Use AJAX to fetch the file content
    fetch(filePath)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text(); // Assuming the file content is text
        })
        .then(data => {
            // Populate the modal with the file content
            document.getElementById('fileContent').textContent = data;
            document.getElementById('fileContent').className = `language-${fileType || 'plaintext'}`;

            // Show the modal
            $('#fileContentModal').modal('show');

            // Re-highlight the syntax
            Prism.highlightAll();
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            alert('Failed to load file content.');
        });
}

function filterFiles(fileType) {
    $.ajax({
        url: 'fetch_files.php?fileType=' + encodeURIComponent(fileType) + '&page=1', // Reset to page 1 on filter
        method: 'GET',
        success: function (data) {
            $('#file-list-container').html(data); // Update your file list container
            // Update the dropdown selection
            document.getElementById('fileTypeSelect').value = fileType;
        },
        error: function () {
            console.error('Error filtering files of type: ' + fileType);
        }
    });
}

function sortFiles() {
    // Get selected sort values
    const sortByDate = document.getElementById('sortByDate').value;
    const sortBySize = document.getElementById('sortBySize').value;

    // Create query parameters to send with the request
    const params = new URLSearchParams();
    if (sortByDate) params.append('sortByDate', sortByDate);
    if (sortBySize) params.append('sortBySize', sortBySize);

    // Assuming your PHP file is named 'fetch_files.php'
    const phpFileName = 'fetch_files.php'; // Change this to your actual PHP file name

    // Send the AJAX request to update the file list
    fetch(`${phpFileName}?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('file-list-container').innerHTML = data; // Update the file list
            
            // Set the selected values for the sort dropdowns
            document.getElementById('sortByDate').value = sortByDate;
            document.getElementById('sortBySize').value = sortBySize;
        })
        .catch(error => console.error('Error fetching sorted files:', error));
}

// Function to reset filters
function resetFilter() {
    // Clear the file type filter
    document.getElementById('fileTypeSelect').value = '';

    // Reset sorting filters
    document.getElementById('sortByDate').value = '';
    document.getElementById('sortBySize').value = '';

    // Clear search query
    document.getElementById('search').value = '';

    // Reload the first page without any filters
    loadPage(1);
}

function loadFilesInDirectory(directoryPath) {
    // Fetch and display files from the specified directory
    fetch(`fetch_files.php?path=${directoryPath}`) // Update with the correct URL
        .then(response => response.text())
        .then(data => {
            document.getElementById('file-list-container').innerHTML = data; // Update file list container
        })
        .catch(error => {
            console.error("Error loading directory:", error);
        });
}

// Function to load a specific page using AJAX
function loadPage(page, dir = '.') {
    // Get selected sort values
    const sortByDate = document.getElementById('sortByDate').value;
    const sortBySize = document.getElementById('sortBySize').value;
    const searchQuery = document.getElementById('search').value.trim();

    // Get selected file type
    const fileTypeSelect = document.getElementById('fileTypeSelect').value;

    // Make an AJAX request to fetch the files
    $.ajax({
        url: 'fetch_files.php',
        type: 'GET',
        data: { 
            page: page, 
            dir: dir,
            sortByDate: sortByDate,
            sortBySize: sortBySize,
            fileType: fileTypeSelect,
            search: searchQuery
        },
        success: function(response) {
            // Update the file list container with the new content
            $('#file-list-container').html(response);
        },
        error: function() {
            alert('Failed to load files.');
        }
    });
}

// Function to handle search input with debouncing
const searchFiles = debounce(function() {
    // Trigger the search by loading the first page with the search query
    loadPage(1);
}, 300); // 300 milliseconds delay

// Debounce function to limit the rate of function calls
function debounce(func, delay) {
    let debounceTimer;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => func.apply(context, args), delay);
    };
}

document.getElementById('file-input').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
    this.nextElementSibling.textContent = fileName; // Change the label to show the file name
});


// Load the first page on initial load
$(document).ready(function() {
    loadPage(1);
});