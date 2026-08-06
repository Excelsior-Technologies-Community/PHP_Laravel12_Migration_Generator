<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Migration Generator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border: none;
            border-radius: 15px;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        textarea {
            resize: none;
        }

        pre {
            background: #212529;
            color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            overflow: auto;
            font-size: 14px;
        }

        .badge {
            font-size: 13px;
        }

        .list-group-item {
            font-size: 14px;
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

                        <h4>

                            Recent Migration Files

                        </h4>

                        @php

                        $migrations = glob(database_path('migrations/*.php'));

                        rsort($migrations);

                        @endphp

                        @if(count($migrations))

                        <div class="list-group">

                            @foreach(array_slice($migrations,0,8) as $migration)

                            <div class="list-group-item">

                                {{ basename($migration) }}

                            </div>

                            @endforeach

                        </div>

                        @else

                        <div class="alert alert-info mt-3">

                            No migration files found.

                        </div>

                        @endif

                    </div>

                    <div class="card-footer text-center">

                        Laravel 12 Migration Generator • Enhanced Version

                    </div>

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