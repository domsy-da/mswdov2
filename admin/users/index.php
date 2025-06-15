<?php
require_once '../../includes/db.php';
require_once '../../classes/User.php';

$user = new User($pdo);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        if ($_POST['password'] === $_POST['confirm_password']) {
            if ($user->create($_POST['username'], $_POST['password'], $_POST['role'])) {
                $message = "User created successfully";
            } else {
                $message = "Unable to create user";
            }
        } else {
            $message = "Passwords do not match";
        }
    }
    
    if (isset($_POST['update'])) {
        if ($user->update($_POST['id'], $_POST['username'], $_POST['password'], $_POST['role'])) {
            $message = "User updated successfully";
        } else {
            $message = "Unable to update user";
        }
    }
    
    if (isset($_POST['delete'])) {
        if ($user->delete($_POST['id'])) {
            $message = "User deleted successfully";
        } else {
            $message = "Unable to delete user";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container mt-5">
        <!-- Add this back button section before the h2 title -->
        <div class="back-section">
            <a href="../index.php" class="back-button">Back to Dashboard</a>
        </div>
        
        <h2>User Management</h2>
        <?php if($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Create User Form -->
        <div class="card mb-4">
            <div class="card-header">Add New User</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirm Password:</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role:</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="user" selected>User</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>
                    <button type="submit" name="create" class="btn btn-primary">Create User</button>
                </form>
            </div>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="card-header">Users List</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $user->read();
                        while ($row = $result->fetch()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['username']}</td>";
                            echo "<td>{$row['role']}</td>";
                            echo "<td>{$row['created_at']}</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-warning me-2' onclick='showEditForm({$row['id']}, \"{$row['username']}\", \"{$row['role']}\")'>✒️</button>";
                            echo "<form method='POST' style='display: inline;'>";
                            echo "<input type='hidden' name='id' value='{$row['id']}'>";
                            echo "<button type='submit' name='delete' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>🗑</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label>Username:</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>New Password:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Role:</label>
                            <select name="role" id="edit_role" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                <option value="editor">Editor</option>
                            </select>
                        </div>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    include '../../includes/bg.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                }
            });
        });

        // Update your existing showEditForm function
        function showEditForm(id, username, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            
            const modal = document.getElementById('editModal');
            const modalInstance = new bootstrap.Modal(modal, {
                backdrop: true,
                keyboard: true
            });
            modalInstance.show();
        }
    </script>
</body>
</html>