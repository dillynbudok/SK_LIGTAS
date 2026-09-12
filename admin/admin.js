let records = {};

const labels = {
    contacts: "Emergency Contact",
    hospitals: "Hospital",
    alerts: "Disaster Alert",
    evacuation: "Evacuation Center",
    firstaid: "First Aid Guide"
};

async function loadData(type) {

    const response = await fetch(
        "api.php?action=list&type=" + type
    );

    const data = await response.json();

    records[type] = data;

    renderTable(type, data);
}

function renderTable(type, data) {

    const container =
        document.getElementById(type + "Table");

    if (!container) return;

    if (!data.length) {

        container.innerHTML =
            "<p>No records found.</p>";

        return;
    }

    let html =
        '<div class="table-wrapper"><table>';

    html += "<thead><tr>";

    if (type === "contacts") {
        html +=
            "<th>Name</th><th>Category</th><th>Position</th><th>Phone</th><th>Actions</th>";
    }

    if (type === "hospitals") {
        html +=
            "<th>Name</th><th>Location</th><th>Phone</th><th>Status</th><th>Actions</th>";
    }

    if (type === "alerts") {
        html +=
            "<th>Title</th><th>Type</th><th>Date</th><th>Actions</th>";
    }

    if (type === "evacuation") {
        html +=
            "<th>Name</th><th>Location</th><th>Capacity</th><th>Status</th><th>Actions</th>";
    }

    if (type === "firstaid") {
        html +=
            "<th>Title</th><th>Description</th><th>Actions</th>";
    }

    if (type === "reports") {
        html +=
            "<th>Category</th><th>Name</th><th>Location</th><th>Description</th><th>Status</th><th>Actions</th>";
    }

    html += "</tr></thead><tbody>";

    data.forEach(function(row) {

        html += "<tr>";

        if (type === "contacts") {

            html += `
                <td>${escapeHTML(row.name)}</td>
                <td>${escapeHTML(row.category)}</td>
                <td>${escapeHTML(row.position)}</td>
                <td>${escapeHTML(row.phone)}</td>
                <td>
                    ${actionButtons(type,row.id)}
                </td>
            `;
        }

        if (type === "hospitals") {

            html += `
                <td>${escapeHTML(row.name)}</td>
                <td>${escapeHTML(row.location)}</td>
                <td>${escapeHTML(row.phone)}</td>
                <td>${escapeHTML(row.status)}</td>
                <td>
                    ${actionButtons(type,row.id)}
                </td>
            `;
        }

        if (type === "alerts") {

            html += `
                <td>${escapeHTML(row.title)}</td>
                <td>${escapeHTML(row.type)}</td>
                <td>${escapeHTML(row.alert_date)}</td>
                <td>
                    ${actionButtons(type,row.id)}
                </td>
            `;
        }

        if (type === "evacuation") {

            html += `
                <td>${escapeHTML(row.name)}</td>
                <td>${escapeHTML(row.location)}</td>
                <td>${escapeHTML(row.capacity)}</td>
                <td>${escapeHTML(row.status)}</td>
                <td>
                    ${actionButtons(type,row.id)}
                </td>
            `;
        }

        if (type === "firstaid") {

            html += `
                <td>${escapeHTML(row.title)}</td>
                <td>${escapeHTML(row.description)}</td>
                <td>
                    ${actionButtons(type,row.id)}
                </td>
            `;
        }

        if (type === "reports") {

            html += `
                <td>${escapeHTML(row.category)}</td>
                <td>${escapeHTML(row.name)}</td>
                <td>${escapeHTML(row.location)}</td>
                <td>${escapeHTML(row.description)}</td>
                <td>
                    <select onchange="changeReportStatus(${row.id},this.value)">
                        <option ${row.status === "Pending" ? "selected" : ""}>Pending</option>
                        <option ${row.status === "Reviewed" ? "selected" : ""}>Reviewed</option>
                        <option ${row.status === "Resolved" ? "selected" : ""}>Resolved</option>
                    </select>
                </td>
                <td>
                    <button class="delete-btn"
                    onclick="deleteReport(${row.id})">
                    Delete
                    </button>
                </td>
            `;
        }

        html += "</tr>";

    });

    html += "</tbody></table></div>";

    container.innerHTML = html;
}

function actionButtons(type,id) {

    return `
        <button class="edit-btn"
        onclick="editRecord('${type}',${id})">
        Edit
        </button>

        <button class="delete-btn"
        onclick="deleteRecord('${type}',${id})">
        Delete
        </button>
    `;
}

function openForm(type,id = 0) {

    document
        .getElementById("adminModal")
        .classList.add("show");

    document
        .getElementById("formTitle")
        .textContent =
        id
            ? "Edit " + labels[type]
            : "Add " + labels[type];

    document
        .getElementById("recordId")
        .value = id;

    document
        .getElementById("formType")
        .value = type;

    const record =
        id
            ? records[type].find(r => Number(r.id) === Number(id))
            : {};

    let html = "";

    if (type === "contacts") {

        html = `
            <label>Category</label>

            <select name="category" required>
                <option ${record.category === "Barangay" ? "selected" : ""}>Barangay</option>
                <option ${record.category === "Municipal" ? "selected" : ""}>Municipal</option>
                <option ${record.category === "Provincial" ? "selected" : ""}>Provincial</option>
                <option ${record.category === "National" ? "selected" : ""}>National</option>
            </select>

            <label>Name</label>
            <input name="name" required value="${value(record.name)}">

            <label>Position</label>
            <input name="position" required value="${value(record.position)}">

            <label>Phone</label>
            <input name="phone" required value="${value(record.phone)}">

            <label>Location</label>
            <input name="location" value="${value(record.location)}">
        `;
    }

    if (type === "hospitals") {

        html = `
            <label>Name</label>
            <input name="name" required value="${value(record.name)}">

            <label>Location</label>
            <input name="location" required value="${value(record.location)}">

            <label>Phone</label>
            <input name="phone" required value="${value(record.phone)}">

            <label>Distance</label>
            <input name="distance" value="${value(record.distance)}">

            <label>Status</label>

            <select name="status">
                <option ${record.status === "Open" ? "selected" : ""}>Open</option>
                <option ${record.status === "Closed" ? "selected" : ""}>Closed</option>
            </select>
        `;
    }

    if (type === "alerts") {

        html = `
            <label>Title</label>
            <input name="title" required value="${value(record.title)}">

            <label>Message</label>
            <textarea name="message" required>${value(record.message)}</textarea>

            <label>Alert Type</label>

            <select name="alert_type">

                <option value="danger"
                ${record.type === "danger" ? "selected" : ""}>
                Danger
                </option>

                <option value="warning"
                ${record.type === "warning" ? "selected" : ""}>
                Warning
                </option>

                <option value="info"
                ${record.type === "info" ? "selected" : ""}>
                Information
                </option>

            </select>

            <label>Date</label>
            <input name="alert_date" value="${value(record.alert_date)}">
        `;
    }

    if (type === "evacuation") {

        html = `
            <label>Name</label>
            <input name="name" required value="${value(record.name)}">

            <label>Location</label>
            <input name="location" required value="${value(record.location)}">

            <label>Capacity</label>
            <input type="number" name="capacity" value="${value(record.capacity)}">

            <label>Status</label>

            <select name="status">
                <option ${record.status === "Open" ? "selected" : ""}>Open</option>
                <option ${record.status === "Full" ? "selected" : ""}>Full</option>
                <option ${record.status === "Closed" ? "selected" : ""}>Closed</option>
            </select>
        `;
    }

    if (type === "firstaid") {

        html = `
            <label>Title</label>

            <input
                name="title"
                required
                value="${value(record.title)}">

            <label>Description</label>

            <textarea
                name="description"
                required>${value(record.description)}</textarea>
        `;
    }

    document
        .getElementById("formFields")
        .innerHTML = html;
}

function editRecord(type,id) {
    openForm(type,id);
}

function closeForm() {

    document
        .getElementById("adminModal")
        .classList.remove("show");
}

document
    .getElementById("adminForm")
    .addEventListener(
        "submit",
        async function(e) {

            e.preventDefault();

            const formData =
                new FormData(this);

            formData.append(
                "action",
                "save"
            );

            const response =
                await fetch(
                    "api.php",
                    {
                        method:"POST",
                        body:formData
                    }
                );

            const data =
                await response.json();

            if (data.success) {

                closeForm();

                loadData(
                    formData.get("type")
                );

                setTimeout(
                    function(){
                        location.reload();
                    },
                    300
                );

            } else {

                alert(
                    data.message ||
                    "Unable to save."
                );

            }
        }
    );

async function deleteRecord(type,id) {

    if (!confirm("Delete this record?")) {
        return;
    }

    const formData =
        new FormData();

    formData.append(
        "action",
        "delete"
    );

    formData.append(
        "type",
        type
    );

    formData.append(
        "id",
        id
    );

    await fetch(
        "api.php",
        {
            method:"POST",
            body:formData
        }
    );

    loadData(type);
}

async function changeReportStatus(id,status) {

    const formData =
        new FormData();

    formData.append(
        "action",
        "save"
    );

    formData.append(
        "type",
        "report_status"
    );

    formData.append(
        "id",
        id
    );

    formData.append(
        "status",
        status
    );

    await fetch(
        "api.php",
        {
            method:"POST",
            body:formData
        }
    );
}

async function deleteReport(id) {

    if (!confirm("Delete this report?")) {
        return;
    }

    const formData =
        new FormData();

    formData.append(
        "action",
        "delete"
    );

    formData.append(
        "type",
        "reports"
    );

    formData.append(
        "id",
        id
    );

    await fetch(
        "api.php",
        {
            method:"POST",
            body:formData
        }
    );

    loadData("reports");
}

function value(v) {

    if (v === undefined || v === null) {
        return "";
    }

    return String(v)
        .replaceAll("&","&amp;")
        .replaceAll('"',"&quot;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;");
}

function escapeHTML(v) {

    return value(v);
}

loadData("contacts");
loadData("hospitals");
loadData("alerts");
loadData("evacuation");
loadData("firstaid");
loadData("reports");