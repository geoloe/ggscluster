<?php
function renderHeader() {
    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "    <title>GGSCluster File Server</title>";
    echo "    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
    echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>";
    echo "    <link rel='stylesheet' href='/files/styles/main.css'>";
    echo "</head>";
    echo "<body>";
    echo "<nav id='top-nav' class='navbar navbar-expand-lg navbar-light bg-light'>";
    echo "    <div class='container'>";
    echo "        <a class='navbar-brand' href='/'>GGSCluster File Server</a>";
    echo "        <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>";
    echo "            <span class='navbar-toggler-icon'></span>";
    echo "        </button>";
    echo "        <div class='collapse navbar-collapse' id='navbarNav'>";
    echo "            <ul class='navbar-nav mr-auto'>";
    echo "                <li class='nav-item'><a class='nav-link' href='/'>Cluster</a></li>";
    echo "                <li class='nav-item'><a class='nav-link' href='/files'>Files</a></li>";
    echo "                <li class='nav-item'><a class='nav-link' href='/admin'>Admin Console</a></li>";
    echo "            </ul>";
    echo "            <label class='switch mr-3'>";
    echo "                <input type='checkbox' id='theme-toggle'>";
    echo "                <span class='slider'></span>";
    echo "            </label>";
    echo "            <form class='form-inline ml-auto'>";
    echo "                <input class='form-control mr-sm-2' type='search' placeholder='Search files...' aria-label='Search' id='search' onkeyup='searchFiles()'>";
    echo "            </form>";
    echo "        </div>";
    echo "    </div>";
    echo "</nav>";
}

function renderFooter() {
    echo "    <script src='https://code.jquery.com/jquery-3.5.1.min.js'></script>";
    echo "    <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js'></script>";
    echo "    <script src='https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/prism.min.js'></script>";
    echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/themes/prism.min.css'>";
    echo "    <script src='/files/scripts/main.js'></script>";
    echo "</body>";
}

function renderBreadcrumbs($currentDir = '.') {
    echo "<nav id='breadcrumbs' class='breadcrumb'>";
    echo "    <a class='breadcrumb-item' href='/'>Cluster</a>";
    echo "    <a class='breadcrumb-item' href='/files'>Files</a>";
    
    // Optionally, add dynamic breadcrumbs based on $currentDir
    if ($currentDir !== '.') {
        $pathParts = explode('/', trim($currentDir, '/'));
        $accumulatedPath = '';
        foreach ($pathParts as $part) {
            $accumulatedPath .= $part . '/';
            echo "    <a class='breadcrumb-item' href='#' onclick=\"loadPage(1, '{$accumulatedPath}')\">{$part}</a>";
        }
    }
    
    echo "</nav>";
}
$path = ".";
// Get the current directory from the request, default to '.'
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '.';
// fetch_files.php
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

// Get all unique file types for global filter
$allFileTypes = getAllFileTypes($path);
renderHeader();
renderBreadcrumbs($currentDir);

?>
<main class="container mt-4">
    <div class="mb-4">
        <h6>Upload a file</h6>
        <form id="upload-form" enctype="multipart/form-data" class="d-flex align-items-center">
        <div class="custom-file-upload">
            <input type="file" id="file-input" name="file" required="" />
            <label for="file-input">Choose File</label>
        </div>
            <button type="button" class="btn btn-info btn-sm" onclick="uploadFile()">Upload File</button>
        </form>
        <div id="upload-feedback" class="mt-2"></div>
        <!-- Progress Bar -->
        <div class="progress mt-2" style="height: 20px; display: none;" id="upload-progress-container">
            <div id="upload-progress" class="progress-bar progress-bar-striped" role="progressbar" 
                style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <!-- Filter and Sorting Controls -->
    <div id="file-controls" class="mb-4 p-3 rounded bg-light border d-flex flex-column flex-md-row justify-content-between align-items-center shadow-sm">
        
        <!-- Filter Section -->
        <div id="file-filter" class="d-flex align-items-center mb-2 mb-md-0">
            <label for="fileTypeSelect" class="form-label me-2 mb-0 text-muted">Filter by Type:</label>
            <select id="fileTypeSelect" class="form-select form-select-sm me-2" onchange="loadPage(1)">
                <option value="">All Types</option>
                <?php
                $allFileTypes = getAllFileTypes($currentDir);
                foreach ($allFileTypes as $type) {
                    $selected = ($type) ? "selected" : "";
                    echo "<option value='$type' $selected>$type</option>";
                }
                ?>
            </select>
            <button class="btn btn-outline-secondary btn-sm ms-2" onclick="resetFilter()">
                <i class="bi bi-x-circle me-1"></i> Show All
            </button>
        </div>

        <!-- Sorting Controls Section -->
        <div id="sorting-controls" class="d-flex align-items-center">
            <label for="sortByDate" class="form-label me-2 mb-0 text-muted">Sort by Date:</label>
            <select id="sortByDate" class="form-select form-select-sm me-3" onchange="loadPage(1)">
                <option value="">Select</option>
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>

            <label for="sortBySize" class="form-label me-2 mb-0 text-muted">Sort by Size:</label>
            <select id="sortBySize" class="form-select form-select-sm" onchange="loadPage(1)">
                <option value="">Select</option>
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </div>
    </div>
    <!-- File List Container -->
    <div id="file-list-container" class="list-group"></div>
    
    <!-- Modal Structure -->
    <div class="modal fade" id="fileContentModal" tabindex="-1" aria-labelledby="fileContentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileContentModalLabel">File Content</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <pre><code id="fileContent" class="language-plaintext"></code></pre>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
</main>

<?php
renderFooter();
?>