<?php
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
    echo "<nav id='top-nav' class='navbar navbar-expand-lg navbar-light bg-light'>";
    echo "    <div class='container'>";
    echo "        <a class='navbar-brand' href='/'>GGSCluster File Server</a>";
    echo "        <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>";
    echo "            <span class='navbar-toggler-icon'></span>";
    echo "        </button>";
    echo "        <div class='collapse navbar-collapse' id='navbarNav'>";
    echo "            <ul class='navbar-nav'>";
    echo "                <li class='nav-item'><a class='nav-link' href='/'>Cluster</a></li>";
    echo "                <li class='nav-item'><a class='nav-link' href='/files'>Files</a></li>";
    echo "                <li class='nav-item'><a class='nav-link' href='/admin'>Admin Console</a></li>";
    echo "            </ul>";
    echo "            <label class='switch'>";
    echo "                <input type='checkbox' id='theme-toggle'>";
    echo "                <span class='slider'></span>";
    echo "            </label>";
    echo "            <form class='form-inline ml-auto'>";
    echo "                <input class='form-control mr-2' type='search' placeholder='Search files...' aria-label='Search' id='search' onkeyup='searchFiles()'>";
    echo "            </form>";
    echo "        </div>";
    echo "    </div>";
    echo "</nav>";
}

// In the footer
function renderFooter() {
    echo "    <script src='https://code.jquery.com/jquery-3.5.1.min.js'></script>";
    echo "    <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js'></script>";
    echo "    <script src='https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/prism.min.js'></script>";
    echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/themes/prism.min.css'>";
    echo "    <script src='/files/scripts/main.js'></script>";
    echo "</body>";
}

function renderBreadcrumbs() {
    echo "<nav id='breadcrumbs' class='breadcrumb'>";
    echo "    <a class='breadcrumb-item' href='/'>Cluster</a>";
    echo "    <a class='breadcrumb-item' href='/files'>Files</a>";
    echo "</nav>";
}

$path = '.'; 

renderHeader();
renderBreadcrumbs();
?>
<main class="container mt-4">
    <div class="mb-4">
        <h5>Upload a New File - It will be uploaded to /files/uploads</h5>
        <form id="upload-form" enctype="multipart/form-data" class="d-flex align-items-center">
            <div class="form-group mb-0 me-2">
                <input type="file" class="form-control-file" id="file-input" name="file" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="uploadFile()">Upload File</button>
        </form>
        <div id="upload-feedback" class="mt-2"></div>
    </div>
    <div id="file-list-container"></div>
    
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