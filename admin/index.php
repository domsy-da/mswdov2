<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check admin access
checkAdminAccess();

// Include header
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="css/index.css">
<div class="admin-container">
    <div class="admin-header">
        <h1>Administration Panel</h1>
        <p class="admin-description">Manage system settings, users, and configurations</p>
    </div>

    <div class="admin-grid">
        <!-- User Management -->
        <a href="users/index.php" class="admin-card">
            <div class="card-icon">👥</div>
            <div class="card-content">
                <h3>User Management</h3>
                <p>Manage system users, roles, and permissions</p>
            </div>
        </a>

        <!-- Backup Management -->
        <a href="backup/index.php" class="admin-card">
            <div class="card-icon">💾</div>
            <div class="card-content">
                <h3>Database Backup</h3>
                <p>Create and manage system backups</p>
            </div>
        </a>

        <!-- Document Paths -->
        <a href="paths/index.php" class="admin-card">
            <div class="card-icon">📁</div>
            <div class="card-content">
                <h3>Document Paths</h3>
                <p>Configure system file paths and storage locations</p>
            </div>
        </a>

        <!-- Requirements & Services -->
        <a href="requirements/index.php" class="admin-card">
            <div class="card-icon">📋</div>
            <div class="card-content">
                <h3>Requirements & Services</h3>
                <p>Manage system requirements and available services</p>
            </div>
        </a>

        <!-- Budget  -->
        <a href="budget/index.php" class="admin-card">
            <div class="card-icon">💵</div>
            <div class="card-content">
                <h3>Budget Management</h3>
                <p>Manage system budget and financial allocations</p>
            </div>
        </a>
        <!-- Places Attributes  -->
        <a href="place_attr/index.php" class="admin-card">
            <div class="card-icon">📍</div>
            <div class="card-content">
                <h3>Places Attributes</h3>
                <p>Manage attributes for barangays and sitios</p>
            </div>
        </a>
        <!-- Combined Transactions  -->
        <a href="combined_transactions/index.php" class="admin-card">
            <div class="card-icon">📦</div>
            <div class="card-content">
                <h3>Combined Transactions</h3>
                <p>Manage combined transactions for beneficiaries</p>
            </div>
        </a>
    </div>
</div>
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

<?php
// Include footer if you have one
// require_once '../includes/footer.php';
?>