<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Migration Generator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            font-family: "Segoe UI", sans-serif;
        }

        .card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .card-header {
            background: linear-gradient(90deg, #2563eb, #4f46e5) !important;
            color: #fff;
            padding: 20px;
        }

        .card-header h3 {
            font-weight: 700;
            margin: 0;
        }

        .card-footer {
            background: #f8f9fa;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #d1d5db;
            height: 45px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary {
            background: #2563eb;
            border: none;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-success {
            border: none;
        }

        .btn-warning {
            border: none;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: #2563eb;
            color: #fff;
        }

        .table th {
            font-weight: 600;
        }

        .table tbody tr:hover {
            background: #eef4ff;
        }

        pre {
            background: #1e293b;
            color: #f8fafc;
            border-radius: 12px;
            padding: 20px;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .alert {
            border: none;
            border-radius: 12px;
        }

        .pagination {
            justify-content: center;
        }

        .page-link {
            border-radius: 8px;
            margin: 0 3px;
        }

        .page-item.active .page-link {
            background: #2563eb;
            border-color: #2563eb;
        }

        .dashboard-card {
            border-radius: 15px;
            color: #fff;
            padding: 20px;
            text-align: center;
            transition: .3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card h2 {
            font-weight: 700;
            margin: 10px 0 0;
        }

        .dashboard-card h6 {
            margin: 0;
            opacity: .9;
        }

        .bg-total {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .bg-today {
            background: linear-gradient(135deg, #16a34a, #15803d);
        }

        .bg-table {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .bg-latest {
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
        }

        .search-box {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .05);
            margin-bottom: 20px;
        }
    </style>

</head>

<body>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-9">

                <div class="card shadow-lg">

                    <div class="card-header bg-primary text-white">

                        <h3 class="mb-0">
                            Laravel 12 Migration Generator
                        </h3>

                    </div>

                    <div class="card-body">

                        {{-- Dashboard Statistics --}}
                        <div class="row mb-4">

                            <div class="col-md-3">
                                <div class="card bg-primary text-white shadow">
                                    <div class="card-body text-center">
                                        <h6>Total Migrations</h6>
                                        <h2>{{ $stats['total'] }}</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-success text-white shadow">
                                    <div class="card-body text-center">
                                        <h6>Today's Migrations</h6>
                                        <h2>{{ $stats['today'] }}</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-warning text-dark shadow">
                                    <div class="card-body text-center">
                                        <h6>Total Tables</h6>
                                        <h2>{{ $stats['tables'] }}</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-dark text-white shadow">
                                    <div class="card-body text-center">
                                        <h6>Latest Migration</h6>
                                        <small>{{ $stats['latest'] ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-body">

                            {{-- Success --}}

                            @if(session('success'))

                            <div class="alert alert-success alert-dismissible fade show">

                                {{ session('success') }}

                                <button class="btn-close" data-bs-dismiss="alert"></button>

                            </div>

                            @endif

                            {{-- Error --}}

                            @if(session('error'))

                            <div class="alert alert-danger alert-dismissible fade show">

                                {{ session('error') }}

                                <button class="btn-close" data-bs-dismiss="alert"></button>

                            </div>

                            @endif

                            {{-- Validation --}}

                            @if($errors->any())

                            <div class="alert alert-danger">

                                <ul class="mb-0">

                                    @foreach($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                    @endforeach

                                </ul>

                            </div>

                            @endif

                            <form action="/generate" method="POST" id="migrationForm">

                                @csrf

                                <div class="mb-3">

                                    <label class="form-label">

                                        Table Name

                                    </label>

                                    <input
                                        type="text"
                                        name="table"
                                        class="form-control"
                                        placeholder="products"
                                        value="{{ old('table') }}"
                                        required>

                                    <div class="form-text">

                                        Only lowercase letters and underscores.

                                    </div>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label fw-bold">

                                        Table Fields

                                    </label>

                                    <input type="hidden" name="fields" id="fields">

                                    <table class="table table-bordered align-middle" id="fieldsTable">

                                        <thead class="table-light">

                                            <tr>

                                                <th width="45%">Field Name</th>

                                                <th width="40%">Data Type</th>

                                                <th width="15%">Action</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <tr>

                                                <td>

                                                    <input
                                                        type="text"
                                                        class="form-control field-name"
                                                        placeholder="name">

                                                </td>

                                                <td>

                                                    <select class="form-select field-type">

                                                        <option value="string">string</option>
                                                        <option value="integer">integer</option>
                                                        <option value="bigInteger">bigInteger</option>
                                                        <option value="decimal">decimal</option>
                                                        <option value="float">float</option>
                                                        <option value="boolean">boolean</option>
                                                        <option value="text">text</option>
                                                        <option value="longText">longText</option>
                                                        <option value="date">date</option>
                                                        <option value="dateTime">dateTime</option>
                                                        <option value="timestamp">timestamp</option>
                                                        <option value="json">json</option>

                                                    </select>

                                                </td>

                                                <td class="text-center">

                                                    <button
                                                        type="button"
                                                        class="btn btn-danger btn-sm removeRow">

                                                        ✖

                                                    </button>

                                                </td>

                                            </tr>

                                        </tbody>

                                    </table>

                                    <button
                                        type="button"
                                        class="btn btn-success"
                                        id="addField">
                                        + Add Field
                                    </button>

                                    <div class="mt-3">

                                        <div class="mb-2">

                                            <span class="badge bg-secondary">string</span>
                                            <span class="badge bg-secondary">integer</span>
                                            <span class="badge bg-secondary">bigInteger</span>
                                            <span class="badge bg-secondary">decimal</span>
                                            <span class="badge bg-secondary">float</span>
                                            <span class="badge bg-secondary">boolean</span>
                                            <span class="badge bg-secondary">text</span>
                                            <span class="badge bg-secondary">longText</span>
                                            <span class="badge bg-secondary">date</span>
                                            <span class="badge bg-secondary">dateTime</span>
                                            <span class="badge bg-secondary">timestamp</span>
                                            <span class="badge bg-secondary">json</span>

                                        </div>

                                        <div class="alert alert-info mb-0">

                                            <strong>How to use:</strong>

                                            <ul class="mb-0 mt-2">
                                                <li>Enter the <strong>Field Name</strong> (e.g. <code>name</code>).</li>
                                                <li>Select the appropriate <strong>Data Type</strong> from the dropdown.</li>
                                                <li>Click <strong>+ Add Field</strong> to add more fields.</li>
                                            </ul>

                                            <hr class="my-2">

                                            <strong>Example:</strong>

                                            <table class="table table-sm table-bordered mt-2 mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Field Name</th>
                                                        <th>Data Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>name</td>
                                                        <td>string</td>
                                                    </tr>
                                                    <tr>
                                                        <td>price</td>
                                                        <td>decimal</td>
                                                    </tr>
                                                    <tr>
                                                        <td>description</td>
                                                        <td>text</td>
                                                    </tr>
                                                    <tr>
                                                        <td>published_at</td>
                                                        <td>date</td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </div>

                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-6 d-grid mb-2">

                                        <button
                                            class="btn btn-warning"
                                            name="preview"
                                            value="1">

                                            Preview Migration

                                        </button>

                                    </div>

                                    <div class="col-md-6 d-grid">

                                        <button
                                            class="btn btn-primary">

                                            Generate Migration

                                        </button>

                                    </div>

                                </div>

                            </form>

                            {{-- Preview --}}

                            @if(session('preview'))

                            <hr>

                            <h4 class="mt-4">

                                Migration Preview

                            </h4>

                            <pre>{{ session('preview') }}</pre>

                            @endif

                            <hr>

                            <div class="mb-3 text-end">
                                <a href="{{ route('migration.trash') }}"
                                    class="btn btn-dark">
                                    🗑 View Trash
                                </a>
                            </div>

                            <h4 class="mb-3">Recent Migration Files</h4>

                            <form method="GET" action="{{ route('home') }}" class="row g-2 mb-3">
                                <div class="col-md-10">
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search migration file..."
                                        value="{{ request('search') }}">
                                </div>

                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100">
                                        Search
                                    </button>
                                </div>
                            </form>

                            @if($migrations->count())

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover align-middle">

                                    <thead class="table-dark">

                                        <tr>
                                            <th>#</th>
                                            <th>Migration File</th>
                                            <th>Created</th>
                                            <th width="220">Actions</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach($migrations as $migration)

                                        <tr>

                                            <td>{{ $loop->iteration + ($migrations->currentPage()-1) * $migrations->perPage() }}</td>

                                            <td>{{ $migration->getFilename() }}</td>

                                            <td>
                                                {{ date('d M Y H:i', $migration->getMTime()) }}
                                            </td>

                                            <td>

                                                <a
                                                    href="{{ route('migration.download', $migration->getFilename()) }}"
                                                    class="btn btn-success btn-sm">

                                                    Download

                                                </a>

                                                <form
                                                    action="{{ route('migration.delete', $migration->getFilename()) }}"
                                                    method="POST"
                                                    class="d-inline">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        onclick="return confirm('Delete this migration file?')"
                                                        class="btn btn-danger btn-sm">

                                                        Delete

                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                            <div class="d-flex justify-content-center">

                                {{ $migrations->links() }}

                            </div>

                            @else

                            <div class="alert alert-info">

                                No migration files found.

                            </div>

                            @endif


                        </div>

                    </div>

                </div>

            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


            <script>
                document.addEventListener("DOMContentLoaded", function() {

                    const tableBody = document.querySelector("#fieldsTable tbody");
                    const addButton = document.getElementById("addField");
                    const form = document.getElementById("migrationForm");
                    const hiddenInput = document.getElementById("fields");

                    function createRow(name = "", type = "string") {

                        const row = document.createElement("tr");

                        row.innerHTML = `
            <td>
                <input
                    type="text"
                    class="form-control field-name"
                    placeholder="Field Name"
                    value="${name}">
            </td>

            <td>
                <select class="form-select field-type">

                    <option value="string">string</option>
                    <option value="integer">integer</option>
                    <option value="bigInteger">bigInteger</option>
                    <option value="decimal">decimal</option>
                    <option value="float">float</option>
                    <option value="boolean">boolean</option>
                    <option value="text">text</option>
                    <option value="longText">longText</option>
                    <option value="date">date</option>
                    <option value="dateTime">dateTime</option>
                    <option value="timestamp">timestamp</option>
                    <option value="json">json</option>

                </select>
            </td>

            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm removeRow">
                    Remove
                </button>
            </td>
        `;

                        row.querySelector(".field-type").value = type;

                        tableBody.appendChild(row);
                    }

                    // Remove default row
                    tableBody.innerHTML = "";

                    // Restore old values after validation
                    const oldFields = @json(old('fields'));

                    if (oldFields) {

                        oldFields.split(",").forEach(field => {

                            const parts = field.split(":");

                            createRow(parts[0], parts[1] ?? "string");

                        });

                    } else {

                        createRow();

                    }

                    // Add new row
                    addButton.addEventListener("click", function() {

                        createRow();

                    });

                    // Remove row
                    tableBody.addEventListener("click", function(e) {

                        if (e.target.classList.contains("removeRow")) {

                            if (tableBody.rows.length > 1) {

                                e.target.closest("tr").remove();

                            } else {

                                alert("At least one field is required.");

                            }

                        }

                    });

                    // Submit form
                    form.addEventListener("submit", function() {

                        let fields = [];

                        document.querySelectorAll("#fieldsTable tbody tr").forEach(function(row) {

                            const name = row.querySelector(".field-name").value.trim();
                            const type = row.querySelector(".field-type").value;

                            if (name !== "") {

                                fields.push(`${name}:${type}`);

                            }

                        });

                        hiddenInput.value = fields.join(",");

                    });

                });
            </script>

</body>

</html>