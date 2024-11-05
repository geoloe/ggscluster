<?php
// Directory where files will be uploaded
$targetDirectory = '/usr/local/apache2/htdocs/files/uploads/'; // Make sure this ends with a slash

// Ensure the directory exists
if (!is_dir($targetDirectory)) {
    mkdir($targetDirectory, 0777, true);
}

// Check if a file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']);
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

    // Set limit for file size to 1 GB
    $maxFileSize = 1024 * 1024 * 1024; // 1 GB in bytes

    $allowedTypes = [
        // Images
        'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'image/svg+xml',
        
        // Documents
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation', 'application/rtf', 'text/plain', 'text/csv',
        'application/zip', 'application/x-tar', 'application/x-rar-compressed', 'application/x-7z-compressed',
        'application/x-zip-compressed', 'application/x-gzip', 'application/x-bzip2',

        // Audio
        'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/aac', 'audio/mp3', 'audio/x-flac',

        // Video
        'video/mp4', 'video/mpeg', 'video/ogg', 'video/webm', 'video/x-msvideo', 'video/quicktime',

        // Code files
        'application/json', 'application/xml', 'text/html', 'text/css', 'text/javascript', 'application/javascript',
        'text/x-python', 'text/x-php', 'text/x-shellscript', 'application/x-java', 'application/x-httpd-php',

        //Cert files
        'application/x-x509-ca-cert',   // For .crt files
        'application/x-pem-file',       // For .pem files
        'application/octet-stream',     // For .key files (common default MIME)
        'application/x-pkcs12'          // For .p12 or .pfx certificate files
    ];

    // Check file size
    if ($fileSize > $maxFileSize) {
        echo "File size exceeds the 1 GB limit.";
        exit;
    }

    // Check file type
    if (!in_array($fileType, $allowedTypes)) {
        echo "Unsupported file type.";
        exit;
    }

    // Secure the file name to prevent directory traversal attacks
    $fileName = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "_", $fileName);
    $destination = $targetDirectory . $fileName; // Make sure this is correct

    // Debug line to check paths
    //echo "Temp file: $fileTmpPath, Destination: $destination"; // Debug line

    // Move the file to the target directory
    if (move_uploaded_file($fileTmpPath, $destination)) {
        echo "File uploaded successfully!";
    } else {
        echo "There was an error moving the uploaded file.";
    }
} else {
    // Capture the error code and output a message
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            echo "File size exceeds the allowed limit.";
            break;
        case UPLOAD_ERR_PARTIAL:
            echo "File was only partially uploaded.";
            break;
        case UPLOAD_ERR_NO_FILE:
            echo "No file was uploaded.";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            echo "Missing a temporary folder.";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            echo "Failed to write file to disk.";
            break;
        case UPLOAD_ERR_EXTENSION:
            echo "A PHP extension stopped the file upload.";
            break;
        default:
            echo "Unknown upload error.";
            break;
    }
    exit;
}
?>