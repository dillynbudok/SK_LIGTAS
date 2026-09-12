function callEmergency() {
    const number = "911";

    if (confirm("Call emergency number " + number + "?")) {
        window.location.href = "tel:" + number;
    }
}

function scrollToSection(id) {
    const element = document.getElementById(id);

    if (element) {
        element.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }
}

function openReport() {
    document.getElementById("reportModal").classList.add("show");
}

function closeReport() {
    document.getElementById("reportModal").classList.remove("show");
}

function getLocation() {

    if (!navigator.geolocation) {
        alert("GPS is not supported by this browser.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            const location =
                lat.toFixed(6) + ", " +
                lng.toFixed(6);

            const input =
                document.getElementById("locationInput");

            if (input) {
                input.value = location;
            }

            alert(
                "Location found:\n" +
                location
            );
        },

        function() {
            alert(
                "Unable to get your location. " +
                "Please allow location permission."
            );
        }
    );
}

function filterContacts(category, button) {

    document
        .querySelectorAll(".tabs button")
        .forEach(function(btn) {
            btn.classList.remove("active");
        });

    button.classList.add("active");

    document
        .querySelectorAll(".contact-item")
        .forEach(function(item) {

            if (
                item.dataset.category === category
            ) {
                item.style.display = "flex";
            } else {
                item.style.display = "none";
            }

        });
}

document.addEventListener(
    "DOMContentLoaded",
    function() {

        const firstTab =
            document.querySelector(".tabs button");

        if (firstTab) {
            filterContacts("Barangay", firstTab);
        }

        const form =
            document.getElementById("reportForm");

        if (form) {

            form.addEventListener(
                "submit",
                async function(e) {

                    e.preventDefault();

                    const formData =
                        new FormData(form);

                    try {

                        const response =
                            await fetch(
                                "api/report.php",
                                {
                                    method: "POST",
                                    body: formData
                                }
                            );

                        const data =
                            await response.json();

                        alert(data.message);

                        if (data.success) {
                            form.reset();
                            closeReport();
                        }

                    } catch (error) {

                        alert(
                            "Unable to submit report."
                        );

                    }

                }
            );
        }

        document
            .getElementById("reportModal")
            ?.addEventListener(
                "click",
                function(e) {

                    if (e.target === this) {
                        closeReport();
                    }

                }
            );

    }
);