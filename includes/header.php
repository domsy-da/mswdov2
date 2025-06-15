<?php
require_once __DIR__ . '/auth.php';

?>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <span class="logo-symbol">M</span>
                </div>
                <div class="logo-text">
                    <h2>MSWDO</h2>
                    <span class="logo-subtitle">Beneficiary Management</span>
                </div>
            </div>
            
            <nav class="nav" id="mainNav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="/mswdov2/index.php" class="nav-link" data-page="home">
                            <span class="nav-icon">🏠</span>
                            <span class="nav-text">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/mswdov2/beneficiaries/index.php" class="nav-link" data-page="beneficiaries">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Beneficiaries</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/mswdov2/resources/index.php" class="nav-link" data-page="resources">
                            <span class="nav-icon">📦</span>
                            <span class="nav-text">Resources</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/mswdov2/reports/index.php" class="nav-link" data-page="reports">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/mswdov2/history/index.php" class="nav-link" data-page="history">
                            <span class="nav-icon">📜</span>
                            <span class="nav-text">History</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a href="/mswdov2/admin/index.php" class="nav-link" data-page="admin">
                                <span class="nav-icon">⚙️</span>
                                <span class="nav-text">Admin Panel</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="nav-actions">
                    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                        <span class="theme-icon">🌙</span>
                    </button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-menu">
                            <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            <a href="/mswdov2/auth/logout.php" class="logout-btn">
                                <span class="logout-icon">🚪</span>
                                <span class="logout-text">Logout</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="/mswdov2/auth/login.php" class="login-btn">
                            <span class="login-icon">👤</span>
                            <span class="login-text">Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
            
            <!-- Mobile menu toggle -->
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</header>

<style>
    .user-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .username {
        color: #333;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background-color: #f5f5f5;
        color: #333;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
    }

    .logout-btn:hover {
        background-color: #e0e0e0;
        color: #000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .logout-icon {
        font-size: 1.1rem;
    }

    .logout-text {
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>

<script>
// Header functionality
function toggleMobileMenu() {
    const nav = document.getElementById('mainNav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    nav.classList.toggle('nav-open');
    toggle.classList.toggle('active');
    
    // Prevent body scroll when menu is open
    if (nav.classList.contains('nav-open')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}

// Set active navigation item based on current page
function setActiveNav() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        
        if (currentPath === href || 
            (currentPath.includes('/beneficiaries/') && href.includes('/beneficiaries/')) ||
            (currentPath.includes('/resources/') && href.includes('/resources/')) ||
            (currentPath.includes('/reports/') && href.includes('/reports/')) ||
            (currentPath.includes('/admin/') && href.includes('/admin/')) ||  // Add this line
            (currentPath.includes('/certificates/') && href.includes('/certificates/')) ||
            (currentPath === '/' && href.includes('index.php'))) {
            link.classList.add('active');
        }
    });
}

// Theme toggle functionality
function toggleTheme() {
    const body = document.body;
    const themeIcon = document.querySelector('.theme-icon');
    
    body.classList.toggle('dark-theme');
    
    if (body.classList.contains('dark-theme')) {
        themeIcon.textContent = '☀️';
        localStorage.setItem('theme', 'dark');
    } else {
        themeIcon.textContent = '🌙';
        localStorage.setItem('theme', 'light');
    }
}

// Initialize theme from localStorage
function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const themeIcon = document.querySelector('.theme-icon');
    
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        if (themeIcon) themeIcon.textContent = '☀️';
    }
}

// Header scroll effect
function handleHeaderScroll() {
    const header = document.querySelector('.header');
    
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
}

// Initialize header functionality
document.addEventListener('DOMContentLoaded', function() {
    setActiveNav();
    initTheme();
    
    // Add scroll listener
    window.addEventListener('scroll', handleHeaderScroll);
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const nav = document.getElementById('mainNav');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (!nav.contains(e.target) && !toggle.contains(e.target) && nav.classList.contains('nav-open')) {
            toggleMobileMenu();
        }
    });
    
    // Close mobile menu when window is resized to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            const nav = document.getElementById('mainNav');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (nav.classList.contains('nav-open')) {
                nav.classList.remove('nav-open');
                toggle.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }
    });
});
</script>
