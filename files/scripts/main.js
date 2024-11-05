// Function to search for files
function searchFiles() {
    let input = document.getElementById('search').value.toLowerCase();
    let items = document.getElementsByClassName('list-group-item');

    for (let item of items) {
        let name = item.textContent.toLowerCase();
        item.style.display = name.includes(input) ? "" : "none";
    }
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

function resetFilter() {
    document.getElementById('fileTypeSelect').selectedIndex = 0; // Reset dropdown selection
    $.ajax({
        url: 'fetch_files.php?page=1', // Reset to page 1 for all files
        method: 'GET',
        success: function (data) {
            $('#file-list-container').html(data); // Update your file list container
        },
        error: function () {
            console.error('Error resetting filter');
        }
    });
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
function loadPage(page, dir = '.', sortByDate = null, sortBySize = null) {
    // Make an AJAX request to fetch the files
    $.ajax({
        url: 'fetch_files.php', // Your PHP file to handle the request
        type: 'GET',
        data: { 
            page: page, 
            dir: dir,
            sortByDate: sortByDate,
            sortBySize: sortBySize 
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

// Load the first page on initial load
$(document).ready(function() {
    loadPage(1);
});