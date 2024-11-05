<?php
// Fetch parameters from AJAX request
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of items per page
$offset = ($currentPage - 1) * $limit;
$path = '.'; // Define the base directory

// Fetch fileType from the request, default to null
$fileType = isset($_GET['fileType']) ? $_GET['fileType'] : null;

// Get all unique file types for global filter
$allFileTypes = getAllFileTypes($path);

// Render the filter dropdown based on all file types
echo "<div id='file-filter' class='mb-4'>";
echo "<select id='fileTypeSelect' onchange='filterFiles(this.value)'>";
echo "<option value=''>Select file type</option>"; // Default option

// Loop through all file types to populate the dropdown
foreach ($allFileTypes as $type) {
    // Maintain the selected option
    $selected = ($type === $fileType) ? "selected" : "";
    echo "<option value='$type' $selected>$type</option>";
}
echo "</select>";
echo "<button class='btn btn-secondary' onclick='resetFilter()'>Show All</button>"; // Button to reset filter
echo "</div>";

// Add sorting controls for size and date
echo "<div id='sorting-controls' class='mb-4'>";
echo "<label for='sortByDate'>Sort by Date:</label>";
echo "<select id='sortByDate' onchange='sortFiles()'>";
echo "<option value=''>Select</option>";
echo "<option value='asc'>Ascending</option>";
echo "<option value='desc'>Descending</option>";
echo "</select>";

echo "<label for='sortBySize'>Sort by Size:</label>";
echo "<select id='sortBySize' onchange='sortFiles()'>";
echo "<option value=''>Select</option>";
echo "<option value='asc'>Ascending</option>";
echo "<option value='desc'>Descending</option>";
echo "</select>";
echo "</div>";

// Get the filtered list of files for pagination
$filteredFiles = getFilteredFileList($path, $fileType);
$totalFiles = count($filteredFiles);

// Sort the files based on user selection
if (isset($_GET['sortByDate']) && $_GET['sortByDate'] === 'asc') {
    usort($filteredFiles, fn($a, $b) => filemtime($a) <=> filemtime($b)); // Sort by date ascending
} elseif (isset($_GET['sortByDate']) && $_GET['sortByDate'] === 'desc') {
    usort($filteredFiles, fn($a, $b) => filemtime($b) <=> filemtime($a)); // Sort by date descending
} elseif (isset($_GET['sortBySize']) && $_GET['sortBySize'] === 'asc') {
    usort($filteredFiles, fn($a, $b) => filesize($a) <=> filesize($b)); // Sort by size ascending
} elseif (isset($_GET['sortBySize']) && $_GET['sortBySize'] === 'desc') {
    usort($filteredFiles, fn($a, $b) => filesize($b) <=> filesize($a)); // Sort by size descending
}

$totalFiles = count($filteredFiles);
$totalPages = ceil($totalFiles / $limit);
$filesForPage = array_slice($filteredFiles, $offset, $limit);

// Render file list for the current page
foreach ($filesForPage as $file) {
    // Get file extension to determine file type for syntax highlighting
    $fileType = pathinfo($file, PATHINFO_EXTENSION);
    $fileName = basename($file); // Get the file name only

    // Get file size and creation date
    $fileSize = filesize($file); // Get file size in bytes
    $fileCreationDate = date("F d, Y H:i:s", filemtime($file)); // Get creation date

    // Get the relative path for the breadcrumb
    $relativePath = str_replace($path . '/', '', $file); // Remove the base path
    $pathParts = explode('/', $relativePath); // Split the path into parts

    // Create breadcrumb string with links
    $breadcrumbItems = [];
    $currentPath = ''; // To build the current path for each part

    foreach ($pathParts as $index => $part) {
        $currentPath .= $part . '/'; // Build the current path
        
        // Check if it's the last part (the file)
        if ($index === count($pathParts) - 1) {
            // If it's the file, create a clickable link with the relative path
            $breadcrumbItems[] = "<a href='{$filePathForLink}{$relativePath}' onclick=\"showContent('{$file}', '{$fileType}')\">{$part}</a>";
        } else {
            // If it's a directory, just display the name without a link
            $breadcrumbItems[] = "<span>{$part}</span>";
        }
    }

    // Join breadcrumb items with ' > '
    $breadcrumb = implode(' &gt; ', $breadcrumbItems);

    // Format file size to KB/MB for better readability
    $formattedSize = $fileSize < 1024 ? "{$fileSize} bytes" : ($fileSize < 1048576 ? round($fileSize / 1024, 2) . " KB" : round($fileSize / 1048576, 2) . " MB");

    // Create a wrapper for breadcrumb and file info to use flexbox for alignment
    echo "<div class='list-group-item d-flex justify-content-between align-items-center'>";
    echo "<span>{$breadcrumb}</span>"; // Show breadcrumb
    echo "<span class='file-info ml-auto'>Size: {$formattedSize} | Created: {$fileCreationDate}</span>"; // Show file size and creation date, aligned to the right
    echo "<button class='btn btn-info btn-sm' onclick='showContent(\"{$file}\", \"{$fileType}\")'>Show Content</button>"; // Button to show content
    echo "</div>";
}

// Function to get all unique file types, excluding 'php'
function getAllFileTypes($dir) {
    $types = [];
    $allFiles = getAllFiles($dir);
    
    foreach ($allFiles as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (!in_array($ext, $types) && $ext !== 'php') { // Exclude 'php' and 'js'
            $types[] = $ext; // Add unique types
        }
    }
    return $types;
}

// Function to get all files recursively
function getAllFiles($dir) {
    $files = [];
    $dirContents = scandir($dir);
    
    foreach ($dirContents as $item) {
        if ($item === '.' || $item === '..') {
            continue; // Skip current and parent directory references
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            $files = array_merge($files, getAllFiles($path)); // Recursive call for directories
        } else {
            $files[] = $path; // Add the file to the list
        }
    }
    
    return $files; // Return all found files
}

// Function to get the filtered list of files/directories for pagination
function getFilteredFileList($dir, $fileType = null) {
    $allFiles = getAllFiles($dir); // Get all files recursively
    $filteredFiles = [];

    foreach ($allFiles as $file) {
        // Exclude PHP files and specific directories
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php' || 
            strpos($file, '/styles/') !== false || 
            strpos($file, '/scripts/') !== false) {
            continue; // Skip PHP files and files in styles or scripts directories
        }

        // Check if fileType is specified and matches the file type
        if ($fileType && pathinfo($file, PATHINFO_EXTENSION) !== $fileType) {
            continue; // Skip files that do not match the filter
        }

        $filteredFiles[] = $file; // Add to the list of filtered files
    }

    return $filteredFiles;
}


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
?>