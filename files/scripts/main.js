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