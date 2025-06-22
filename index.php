<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSWDO Beneficiary Management System</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div id="toast" class="toast" style="display: none;"></div>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1 class="hero-title">Welcome to MSWDO APP</h1>
                    <p class="hero-subtitle">Streamlining Social Services for Better Community Support</p>
                    
                    <div class="hero-description">
                        <p>Our comprehensive management system helps Municipal Social Welfare and Development Offices efficiently track, manage, and support beneficiaries across various social programs. From registration to certificate generation, we provide the tools needed to deliver effective social services to your community.</p>
                    </div>
                    
                    <div class="cta-section">
                        <?php
                        
                        if (!isset($_SESSION['user_id'])) {
                            // Not logged in - show login button
                            ?>
                            <a href="auth/login.php" class="btn btn-primary">Login to System</a>
                            <?php
                        } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                            // Logged in as admin
                            ?>
                            <a href="admin/index.php" class="btn btn-primary">Admin</a>
                            <?php
                        }
                        // If logged in but not admin, nothing will show
                        ?>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="features">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>Beneficiary Management</h3>
                        <p>Efficiently register, update, and track beneficiary information across all social programs.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Resource Allocation</h3>
                        <p>Monitor and manage the distribution of resources and assistance to ensure fair allocation.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Report Generation</h3>
                        <p>Generate comprehensive reports for program evaluation and compliance requirements.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Certificate Issuance</h3>
                        <p>Create and manage official certificates and documentation for beneficiaries.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 MSWDO Beneficiary Management System. All rights reserved.</p>
        </div>
    </footer>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['toast_message'])): ?>
        showToast('<?= htmlspecialchars($_SESSION['toast_message']) ?>', '<?= $_SESSION['toast_type'] ?>');
        <?php 
        // Clear the toast message
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
        ?>
    <?php endif; ?>
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
</script>
</body>
</html>
