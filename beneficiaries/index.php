<?php
require_once '../includes/db.php';
include '../includes/auth_check.php';

// Fetch all barangays for filter
$stmt = $pdo->query("SELECT DISTINCT barangay FROM beneficiaries ORDER BY barangay");
$barangays = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch beneficiaries with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Select only essential columns for the table
$stmt = $pdo->prepare("
    SELECT id, full_name, age, gender, barangay, date_added 
    FROM beneficiaries 
    ORDER BY date_added DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$total = $pdo->query("SELECT COUNT(*) FROM beneficiaries")->fetchColumn();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiaries Management - MSWDO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1>Beneficiaries Management</h1>
                    <p>Manage and track beneficiaries in the system</p>
                </div>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Beneficiary
                </a>
            </div>

            <!-- Search and Filters -->
            <div class="filters-section">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search by name..." class="search-input">
                    <select id="filterBarangay" class="filter-select">
                        <option value="">All Barangays</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?= htmlspecialchars($barangay) ?>">
                                <?= htmlspecialchars($barangay) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterGender" class="filter-select">
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <button onclick="resetFilters()" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset Filters
                    </button>
                </div>
            </div>

            <!-- Beneficiaries Table -->
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Full Name</th>
                            <th><i class="fas fa-birthday-cake"></i> Age</th>
                            <th><i class="fas fa-venus-mars"></i> Gender</th>
                            <th><i class="fas fa-map-marker-alt"></i> Barangay</th>
                            <th><i class="fas fa-calendar"></i> Date Added</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="beneficiariesTable">
                        <?php foreach ($beneficiaries as $beneficiary): ?>
                            <tr>
                                <td>
                                    <span class="status-indicator status-active"></span>
                                    <?= htmlspecialchars($beneficiary['full_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($beneficiary['age']) ?></td>
                                <td>
                                    <i class="fas fa-<?= $beneficiary['gender'] === 'Male' ? 'mars' : 'venus' ?>"></i>
                                    <?= htmlspecialchars($beneficiary['gender']) ?>
                                </td>
                                <td><?= htmlspecialchars($beneficiary['barangay']) ?></td>
                                <td><?= date('M d, Y', strtotime($beneficiary['date_added'])) ?></td>
                                <td class="actions">
                                    <a href="view.php?id=<?= $beneficiary['id'] ?>" class="btn btn-info btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $beneficiary['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteBeneficiary(<?= $beneficiary['id'] ?>)" 
                                            class="btn btn-danger btn-sm"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($totalPages > 1): ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" 
                           class="page-link <?= $page === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <div style="
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120%;
        height: 120%;
        z-index: -1;
        opacity: 0.05;
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

    <script>
        // Search and filter functionality
        function filterTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const barangay = document.getElementById('filterBarangay').value.toLowerCase();
            const gender = document.getElementById('filterGender').value.toLowerCase();
            const rows = document.getElementById('beneficiariesTable').getElementsByTagName('tr');

            for (let row of rows) {
                const name = row.cells[0].textContent.toLowerCase();
                // Extract only the gender text, removing extra whitespace
                const rowGender = row.cells[2].textContent.trim().split('\n').pop().trim().toLowerCase();
                const rowBarangay = row.cells[3].textContent.toLowerCase();

                const matchesSearch = name.includes(search);
                const matchesBarangay = !barangay || rowBarangay === barangay;
                const matchesGender = !gender || rowGender === gender;

                row.style.display = (matchesSearch && matchesBarangay && matchesGender) ? '' : 'none';
            }
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('filterBarangay').addEventListener('change', filterTable);
        document.getElementById('filterGender').addEventListener('change', filterTable);

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterBarangay').value = '';
            document.getElementById('filterGender').value = '';
            filterTable();
        }

        function deleteBeneficiary(id) {
            if (confirm('Are you sure you want to delete this beneficiary?')) {
                fetch('delete_beneficiary.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting beneficiary');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting beneficiary');
                });
            }
        }

        // Add loading animation to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.classList.contains('btn-danger')) {
                    this.classList.add('loading');
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 1000);
                }
            });
        });
    </script>
</body>
</html>
