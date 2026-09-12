document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("settingsForm");
    const message = document.getElementById("settingsMessage");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch("api.php", {
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showMessage(data.message || "Settings saved successfully.", "success");
            } else {
                showMessage(data.message || "Failed to save settings.", "error");
            }
        } catch (error) {
            console.error(error);
            showMessage("Server error. Please try again.", "error");
        }
    });

    function showMessage(text, type) {
        if (!message) return;

        message.textContent = text;
        message.className = "settings-message " + type;
        message.style.display = "block";

        setTimeout(() => {
            message.style.display = "none";
        }, 4000);
    }

    const passwordForm = document.getElementById("passwordForm");

    if (passwordForm) {
        passwordForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const formData = new FormData(passwordForm);
            formData.append("action", "change_password");

            try {
                const response = await fetch("api.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(
                        data.message || "Password changed successfully.",
                        "success"
                    );

                    passwordForm.reset();
                } else {
                    showMessage(
                        data.message || "Unable to change password.",
                        "error"
                    );
                }
            } catch (error) {
                console.error(error);
                showMessage("Server error. Please try again.", "error");
            }
        });
    }

    const emergencyToggle = document.getElementById("emergencyToggle");

    if (emergencyToggle) {
        emergencyToggle.addEventListener("change", async () => {
            const formData = new FormData();

            formData.append("action", "update_emergency_status");
            formData.append(
                "status",
                emergencyToggle.checked ? "1" : "0"
            );

            try {
                const response = await fetch("api.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    emergencyToggle.checked = !emergencyToggle.checked;

                    showMessage(
                        data.message || "Unable to update emergency status.",
                        "error"
                    );
                } else {
                    showMessage(
                        data.message || "Emergency status updated.",
                        "success"
                    );
                }
            } catch (error) {
                console.error(error);

                emergencyToggle.checked = !emergencyToggle.checked;

                showMessage(
                    "Server error. Please try again.",
                    "error"
                );
            }
        });
    }

    const testButton = document.getElementById("testNotification");

    if (testButton) {
        testButton.addEventListener("click", async () => {
            const formData = new FormData();

            formData.append("action", "test_notification");

            testButton.disabled = true;
            testButton.textContent = "Sending...";

            try {
                const response = await fetch("api.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                showMessage(
                    data.message ||
                    (data.success
                        ? "Notification sent."
                        : "Notification failed."),
                    data.success ? "success" : "error"
                );
            } catch (error) {
                console.error(error);
                showMessage("Server error. Please try again.", "error");
            }

            testButton.disabled = false;
            testButton.textContent = "Test Notification";
        });
    }
});