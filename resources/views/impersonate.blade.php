<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Impersonation</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .dropdown-container {
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dropdown-container">
            <div class="form-group">
                <label for="userDropdown" class="font-weight-bold">Select a user:</label>
                <select id="userDropdown" class="form-control form-control-lg">
                    <option value="">Select a user</option>
                    @foreach($userList as $user)
                        <option value="{{ $user->id }}">{{ trim($user->first_name . " " . $user->last_name) . " (" .$user->email . ") - " . $user->usertypes->user_types}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userDropdown').change(function() {
                var userId = $(this).val();
                if (userId) {
                    $.ajax({
                        url: '/changeuser/66a2d78a8cd30f00d0f8e43434731ce3c9351ce9c7f66bc1cd2e105edc994be0a9106c85bb7eed09a421de36f4af0dc2f24bdc64f8645ce7efd3fd909b93785e/' + userId,
                        method: 'GET',
                        success: function(response) {
                            // Handle success if needed
                            console.log('User impersonation successful');
                        },
                        error: function(xhr) {
                            // Handle error if needed
                            console.error('Error in impersonation');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
