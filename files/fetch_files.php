<?php
// Fetch parameters from AJAX request
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 2; // Number of items per page
$offset = ($currentPage - 1) * $limit;
$path = '.'; // Define the base directory

// Function to get the filtered list of files/directories for pagination
function getFilteredFileList($dir) {
    $allFiles = array_diff(scandir($dir), array('.', '..'));
    $filteredFiles = [];

    foreach ($allFiles as $file) {
        $filePath = $dir . '/' . $file;
        // Exclude top-level files (e.g., index.php, fetch_files.php)
        if (is_file($filePath) && ($file === 'index.php' || $file === 'fetch_files.php' || $file === 'upload.php')) {
            continue;
        }
        // Exclude hidden directories (e.g., 'styles' and 'scripts')
        if (is_dir($filePath) && ($file === 'styles' || $file === 'scripts')) {
            continue;
        }
        $filteredFiles[] = $file;
    }

    return $filteredFiles;
}

// Function to render the file list for the current page
function renderFileList($files, $dir) {
    echo "<ul class='list-group pl-3'>";
    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;

        if (is_dir($filePath)) {
            echo "<li class='list-group-item'><strong>$file/</strong></li>";
            // Recursively list files in the subdirectory
            renderFileList(getFilteredFileList($filePath), $filePath);
        } else {
            // Generate relative path for the file link within subdirectories
            $relativePath = str_replace('./', '', "$dir/$file");

            // Extract file extension to determine the file type
            $fileType = pathinfo($file, PATHINFO_EXTENSION);

            echo "<li class='list-group-item d-flex justify-content-between align-items-left'>";
            echo "    <a href='/files/$relativePath' class='mr-2'>$file</a>";
            echo "    <button class='btn btn-primary btn-sm' onclick='showContent(\"/files/$relativePath\", \"$fileType\")'>Show Content</button>";
            echo "</li>";
        }
    }
    echo "</ul>";
}

// Get the filtered list of files for pagination
$filteredFiles = getFilteredFileList($path);
$totalFiles = count($filteredFiles);
$totalPages = ceil($totalFiles / $limit);
$filesForPage = array_slice($filteredFiles, $offset, $limit);

// Render the file list for the current page
renderFileList($filesForPage, $path);

// Output pagination controls for AJAX
echo "<nav aria-label='File pagination'>";
echo "    <ul class='pagination justify-content-center'>";

if ($currentPage > 1) {
    $prevPage = $currentPage - 1;
    echo "<li class='page-item'><a class='page-link' href='#' onclick='loadPage($prevPage)'>Previous</a></li>";
}

for ($i = 1; $i <= $totalPages; $i++) {
    $activeClass = ($i == $currentPage) ? 'active' : '';
    echo "<li class='page-item $activeClass'><a class='page-link' href='#' onclick='loadPage($i)'>$i</a></li>";
}

if ($currentPage < $totalPages) {
    $nextPage = $currentPage + 1;
    echo "<li class='page-item'><a class='page-link' href='#' onclick='loadPage($nextPage)'>Next</a></li>";
}

echo "</ul>";
echo "</nav>";