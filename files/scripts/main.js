// Function to search for files
function searchFiles() {
    let input = document.getElementById('search').value.toLowerCase();
    let items = document.getElementsByClassName('list-group-item');

    for (let item of items) {
        let name = item.textContent.toLowerCase();
        item.style.display = name.includes(input) ? "" : "none";
    }
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

function showContent(filePath, fileType) {
    // Fetch file content from the server
    fetch(filePath)
        .then(response => {
            if (!response.ok) {
                throw new Error("File not found");
            }
            return response.text();
        })
        .then(content => {
            // Insert content into modal and set syntax highlighting class based on fileType
            document.getElementById("fileContent").textContent = content;
            document.getElementById("fileContent").className = `language-${fileType || 'plaintext'}`;

            // Show the modal
            $('#fileContentModal').modal('show');

            // Re-highlight the syntax
            Prism.highlightAll();
        })
        .catch(error => {
            console.error("Error fetching file content:", error);
        });
}

// Load the first page on initial load
$(document).ready(function() {
    loadPage(1);
});