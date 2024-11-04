<?php
// Function to render the HTML header
// Function to render the HTML header
function renderHeader() {
    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "    <title>GGSCluster File Server</title>";
    echo "    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
    echo "    <link rel='stylesheet' href='/files/styles/main.css'>";
    echo "</head>";
    echo "<body>";
    echo "<nav class='navbar navbar-expand-lg navbar-light bg-light'>";
    echo "    <div class='container'>";
    echo "        <a class='navbar-brand' href='/'>GGSCluster File Server</a>";
    echo "        <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>";
    echo "            <span class='navbar-toggler-icon'></span>";
    echo "        </button>";
    echo "        <div class='collapse navbar-collapse' id='navbarNav'>";
    echo "            <ul class='navbar-nav'>";
    echo "                <li class='nav-item'>";
    echo "                    <a class='nav-link' href='/'>Cluster</a>";
    echo "                </li>";
    echo "                <li class='nav-item'>";
    echo "                    <a class='nav-link' href='/files'>Files</a>";
    echo "                </li>";
    echo "                <li class='nav-item'>";
    echo "                    <a class='nav-link' href='/admin'>Admin Console</a>";
    echo "                </li>";
    echo "            </ul>";
    echo "            <form class='form-inline ml-auto'>";
    echo "                <input class='form-control mr-2' type='search' placeholder='Search files...' aria-label='Search' id='search' onkeyup='searchFiles()'>";
    echo "            </form>";
    echo "        </div>";
    echo "    </div>";
    echo "</nav>";
}

// Function to render the HTML footer
function renderFooter() {
    // Changed to full jQuery version
    echo "    <script src='https://code.jquery.com/jquery-3.5.1.min.js'></script>";
    echo "    <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js'></script>";
    echo "    <script src='/files/scripts/main.js'></script>";
    echo "</body>";
}

// Function to render breadcrumbs
function renderBreadcrumbs() {
    echo "<nav id='breadcrumbs' class='breadcrumb'>";
    echo "    <a class='breadcrumb-item' href='/'>Cluster</a>";
    echo "    <a class='breadcrumb-item' href='/files'>Files</a>";
    echo "</nav>";
}

// Function to render the file list recursively
function renderFileList($dir, $baseDir) {
    $files = array_diff(scandir($dir), array('.', '..'));

    echo "<ul class='list-group pl-3'>"; // Added padding for better structure

    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        if (is_dir($filePath)) {
            // Exclude specific folders
            if ($file !== 'styles' && $file !== 'scripts') {
                echo "<li class='list-group-item'><strong>$file/</strong></li>"; // Display directory name
                renderFileList($filePath, $baseDir); // Recursive call to list files in the subdirectory
            }
        } else {
            // Exclude certain files
            if ($file !== 'index.php') {
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                echo "    <a href='/files/$dir/$file' class='mr-2'>$file</a>"; // Add a link to the file
                echo "    <button class='btn btn-primary btn-sm' onclick='showContent(\"$file\",\"$dir\")'>Show Content</button>";
                echo "</li>";
            }
        }
    }    

    echo "</ul>";
}

// Main logic
$path = '.'; // Set the path to the current directory

// Render the page
renderHeader();
renderBreadcrumbs();
echo "<main class='container mt-4'>";
echo "    <h2>Files</h2>";
echo "    <div class='input-group mb-3'>";
echo "    </div>";
renderFileList($path, $path); // Render the file list
echo "</main>";
renderFooter();
?>