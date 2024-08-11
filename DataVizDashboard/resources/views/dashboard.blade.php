<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">DASHBOARD</a>
            <a href="/logout" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
    <form id="upload" action="/import-csv" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".csv">
        <button type="submit">Upload</button>
    </form>
    <div id="add-response" class="mt-3"></div>
    <a id="view-dashboard" href="{{ route('datadashboard') }}" class="btn btn-primary mt-3" style="display: none;">View Dashboard</a>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#upload').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: '/import-csv',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#add-response').html(`<div class="alert alert-success">${response.success}</div>`);
                    },
                    error: function(xhr) {
                        $('#add-response').html(`<div class="alert alert-danger">${xhr.responseJSON.error || xhr.responseText}</div>`);
                    }
                });
            });
        });
    </script>
    <script>
    document.getElementById('upload').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        
        var formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            // Check if the upload was successful and display the button
            if (data.success) {
                document.getElementById('view-dashboard').style.display = 'inline-block';
                document.getElementById('add-response').innerText = 'File uploaded successfully!';
            } else {
                document.getElementById('add-response').innerText = 'File upload failed!';
            }
        })
        .catch(error => {
            document.getElementById('add-response').innerText = 'An error occurred!';
        });
    });
</script>
</body>
</html>
