<?php
include '../includes/db.php';
include '../includes/auth_check.php';

// Handle Add Resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO resources (name, description, quantity) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['quantity']
    ]);
    header('Location: index.php');
    exit;
}

// Fetch resources
$stmt = $pdo->query("SELECT * FROM resources ORDER BY date_added DESC");
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - MSWDO Management System</title>
    <link rel="stylesheet" href="resources.css" />
</head>
<body>
    <div id="toast" class="toast" style="display: none;"></div>
    
    <?php include '../includes/header.php'; ?>
    <main class="main-content">
        <div class="container programs-section">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Program Management</h1>
                    <p class="page-subtitle">Manage and track MSWDO programs</p>
                </div>
                <div class="header-actions">
                    <button id="toggleFormBtn" class="btn btn-primary">
                        ➕ Add New Program
                    </button>
                </div>
            </div>

            <!-- Add Program Form -->
            <form id="programForm" class="form" style="display: none;">
                <h2 class="form-title">Add New Program</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="program_name">Program Name:</label>
                        <input type="text" id="program_name" name="program_name" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="program_type">Program Type:</label>
                        <select id="program_type" name="program_type" required>
                            <option value="">Select Type</option>
                            <option value="Educational">Educational</option>
                            <option value="Medical">Medical</option>
                            <option value="Financial">Financial</option>
                            <option value="Social">Social</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="target_beneficiaries">Target Beneficiaries:</label>
                        <input type="number" id="target_beneficiaries" name="target_beneficiaries" min="1" required />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="program_description">Description:</label>
                    <textarea id="program_description" name="program_description"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Program</button>
            </form>

            <!-- Programs Grid -->
            <div class="programs-grid">
                <div class="grid-header">
                    <h2 class="grid-title">Active Programs</h2>
                </div>
                <div class="grid-container">
                    <?php
                    // Fetch programs
                    $stmt = $pdo->query("SELECT * FROM programs ORDER BY created_at DESC");
                    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($programs) > 0):
                        foreach ($programs as $program):
                    ?>
                        <div class="program-card">
                            <div class="program-type"><?= htmlspecialchars($program['program_type']) ?></div>
                            <h3 class="program-name"><?= htmlspecialchars($program['program_name']) ?></h3>
                            <p class="program-description"><?= htmlspecialchars($program['program_description']) ?></p>
                            <div class="program-stats">
                                <div class="stat">
                                    <span class="stat-label">Target:</span>
                                    <span class="stat-value"><?= number_format($program['target_beneficiaries']) ?> beneficiaries</span>
                                </div>
                            </div>
                            <div class="program-actions">
                                <button class="btn-edit" onclick="editProgram(<?= $program['id'] ?>)" title="Edit">✏️</button>
                                <button class="btn-delete" onclick="deleteProgram(<?= $program['id'] ?>)" title="Delete">🗑️</button>
                                <a href="recommended_beneficiaries.php?program_id=<?= $program['id'] ?>" 
                                   class="btn-recommend" 
                                   title="View recommended beneficiaries for <?= htmlspecialchars($program['program_name']) ?>">
                                    👥
                                </a>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <div class="empty-state">No programs found. Add your first program using the form above.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="divider"></div>
        

        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Resource Management</h1>
                    <p class="page-subtitle">Track and manage all resources in the system</p>
                </div>
                <div class="header-actions">
                    <a href="distribution.php" class="btn btn-primary">
                        📦 Distribute Resources
                    </a>
                </div>
            </div>

            <!-- Add Resource Form -->
            <form method="POST" class="form">
                <h2 class="form-title">Add New Resource</h2>
                <input type="hidden" name="action" value="add" />
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Resource Name:</label>
                        <input type="text" id="name" name="name" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" min="0" required />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Resource</button>
            </form>

            <!-- Resources Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Available Resources</h2>
                </div>
                <table class="beneficiaries-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resources) > 0): ?>
                            <?php foreach ($resources as $res): ?>
                                <tr>
                                    <td><?= htmlspecialchars($res['name']) ?></td>
                                    <td><?= htmlspecialchars($res['description']) ?></td>
                                    <td><?= htmlspecialchars($res['quantity']) ?></td>
                                    <td><?= htmlspecialchars($res['date_added']) ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $res['id'] ?>" class="btn-edit" title="Edit">✏️</a>
                                        <a href="delete.php?id=<?= $res['id'] ?>" class="btn-delete" title="Delete" onclick="return confirm('Are you sure?')">🗑️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">No resources found. Add your first resource using the form above.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="display: none;">
            <form action="https://mswdoc.ct.ws/process.php" method="GET" id="add-program-ol">
                <input type="text" name="program_name1" />
                <input type="text" name="program_type1" />
                <input type="text" name="target_beneficiaries1" />
                <input type="text" name="program_description1" />
            </form>
        </div>
    </main>
    
    <div style="
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120%;
        height: 120%;
        z-index: 1;
        opacity: 0.02;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    ">
        <img src="../assets/img/mswdologo2.jpg" alt="" style="
            width: 100%;
            height: 100%;
            object-fit: contain;
        ">
    </div>
    <style>
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 4px;
        z-index: 9999;
        font-size: 0.9rem;
        max-width: 350px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .toast.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .toast.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const programForm = document.getElementById('programForm');

    toggleFormBtn.addEventListener('click', function() {
        programForm.classList.toggle('show');
        programForm.style.display = programForm.classList.contains('show') ? 'block' : 'none';
        
        // Change button text based on form visibility
        this.textContent = programForm.classList.contains('show') 
            ? '✖ Close Form' 
            : '➕ Add New Program';
    });

    document.getElementById('programForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('add_program.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                // Submit the other form after the alert
                document.getElementById('add-program-ol').submit();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });

    // Check for toast message in session
    <?php if (isset($_SESSION['toast_message'])): ?>
        showToast('<?= htmlspecialchars($_SESSION['toast_message']) ?>', '<?= $_SESSION['toast_type'] ?>');
        <?php 
        // Clear the toast message
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
        ?>
    <?php endif; ?>

    // Get references to the first form inputs
    const programName = document.getElementById('program_name');
    const programType = document.getElementById('program_type');
    const targetBeneficiaries = document.getElementById('target_beneficiaries');
    const programDescription = document.getElementById('program_description');

    // Get references to the second form inputs
    const programName1 = document.querySelector('input[name="program_name1"]');
    const programType1 = document.querySelector('input[name="program_type1"]');
    const targetBeneficiaries1 = document.querySelector('input[name="target_beneficiaries1"]');
    const programDescription1 = document.querySelector('input[name="program_description1"]');

    // Add input event listeners to sync the values
    programName.addEventListener('input', function() {
        programName1.value = this.value;
    });

    programType.addEventListener('change', function() {
        programType1.value = this.value;
    });

    targetBeneficiaries.addEventListener('input', function() {
        targetBeneficiaries1.value = this.value;
    });

    programDescription.addEventListener('input', function() {
        programDescription1.value = this.value;
    });
});

function showToast(message, type) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast ' + type;
    toast.style.display = 'block';
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.style.display = 'none';
            toast.style.opacity = '1';
        }, 300);
    }, 3000);
}

function editProgram(id) {
    // Implement edit functionality
    window.location.href = `edit_program.php?id=${id}`;
}

function deleteProgram(id) {
    if (confirm('Are you sure you want to delete this program?')) {
        fetch(`delete_program.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.status);
                if (data.status === 'success') {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                showToast('Error deleting program', 'error');
            });
    }
}
</script>
</body>
</html>
