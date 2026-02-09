/**
 * SAKIP Custom Scripts
 * Handles sidebar toggle, theme management, and UI interactions
 */

(function () {
    "use strict";

    // ============================
    // Sidebar Toggle Functionality
    // ============================

    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");
    const sidebarCollapseBtn = document.getElementById("sidebarCollapseBtn");
    const sidebarToggleBtn = document.getElementById("sidebarToggleBtn");
    const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");
    const sidebarOverlay = document.getElementById("sidebarOverlay");

    // Desktop sidebar collapse
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener("click", function (e) {
            e.preventDefault();

            if (!sidebar || !mainContent) {
                console.error("Sidebar or main content element not found");
                return;
            }

            sidebar.classList.toggle("collapsed");
            mainContent.classList.toggle("expanded");

            // Update icon direction
            const icon = this.querySelector("i");
            const text = this.querySelector(".btn-text");
            if (icon) {
                if (sidebar.classList.contains("collapsed")) {
                    icon.classList.remove("fa-chevron-left");
                    icon.classList.add("fa-chevron-right");
                } else {
                    icon.classList.remove("fa-chevron-right");
                    icon.classList.add("fa-chevron-left");
                }
            }

            // Update button text
            if (text) {
                if (sidebar.classList.contains("collapsed")) {
                    text.textContent = "Kembangkan";
                } else {
                    text.textContent = "Ciutkan";
                }
            }

            // Save state
            localStorage.setItem(
                "sidebarCollapsed",
                sidebar.classList.contains("collapsed"),
            );
        });
    } else {
        console.warn("Sidebar collapse button not found");
    }

    // Mobile sidebar toggle
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener("click", function () {
            sidebar.classList.add("mobile-open");
            sidebarOverlay.classList.add("show");
        });
    }

    // Mobile sidebar close
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", closeMobileSidebar);
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener("click", closeMobileSidebar);
    }

    function closeMobileSidebar() {
        sidebar.classList.remove("mobile-open");
        sidebarOverlay.classList.remove("show");
    }

    // ============================
    // Active State Management
    // ============================

    // Add active state to current menu item
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll(".sidebar .nav-link");

        navLinks.forEach((link) => {
            const href = link.getAttribute("href");
            if (
                href &&
                (currentPath === href || currentPath.startsWith(href + "/"))
            ) {
                link.classList.add("active");
            }
        });
    }

    // ============================
    // Theme Toggle Functionality
    // ============================

    function toggleTheme() {
        const currentTheme =
            document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);

        // Update icon
        const themeIcon = document.getElementById("theme-icon");
        if (newTheme === "dark") {
            themeIcon.className = "fas fa-sun";
        } else {
            themeIcon.className = "fas fa-moon";
        }
    }

    // Load saved theme on page load
    function loadSavedTheme() {
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-theme", savedTheme);

        // Update icon based on current theme
        const themeIcon = document.getElementById("theme-icon");
        if (themeIcon) {
            if (savedTheme === "dark") {
                themeIcon.className = "fas fa-sun";
            } else {
                themeIcon.className = "fas fa-moon";
            }
        }
    }

    // ============================
    // Restore State on Page Load
    // ============================

    function restoreState() {
        // Restore sidebar state
        const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
        const isMobile = window.innerWidth <= 992; // Match CSS media query

        console.log(
            "Restore State - isCollapsed:",
            isCollapsed,
            "isMobile:",
            isMobile,
        );
        console.log("Sidebar element:", sidebar);
        console.log("Main content element:", mainContent);
        console.log("Collapse button:", sidebarCollapseBtn);

        if (isCollapsed && !isMobile && sidebar && mainContent) {
            sidebar.classList.add("collapsed");
            mainContent.classList.add("expanded");

            // Update icon
            const icon = sidebarCollapseBtn?.querySelector("i");
            const text = sidebarCollapseBtn?.querySelector(".btn-text");
            if (icon) {
                icon.classList.remove("fa-chevron-left");
                icon.classList.add("fa-chevron-right");
            }
            if (text) {
                text.textContent = "Kembangkan";
            }
        } else if (!isMobile && sidebar && mainContent) {
            // Ensure sidebar is expanded by default on desktop
            sidebar.classList.remove("collapsed");
            mainContent.classList.remove("expanded");

            const icon = sidebarCollapseBtn?.querySelector("i");
            const text = sidebarCollapseBtn?.querySelector(".btn-text");
            if (icon) {
                icon.classList.remove("fa-chevron-right");
                icon.classList.add("fa-chevron-left");
            }
            if (text) {
                text.textContent = "Ciutkan";
            }
        }

        // Load saved theme
        loadSavedTheme();

        // Set active menu item
        setActiveMenuItem();
    }

    // ============================
    // Event Listeners
    // ============================

    // DOM Content Loaded
    document.addEventListener("DOMContentLoaded", function () {
        restoreState();

        // Handle window resize
        let resizeTimer;
        window.addEventListener("resize", function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                const isMobile = window.innerWidth <= 992;

                if (!isMobile) {
                    // Remove mobile-specific classes
                    if (sidebar) {
                        sidebar.classList.remove("mobile-open");
                    }
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove("show");
                    }

                    // Restore collapsed state if it was saved
                    const isCollapsed =
                        localStorage.getItem("sidebarCollapsed") === "true";
                    if (isCollapsed && sidebar && mainContent) {
                        sidebar.classList.add("collapsed");
                        mainContent.classList.add("expanded");
                    }
                } else {
                    // On mobile, always remove collapsed state
                    if (sidebar) {
                        sidebar.classList.remove("collapsed");
                    }
                    if (mainContent) {
                        mainContent.classList.remove("expanded");
                    }
                }
            }, 250);
        });

        // Add event listener for theme toggle button
        const themeToggleBtn = document.querySelector(".theme-toggle");
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener("click", function (e) {
                e.preventDefault();
                toggleTheme();
            });
        }
    });

    // Auto hide alerts after 5 seconds
    setTimeout(function () {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach(function (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(function () {
                alert.remove();
            }, 500);
        });
    }, 5000);

    // Event delegation for inline onclick handlers (CSP compliance)
    // This executes onclick attributes that were moved to data-onclick attributes
    document.body.addEventListener("click", function (e) {
        const target = e.target.closest("[data-onclick]");
        if (target) {
            e.preventDefault();
            e.stopPropagation();
            const onclickCode = target.getAttribute("data-onclick");
            if (onclickCode) {
                try {
                    // Safe execution of onclick code
                    new Function(onclickCode).call(target);
                } catch (error) {
                    console.error(
                        "Error executing onclick handler:",
                        error,
                        "Code:",
                        onclickCode,
                    );
                }
            }
        }

        // Close dropdowns when clicking outside
        const userDropdown = document.getElementById('userDropdown');
        const navbarDropdown = document.getElementById('navbarDropdownMenu');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const navbarDropdownBtn = document.getElementById('navbarDropdown');

        // Close modern layout dropdown
        if (userDropdown && userMenuBtn && !userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
            userDropdown.classList.remove('show');
        }

        // Close app layout dropdown
        if (navbarDropdown && navbarDropdownBtn && !navbarDropdown.contains(e.target) && !navbarDropdownBtn.contains(e.target)) {
            navbarDropdown.classList.remove('show');
        }
    });

    // Confirm delete actions (requires jQuery)
    if (typeof $ !== "undefined") {
        $(".btn-delete").on("click", function (e) {
            if (!confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                e.preventDefault();
            }
        });
    }

    // Make toggleTheme available globally
    window.toggleTheme = toggleTheme;
})();
