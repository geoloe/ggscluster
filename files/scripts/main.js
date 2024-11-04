// Function to search for files
function searchFiles() {
    let input = document.getElementById('search').value.toLowerCase();
    let items = document.getElementsByClassName('list-group-item');

    for (let item of items) {
        let name = item.textContent.toLowerCase();
        item.style.display = name.includes(input) ? "" : "none";
    }
}

// Function to show the content of a file
function showContent(fileName, fileDir) {
    $.ajax({
        url: `/files/${fileDir}/${fileName}`,
        type: 'GET',
        success: function(response) {
            // Display the content in a modal or alert
            alert(response); // You can replace this with a modal display if preferred
        },
        error: function() {
            alert('Error fetching file content.');
        }
    });
}

// Function to load a specific page using AJAX
function loadPage(page, dir = '.') {
    $.ajax({
        url: 'fetch_files.php',
        type: 'GET',
        data: { page: page, dir: dir },
        success: function(response) {
            // Update the file list container with the new content
            $('#file-list-container').html(response);
        },
        error: function() {
            alert('Failed to load files.');
        }
    });
}

// Function to load a specific directory using AJAX
function loadDirectory(dir) {
    loadPage(1, dir); // Load the first page of the directory
}

// Function to upload a file
function uploadFile() {
    const fileInput = document.getElementById('file-input');
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    $.ajax({
        url: 'upload.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#upload-feedback').html(`<div class='alert alert-success'>${response}</div>`);
            loadPage(1); // Reload the file list after upload
        },
        error: function() {
            $('#upload-feedback').html("<div class='alert alert-danger'>Failed to upload file.</div>");
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('theme-toggle');

    // Check localStorage for theme preference
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        toggle.checked = true; // Set toggle to checked if dark mode is active
    }

    // Toggle dark mode on switch click
    toggle.addEventListener('change', () => {
        document.body.classList.toggle('dark-mode');
        // Store preference in localStorage
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    });
});

function updateFileList() {
    const sortBy = document.getElementById('sort-select').value;
    const filterBy = document.getElementById('filter-select').value;

    // Fetch the files with the selected sort and filter options
    fetch(`/files/getFiles.php?sort=${sortBy}&filter=${filterBy}`)
        .then(response => response.json())
        .then(data => {
            // Clear existing file list
            const fileListContainer = document.getElementById('file-list-container');
            fileListContainer.innerHTML = '';

            // Create and display new file list
            data.files.forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'list-group-item';
                fileItem.innerHTML = `<a href="${file.url}">${file.name}</a> - ${file.size} bytes - ${file.date}`;
                fileListContainer.appendChild(fileItem);
            });
        })
        .catch(error => console.error('Error fetching file list:', error));
}

// Load the first page on initial load
$(document).ready(function() {
    loadPage(1);
});