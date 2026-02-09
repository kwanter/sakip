/**
 * Admin Settings Page Scripts
 * Handles cache clearing, app optimization, database backup, and settings management
 */

$(document).ready(function () {
    // Setup AJAX headers for CSRF
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Cache inputs and feedback elements
    const appNameInput = document.getElementById("app_name");
    const appDescInput = document.getElementById("app_description");
    const descCount = document.getElementById("descCount");
    const descFeedback = document.getElementById("descFeedback");

    // Update description char counter and validity
    function updateDescCount() {
        if (!appDescInput || !descCount || !descFeedback) return;
        const len = appDescInput.value.length;
        descCount.textContent = len;
        if (len > 500) {
            descFeedback.style.display = "inline";
            appDescInput.classList.add("is-invalid");
        } else {
            descFeedback.style.display = "none";
            appDescInput.classList.remove("is-invalid");
        }
    }

    if (appDescInput) {
        appDescInput.addEventListener("input", updateDescCount);
        updateDescCount();
    }

    if (appNameInput) {
        appNameInput.addEventListener("input", function () {
            if (appNameInput.value.trim() === "") {
                appNameInput.classList.add("is-invalid");
            } else {
                appNameInput.classList.remove("is-invalid");
            }
        });
    }
});

/**
 * Clear Application Cache
 */
function clearCache() {
    if (!confirm("Are you sure you want to clear application cache?")) {
        return;
    }

    $.ajax({
        url: adminRoutes.clearCache,
        type: "POST",
        beforeSend: function () {
            $('button[data-onclick="clearCache()"]')
                .prop("disabled", true)
                .html(
                    '<i class="fas fa-spinner fa-spin"></i> Clearing...'
                );
        },
        success: function (response) {
            alert((response.success ? "✅ " : "❌ ") + response.message);
        },
        error: function (xhr) {
            let message = "Error clearing cache";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert("❌ " + message);
        },
        complete: function () {
            $('button[data-onclick="clearCache()"]')
                .prop("disabled", false)
                .html('<i class="fas fa-broom"></i> Clear Cache');
        },
    });
}

/**
 * Optimize Application
 */
function optimizeApp() {
    if (!confirm("Are you sure you want to optimize the application?")) {
        return;
    }

    $.ajax({
        url: adminRoutes.optimize,
        type: "POST",
        beforeSend: function () {
            $('button[data-onclick="optimizeApp()"]')
                .prop("disabled", true)
                .html(
                    '<i class="fas fa-spinner fa-spin"></i> Optimizing...'
                );
        },
        success: function (response) {
            alert((response.success ? "✅ " : "❌ ") + response.message);
        },
        error: function (xhr) {
            let message = "Error optimizing application";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert("❌ " + message);
        },
        complete: function () {
            $('button[data-onclick="optimizeApp()"]')
                .prop("disabled", false)
                .html('<i class="fas fa-rocket"></i> Optimize App');
        },
    });
}

/**
 * Backup Database
 */
function backupDatabase() {
    if (!confirm("Are you sure you want to create a database backup?")) {
        return;
    }

    $.ajax({
        url: adminRoutes.backup,
        type: "POST",
        beforeSend: function () {
            $('button[data-onclick="backupDatabase()"]')
                .prop("disabled", true)
                .html(
                    '<i class="fas fa-spinner fa-spin"></i> Backing up...'
                );
        },
        success: function (response) {
            let message =
                (response.success ? "✅ " : "❌ ") + response.message;
            if (response.file_size) {
                message += "\nSize: " + response.file_size;
            }
            if (response.file_path) {
                message += "\nPath: " + response.file_path;
            }
            alert(message);
        },
        error: function (xhr) {
            let message = "Error creating backup";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert("❌ " + message);
        },
        complete: function () {
            $('button[data-onclick="backupDatabase()"]')
                .prop("disabled", false)
                .html('<i class="fas fa-database"></i> Backup Database');
        },
    });
}

/**
 * Save Application Settings via AJAX
 * Performs client-side validation, posts to server, and updates UI.
 */
window.saveAppSettings = function (e) {
    e.preventDefault();
    const btn = document.getElementById("appSettingsSaveBtn");
    const appNameInput = document.getElementById("app_name");
    const appDescInput = document.getElementById("app_description");
    const name = appNameInput ? appNameInput.value.trim() : "";
    const desc = appDescInput ? appDescInput.value : "";

    // Client-side checks
    let hasError = false;
    if (!name && appNameInput) {
        appNameInput.classList.add("is-invalid");
        hasError = true;
    }
    if (desc.length > 500 && appDescInput) {
        appDescInput.classList.add("is-invalid");
        hasError = true;
    }
    if (hasError) {
        return false;
    }

    $.ajax({
        url: adminRoutes.update,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            settings: {
                "app.name": {
                    key: "app.name",
                    value: name,
                    type: "string",
                    description: "Application name",
                },
                "app.description": {
                    key: "app.description",
                    value: desc,
                    type: "string",
                    description: "Application description",
                },
            },
        },
        beforeSend: function () {
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }
        },
        success: function (res) {
            const newName = name;
            if (newName) {
                document.title = newName + " • Admin Settings";
                const navBrand = document.querySelector(".navbar-brand");
                if (navBrand) {
                    navBrand.textContent = newName;
                }
                document.querySelectorAll("[data-app-name]").forEach(function (el) {
                    el.textContent = newName;
                });
            }
            alert("✅ Application settings saved successfully.");
        },
        error: function (xhr) {
            let message = "Error saving application settings";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert("❌ " + message);
        },
        complete: function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = "Save Application Settings";
            }
        },
    });

    return false;
};
