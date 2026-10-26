<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel 12 Migration Studio & Schema Generator</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Prism.js Syntax Highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #0ea5e9;
            --dark-card: #0f172a;
            --dark-border: #1e293b;
        }

        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
            min-height: 100vh;
        }

        /* Navbar / Header */
        .navbar-brand-badge {
            background: rgba(255, 255, 255, 0.2);
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .card-header-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%);
            color: #fff;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }

        .dashboard-stat-card {
            border-radius: 14px;
            color: #fff;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-3px);
        }

        .bg-stat-total {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .bg-stat-today {
            background: linear-gradient(135deg, #10b981, #047857);
        }

        .bg-stat-tables {
            background: linear-gradient(135deg, #f59e0b, #b45309);
        }

        .bg-stat-latest {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        /* Nav Tabs */
        .nav-pills-custom .nav-link {
            border-radius: 10px;
            font-weight: 600;
            color: #475569;
            padding: 10px 20px;
            margin-right: 8px;
            transition: all 0.2s;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .nav-pills-custom .nav-link.active {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        /* Builder Table */
        .builder-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
        }

        .builder-table tbody td {
            padding: 10px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .builder-table tbody tr:hover {
            background-color: #f8faff;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.92rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 0.9rem;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }

        /* Code & Pre */
        pre[class*="language-"] {
            border-radius: 12px;
            margin: 0;
            padding: 1.25rem;
            max-height: 480px;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        /* Modifier Badge Toggle */
        .mod-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #64748b;
            user-select: none;
            margin: 2px;
            transition: all 0.15s;
        }

        .mod-pill.active {
            background: #e0e7ff;
            color: #3730a3;
            border-color: #818cf8;
        }

        /* ERD Canvas Container */
        .erd-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            min-height: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            position: relative;
        }

        .erd-toolbar {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            padding: 6px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Code Studio Modal */
        .editor-textarea {
            width: 100%;
            height: 420px;
            font-family: "Fira Code", Consolas, "Courier New", monospace;
            font-size: 13.5px;
            line-height: 1.5;
            background: #1e1e2e;
            color: #cdd6f4;
            border: 1px solid #313244;
            border-radius: 10px;
            padding: 15px;
            resize: vertical;
        }

        .editor-textarea:focus {
            outline: none;
            border-color: #89b4fa;
        }

        /* Mode Switcher Pill */
        .mode-btn-group .btn-check:checked+.btn {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }
    </style>
</head>

<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark card-header-gradient mb-4 shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('home') }}">
                <i class="fa-solid fa-database fa-lg"></i>
                <span>Laravel 12 Migration Studio</span>
                <span class="navbar-brand-badge">PRO GENERATOR</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('migration.trash') }}" class="btn btn-sm btn-outline-light">
                    <i class="fa-solid fa-trash me-1"></i> Trash Bin
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">

        <!-- Top Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="dashboard-stat-card bg-stat-total">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">Total Migrations</div>
                        <h2 class="fw-bold mb-0 mt-1">{{ $stats['total'] }}</h2>
                    </div>
                    <i class="fa-solid fa-folder-tree fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat-card bg-stat-today">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">Today's Migrations</div>
                        <h2 class="fw-bold mb-0 mt-1">{{ $stats['today'] }}</h2>
                    </div>
                    <i class="fa-solid fa-calendar-check fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat-card bg-stat-tables">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">Known Tables</div>
                        <h2 class="fw-bold mb-0 mt-1">{{ $stats['tables'] }}</h2>
                    </div>
                    <i class="fa-solid fa-table fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat-card bg-stat-latest">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">Latest Migration</div>
                        <div class="fw-semibold text-truncate mt-1" style="max-width: 200px;" title="{{ $stats['latest'] ?? 'None' }}">
                            {{ $stats['latest'] ?? 'N/A' }}
                        </div>
                    </div>
                    <i class="fa-solid fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Global Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Main Module Navigation Tabs -->
        <ul class="nav nav-pills nav-pills-custom mb-3" id="mainStudioTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-generator-btn" data-bs-toggle="pill" data-bs-target="#tab-generator">
                    <i class="fa-solid fa-wand-magic-sparkles me-1 text-primary"></i> 1. Schema & Evolution Designer
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-erd-btn" data-bs-toggle="pill" data-bs-target="#tab-erd">
                    <i class="fa-solid fa-diagram-project me-1 text-info"></i> 2. Visual Schema ERD Radar
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-files-btn" data-bs-toggle="pill" data-bs-target="#tab-files">
                    <i class="fa-solid fa-code me-1 text-success"></i> 3. Migration Files & Code Studio
                </button>
            </li>
        </ul>

        <div class="tab-content" id="mainStudioTabContent">

            <!-- ========================================== -->
            <!-- TAB 1: SCHEMA BUILDER & EVOLUTION STUDIO   -->
            <!-- ========================================== -->
            <div class="tab-pane fade show active" id="tab-generator" role="tabpanel">

                <div class="card mb-4">
                    <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-layer-group fa-lg"></i>
                            <h5 class="mb-0 fw-bold">Interactive Schema Studio</h5>
                        </div>

                        <!-- Mode Switcher: Create vs Alter -->
                        <div class="btn-group mode-btn-group" role="group">
                            <input type="radio" class="btn-check" name="migration_type_toggle" id="modeCreate" value="create" autocomplete="off" checked>
                            <label class="btn btn-sm btn-outline-light" for="modeCreate">
                                <i class="fa-solid fa-plus-circle me-1"></i> Create Table
                            </label>

                            <input type="radio" class="btn-check" name="migration_type_toggle" id="modeAlter" value="alter" autocomplete="off">
                            <label class="btn btn-sm btn-outline-light" for="modeAlter">
                                <i class="fa-solid fa-wrench me-1"></i> Alter Existing Table
                            </label>
                        </div>
                    </div>

                    <div class="card-body p-4">

                        <form action="{{ route('migration.generate') }}" method="POST" id="schemaStudioForm">
                            @csrf
                            <input type="hidden" name="migration_type" id="migrationTypeInput" value="create">
                            <input type="hidden" name="fields" id="fieldsPayload">

                            <!-- Table Configuration Row -->
                            <div class="row g-3 mb-4 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark d-flex justify-content-between">
                                        <span><i class="fa-solid fa-table me-1 text-primary"></i> Target Table Name</span>
                                        <small class="text-muted" id="tableModeNotice">Generates: <code>create_xxx_table</code></small>
                                    </label>
                                    
                                    <div class="input-group">
                                        <input type="text"
                                            name="table"
                                            id="tableNameInput"
                                            class="form-control"
                                            placeholder="e.g. products, orders, customers"
                                            value="{{ old('table', session('table_name', '')) }}"
                                            list="knownTablesList"
                                            required>
                                        <datalist id="knownTablesList">
                                            @foreach($knownTables as $tbl)
                                            <option value="{{ $tbl }}"></option>
                                            @endforeach
                                        </datalist>
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Presets
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><h6 class="dropdown-header">Quick Starter Presets</h6></li>
                                            <li><a class="dropdown-item preset-load" href="#" data-preset="ecommerce"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i> E-Commerce Products</a></li>
                                            <li><a class="dropdown-item preset-load" href="#" data-preset="blog"><i class="fa-solid fa-newspaper me-2 text-info"></i> Blog Post & Author</a></li>
                                            <li><a class="dropdown-item preset-load" href="#" data-preset="customer"><i class="fa-solid fa-user-tie me-2 text-success"></i> Customer / Account</a></li>
                                            <li><a class="dropdown-item preset-load" href="#" data-preset="audit"><i class="fa-solid fa-clipboard-check me-2 text-warning"></i> Audit Activity Log</a></li>
                                        </ul>
                                    </div>
                                    <div class="form-text small text-muted">Use snake_case and plural form (e.g., <code>order_items</code>).</div>
                                </div>

                                <div class="col-md-7 d-flex justify-content-md-end gap-2">
                                    <!-- Dual-Mode Switcher: Visual GUI vs Shorthand Text -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary active" id="btnModeGui">
                                            <i class="fa-solid fa-table-cells me-1"></i> Visual GUI Builder
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="btnModeRaw">
                                            <i class="fa-solid fa-terminal me-1"></i> Shorthand Text
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Raw Shorthand Text Box (Hidden by default, two-way synced) -->
                            <div class="card bg-light border p-3 mb-4 d-none" id="rawShorthandContainer">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-dark">
                                        <i class="fa-solid fa-bolt me-1 text-warning"></i> Quick Shorthand Syntax
                                    </label>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnSyncRawToGui">
                                        <i class="fa-solid fa-rotate me-1"></i> Sync into GUI Table
                                    </button>
                                </div>
                                <textarea class="form-control font-monospace" id="rawShorthandText" rows="3" placeholder="name:string, price:decimal(10,2):default(0), user_id:foreign(users):cascade, status:enum(active,pending):default(active)"></textarea>
                                <div class="form-text small text-muted mt-1">
                                    Syntax: <code>field:type:nullable:unique:default(val):foreign(table):cascade</code>. Click "Sync into GUI Table" to update the builder.
                                </div>
                            </div>

                            <!-- Visual GUI Column Studio -->
                            <div class="card border mb-3" id="guiBuilderContainer">
                                <div class="table-responsive">
                                    <table class="table builder-table align-middle mb-0" id="columnsStudioTable">
                                        <thead>
                                            <tr>
                                                <th width="3%" class="text-center">#</th>
                                                <th width="15%" class="alter-col-action d-none">Operation</th>
                                                <th width="22%">Column Name</th>
                                                <th width="22%">Data Type</th>
                                                <th width="28%">Modifiers & Foreign Key</th>
                                                <th width="10%" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="columnsStudioTbody">
                                            <!-- Rows rendered by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="card-footer bg-white p-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-success btn-sm" id="btnAddColumn">
                                            <i class="fa-solid fa-plus me-1"></i> Add Column
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnQuickFk">
                                            <i class="fa-solid fa-link me-1 text-primary"></i> + Foreign Key ID
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnQuickEnum">
                                            <i class="fa-solid fa-list-check me-1 text-info"></i> + Status Enum
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnQuickSoftDeletes">
                                            <i class="fa-solid fa-trash-can me-1 text-danger"></i> + SoftDeletes
                                        </button>
                                    </div>

                                    <div class="small text-muted">
                                        <i class="fa-solid fa-circle-info me-1 text-primary"></i> <code>id()</code> and <code>timestamps()</code> are included automatically in create mode.
                                    </div>
                                </div>
                            </div>

                            <!-- Live Actions & Preview Buttons -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-2">
                                <button type="button" class="btn btn-outline-dark" id="btnResetBuilder">
                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset Columns
                                </button>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="preview" value="1" class="btn btn-warning text-dark" id="btnPreviewSubmit">
                                        <i class="fa-solid fa-eye me-1"></i> Preview Code & SQL DDL
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="btnGenerateSubmit">
                                        <i class="fa-solid fa-circle-check me-1"></i> Generate Migration File
                                    </button>
                                </div>
                            </div>

                        </form>

                        <!-- Generated Preview Display (When preview session exists) -->
                        @if(session('preview_code'))
                        <div class="card mt-4 border-primary shadow-sm" id="previewContainer">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-code fa-lg"></i>
                                    <span class="fw-bold">Migration Preview & Live SQL DDL Pretend</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('previewCodeBlock')">
                                        <i class="fa-solid fa-copy me-1"></i> Copy PHP
                                    </button>
                                    <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('previewSqlBlock')">
                                        <i class="fa-solid fa-database me-1"></i> Copy SQL
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs bg-light px-3 pt-2" id="previewTabs" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active fw-bold" id="tab-php-preview-btn" data-bs-toggle="tab" data-bs-target="#tab-php-preview">
                                            <i class="fa-brands fa-php text-primary me-1"></i> Laravel 12 Migration Class
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link fw-bold" id="tab-sql-preview-btn" data-bs-toggle="tab" data-bs-target="#tab-sql-preview">
                                            <i class="fa-solid fa-terminal text-success me-1"></i> Live SQL DDL (Pretend Mode)
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade show active" id="tab-php-preview">
                                        <pre class="line-numbers"><code class="language-php" id="previewCodeBlock">{{ session('preview_code') }}</code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="tab-sql-preview">
                                        <pre class="line-numbers"><code class="language-sql" id="previewSqlBlock">{{ session('preview_sql') }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 2: VISUAL SCHEMA ERD RADAR             -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-erd" role="tabpanel">

                <div class="card">
                    <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-diagram-project fa-lg"></i>
                            <div>
                                <h5 class="mb-0 fw-bold">Visual Schema Radar & ERD</h5>
                                <small class="text-white-50">Auto-generated Entity-Relationship Model from all Laravel migrations</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('migration.erd_export', 'mermaid') }}" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-download me-1"></i> Export Markdown
                            </a>
                            <a href="{{ route('migration.erd_export', 'json') }}" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-file-code me-1"></i> Export JSON
                            </a>
                            <button class="btn btn-sm btn-light" id="btnCopyMermaid">
                                <i class="fa-solid fa-copy me-1"></i> Copy Mermaid
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-primary px-3 py-2 fs-6">
                                    <i class="fa-solid fa-table me-1"></i> {{ $erdData['total_tables'] }} Tables
                                </span>
                                <span class="badge bg-success px-3 py-2 fs-6">
                                    <i class="fa-solid fa-link me-1"></i> {{ $erdData['total_relations'] }} Relationships
                                </span>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnZoomIn">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnZoomOut">
                                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnZoomReset">
                                    <i class="fa-solid fa-compress me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- Mermaid Render Canvas -->
                        <div class="erd-container p-4" id="erdCanvasContainer">
                            <div class="text-center w-100" id="mermaidContainer">
                                <div class="text-muted py-5">
                                    <i class="fa-solid fa-diagram-project fa-2x mb-2 text-primary opacity-50"></i>
                                    <p class="mb-0 fw-semibold">Click tab to render interactive Schema Radar diagram</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 3: MIGRATION FILES & CODE STUDIO       -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-files" role="tabpanel">

                <div class="card">
                    <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-folder-open fa-lg"></i>
                            <h5 class="mb-0 fw-bold">Active Migration Files</h5>
                        </div>
                        <span class="badge bg-light text-dark">{{ $migrations->total() }} Files</span>
                    </div>

                    <div class="card-body p-4">

                        <!-- Search Box -->
                        <form method="GET" action="{{ route('home') }}" class="row g-2 mb-4">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search migration file by name or table..."
                                        value="{{ request('search') }}">
                                </div>
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
                                        <th width="4%">#</th>
                                        <th>Migration File</th>
                                        <th width="18%">Modified At</th>
                                        <th width="240" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($migrations as $migration)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">
                                            {{ $loop->iteration + ($migrations->currentPage() - 1) * $migrations->perPage() }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-brands fa-php text-primary fa-lg"></i>
                                                <span class="font-monospace fw-semibold">{{ $migration->getFilename() }}</span>
                                            </div>
                                        </td>
                                        <td class="text-muted small">
                                            <i class="fa-regular fa-clock me-1"></i> {{ date('d M Y, H:i', $migration->getMTime()) }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-sm btn-primary me-1 open-code-studio"
                                                data-filename="{{ $migration->getFilename() }}">
                                                <i class="fa-solid fa-code me-1"></i> Code Studio
                                            </button>

                                            <a href="{{ route('migration.download', $migration->getFilename()) }}"
                                                class="btn btn-sm btn-success me-1"
                                                title="Download PHP File">
                                                <i class="fa-solid fa-download"></i>
                                            </a>

                                            <form action="{{ route('migration.delete', $migration->getFilename()) }}"
                                                method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Move this migration file to trash?')"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Move to Trash">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $migrations->links('pagination::bootstrap-5') }}
                        </div>

                        @else
                        <div class="alert alert-info text-center py-4">
                            <i class="fa-solid fa-inbox fa-2x mb-2 text-muted"></i>
                            <p class="mb-0 fw-semibold">No migration files found matching your search.</p>
                        </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- IN-BROWSER CODE STUDIO MODAL (Feature 3)                       -->
    <!-- ============================================================== -->
    <div class="modal fade" id="codeStudioModal" tabindex="-1" aria-labelledby="codeStudioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-terminal text-info fa-lg"></i>
                        <h5 class="modal-title fw-bold mb-0" id="codeStudioModalLabel">Migration Code Studio</h5>
                        <span class="badge bg-secondary font-monospace" id="codeStudioFilename">filename.php</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-light" id="btnCopyStudioCode">
                            <i class="fa-solid fa-copy me-1"></i> Copy
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="btnDownloadStudioFile">
                            <i class="fa-solid fa-download me-1"></i> Download
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body p-0 bg-dark text-white">
                    <ul class="nav nav-tabs border-secondary px-3 pt-2 bg-dark" id="studioTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active text-light fw-semibold" id="tab-editor-btn" data-bs-toggle="tab" data-bs-target="#tab-editor">
                                <i class="fa-brands fa-php text-primary me-1"></i> PHP Migration Code
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-light fw-semibold" id="tab-sql-info-btn" data-bs-toggle="tab" data-bs-target="#tab-sql-info">
                                <i class="fa-solid fa-database text-success me-1"></i> SQL Blueprint
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <div class="tab-pane fade show active" id="tab-editor">
                            <div class="d-flex justify-content-between align-items-center mb-2 px-1 text-muted small">
                                <span><i class="fa-solid fa-pen-to-square me-1"></i> Edit PHP code directly and click 'Save Changes' to update the file on disk.</span>
                                <span id="studioLastModified">Last modified: -</span>
                            </div>
                            <textarea class="editor-textarea" id="studioEditorTextarea" spellcheck="false"></textarea>
                        </div>
                        <div class="tab-pane fade" id="tab-sql-info">
                            <pre class="line-numbers"><code class="language-sql" id="studioSqlBlock">-- SQL Preview</code></pre>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-dark border-secondary d-flex justify-content-between">
                    <div id="studioSaveStatus" class="small text-muted">Ready</div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btnSaveStudioChanges">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>

    <!-- Mermaid.js for ERD -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>

    <script>
        // Column types categorized for dropdowns
        const COLUMN_TYPES_DATA = @json($columnTypes);
        const KNOWN_TABLES_DATA = @json($knownTables);
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Initialize Mermaid with startOnLoad disabled to prevent rendering on hidden tabs (NaN coordinate bug fix)
        mermaid.initialize({
            startOnLoad: false,
            theme: 'default',
            securityLevel: 'loose',
            er: {
                useMaxWidth: true,
                entityPadding: 15
            }
        });

        document.addEventListener("DOMContentLoaded", function () {

            let currentMigrationType = 'create'; // 'create' or 'alter'
            const tbody = document.getElementById("columnsStudioTbody");
            const form = document.getElementById("schemaStudioForm");
            const hiddenPayload = document.getElementById("fieldsPayload");
            const tableNameInput = document.getElementById("tableNameInput");
            const rawShorthandText = document.getElementById("rawShorthandText");
            const rawContainer = document.getElementById("rawShorthandContainer");
            const guiContainer = document.getElementById("guiBuilderContainer");
            const tableModeNotice = document.getElementById("tableModeNotice");

            // Starter schema presets
            const STARTER_PRESETS = {
                ecommerce: [
                    { name: 'name', type: 'string', length: '255', nullable: false, unique: false, index: true },
                    { name: 'slug', type: 'string', length: '255', nullable: false, unique: true, index: true },
                    { name: 'sku', type: 'string', length: '100', nullable: false, unique: true },
                    { name: 'price', type: 'decimal', precision: 10, scale: 2, default: '0.00' },
                    { name: 'stock_quantity', type: 'integer', default: '0', unsigned: true },
                    { name: 'description', type: 'text', nullable: true },
                    { name: 'status', type: 'enum', enum_values: 'draft,active,archived', default: 'active' },
                    { name: 'category_id', type: 'foreignId', foreign_table: 'categories', on_delete: 'cascade', nullable: true }
                ],
                blog: [
                    { name: 'title', type: 'string', length: '255', nullable: false },
                    { name: 'slug', type: 'string', length: '255', unique: true },
                    { name: 'body', type: 'longText', nullable: false },
                    { name: 'user_id', type: 'foreignId', foreign_table: 'users', on_delete: 'cascade' },
                    { name: 'is_published', type: 'boolean', default: 'false', index: true },
                    { name: 'published_at', type: 'timestamp', nullable: true }
                ],
                customer: [
                    { name: 'first_name', type: 'string', length: '100', nullable: false },
                    { name: 'last_name', type: 'string', length: '100', nullable: false },
                    { name: 'email', type: 'string', unique: true, index: true },
                    { name: 'phone', type: 'string', length: '20', nullable: true },
                    { name: 'billing_address', type: 'text', nullable: true },
                    { name: 'credit_limit', type: 'decimal', precision: 12, scale: 2, default: '1000.00' }
                ],
                audit: [
                    { name: 'user_id', type: 'foreignId', foreign_table: 'users', on_delete: 'cascade', nullable: true },
                    { name: 'event', type: 'string', length: '100', index: true },
                    { name: 'auditable_type', type: 'string', length: '200', index: true },
                    { name: 'auditable_id', type: 'bigInteger', unsigned: true, index: true },
                    { name: 'old_values', type: 'json', nullable: true },
                    { name: 'new_values', type: 'json', nullable: true },
                    { name: 'ip_address', type: 'ipAddress', nullable: true }
                ]
            };

            // Switch between Create Table and Alter Table modes
            document.querySelectorAll('input[name="migration_type_toggle"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    currentMigrationType = this.value;
                    document.getElementById('migrationTypeInput').value = currentMigrationType;

                    if (currentMigrationType === 'alter') {
                        tableModeNotice.innerHTML = `Generates: <code>alter_xxx_table</code> (Schema::table)`;
                        document.querySelectorAll('.alter-col-action').forEach(el => el.classList.remove('d-none'));
                        document.querySelectorAll('.alter-action-cell').forEach(el => el.classList.remove('d-none'));
                    } else {
                        tableModeNotice.innerHTML = `Generates: <code>create_xxx_table</code> (Schema::create)`;
                        document.querySelectorAll('.alter-col-action').forEach(el => el.classList.add('d-none'));
                        document.querySelectorAll('.alter-action-cell').forEach(el => el.classList.add('d-none'));
                    }
                    syncGuiToRaw();
                });
            });

            // Dual Mode Toggle: GUI vs Raw Shorthand
            document.getElementById('btnModeGui').addEventListener('click', function () {
                this.classList.add('active', 'btn-outline-primary');
                this.classList.remove('btn-outline-secondary');
                document.getElementById('btnModeRaw').classList.remove('active', 'btn-outline-primary');
                document.getElementById('btnModeRaw').classList.add('btn-outline-secondary');
                guiContainer.classList.remove('d-none');
                rawContainer.classList.add('d-none');
            });

            document.getElementById('btnModeRaw').addEventListener('click', function () {
                this.classList.add('active', 'btn-outline-primary');
                this.classList.remove('btn-outline-secondary');
                document.getElementById('btnModeGui').classList.remove('active', 'btn-outline-primary');
                document.getElementById('btnModeGui').classList.add('btn-outline-secondary');
                rawContainer.classList.remove('d-none');
                syncGuiToRaw();
            });

            // Sync Raw to GUI
            document.getElementById('btnSyncRawToGui').addEventListener('click', function () {
                const text = rawShorthandText.value.trim();
                if (!text) return;

                fetch("{{ route('migration.preview_sql') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        table: tableNameInput.value || 'temp',
                        fields: text,
                        migration_type: currentMigrationType
                    })
                })
                .then(res => res.json())
                .then(data => {
                    // Parse shorthand text manually into GUI
                    tbody.innerHTML = "";
                    const parts = text.split(",");
                    parts.forEach(part => {
                        const trimmed = part.trim();
                        if (!trimmed) return;
                        const colTokens = trimmed.split(":");
                        const name = colTokens[0] ? colTokens[0].trim() : '';
                        const type = colTokens[1] ? colTokens[1].trim() : 'string';
                        const isNullable = trimmed.includes(':nullable');
                        const isUnique = trimmed.includes(':unique');
                        const isIndex = trimmed.includes(':index');
                        const isUnsigned = trimmed.includes(':unsigned');

                        let defVal = '';
                        const defMatch = trimmed.match(/:default\((.*?)\)/);
                        if (defMatch) defVal = defMatch[1];

                        let foreignTable = '';
                        const fMatch = trimmed.match(/:foreign\((.*?)\)/);
                        if (fMatch) foreignTable = fMatch[1];

                        createRow({
                            name: name,
                            type: type.split('(')[0],
                            nullable: isNullable,
                            unique: isUnique,
                            index: isIndex,
                            unsigned: isUnsigned,
                            default: defVal,
                            foreign_table: foreignTable
                        });
                    });
                    document.getElementById('btnModeGui').click();
                })
                .catch(err => alert("Error parsing shorthand: " + err));
            });

            // Create Builder Row
            function createRow(data = {}) {
                const index = tbody.rows.length + 1;
                const tr = document.createElement("tr");

                const name = data.name || "";
                const type = data.type || "string";
                const action = data.action || "add";
                const isNullable = !!data.nullable;
                const isUnique = !!data.unique;
                const isIndex = !!data.index;
                const isUnsigned = !!data.unsigned;
                const defaultVal = data.default || "";
                const foreignTable = data.foreign_table || "";
                const enumValues = data.enum_values || "";
                const lengthVal = data.length || "";

                // Build Type Options Grouped
                let optionsHtml = "";
                for (const groupName in COLUMN_TYPES_DATA) {
                    optionsHtml += `<optgroup label="${groupName}">`;
                    for (const typeKey in COLUMN_TYPES_DATA[groupName]) {
                        const selected = (typeKey === type) ? "selected" : "";
                        optionsHtml += `<option value="${typeKey}" ${selected}>${COLUMN_TYPES_DATA[groupName][typeKey]}</option>`;
                    }
                    optionsHtml += `</optgroup>`;
                }

                // Alter Action selector
                const alterClass = (currentMigrationType === 'alter') ? '' : 'd-none';

                tr.innerHTML = `
                    <td class="text-center text-muted fw-bold row-index">${index}</td>
                    
                    <td class="alter-action-cell ${alterClass}">
                        <select class="form-select form-select-sm col-action">
                            <option value="add" ${action === 'add' ? 'selected' : ''}>+ Add</option>
                            <option value="modify" ${action === 'modify' ? 'selected' : ''}>⚡ Change</option>
                            <option value="rename" ${action === 'rename' ? 'selected' : ''}>✎ Rename</option>
                            <option value="drop" ${action === 'drop' ? 'selected' : ''}>✖ Drop</option>
                        </select>
                    </td>

                    <td>
                        <input type="text" class="form-control form-control-sm col-name" placeholder="field_name" value="${name}">
                        <input type="text" class="form-control form-control-sm col-rename-to mt-1 d-none" placeholder="new_field_name" value="${data.rename_to || ''}">
                    </td>

                    <td>
                        <select class="form-select form-select-sm col-type">
                            ${optionsHtml}
                        </select>
                        <div class="col-type-extra mt-1">
                            ${type === 'enum' ? `<input type="text" class="form-control form-control-sm col-enum-values" placeholder="val1,val2,val3" value="${enumValues}">` : ''}
                            ${type === 'foreignId' ? `<input type="text" class="form-control form-control-sm col-foreign-table" placeholder="target_table (e.g. users)" value="${foreignTable}">` : ''}
                        </div>
                    </td>

                    <td>
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <span class="mod-pill mod-nullable ${isNullable ? 'active' : ''}">? Nullable</span>
                            <span class="mod-pill mod-unique ${isUnique ? 'active' : ''}">★ Unique</span>
                            <span class="mod-pill mod-index ${isIndex ? 'active' : ''}">⚡ Index</span>
                            <span class="mod-pill mod-unsigned ${isUnsigned ? 'active' : ''}">+ Unsigned</span>
                            
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 ms-1 btn-toggle-extra text-primary" title="More settings">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                        </div>

                        <!-- Extra modifier drawer -->
                        <div class="extra-modifiers mt-2 p-2 bg-light rounded border d-none">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small mb-1">Default Value</label>
                                    <input type="text" class="form-control form-control-sm col-default" placeholder="e.g. 0, active, null" value="${defaultVal}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small mb-1">Length / Precision</label>
                                    <input type="text" class="form-control form-control-sm col-length" placeholder="e.g. 255 or 10,2" value="${lengthVal}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1">Comment</label>
                                    <input type="text" class="form-control form-control-sm col-comment" placeholder="Database comment..." value="${data.comment || ''}">
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm duplicate-row me-1" title="Duplicate Row">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Delete Row">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </td>
                `;

                // Wire up pill toggles
                tr.querySelectorAll('.mod-pill').forEach(pill => {
                    pill.addEventListener('click', function () {
                        this.classList.toggle('active');
                        syncGuiToRaw();
                    });
                });

                // Wire up extra modifiers toggle
                tr.querySelector('.btn-toggle-extra').addEventListener('click', function () {
                    tr.querySelector('.extra-modifiers').classList.toggle('d-none');
                });

                // Wire up type change handler
                const typeSelect = tr.querySelector('.col-type');
                const typeExtra = tr.querySelector('.col-type-extra');
                typeSelect.addEventListener('change', function () {
                    const selectedType = this.value;
                    if (selectedType === 'enum') {
                        typeExtra.innerHTML = `<input type="text" class="form-control form-control-sm col-enum-values" placeholder="pending,completed,failed" value="${enumValues}">`;
                    } else if (selectedType === 'foreignId') {
                        const colName = tr.querySelector('.col-name').value;
                        const autoTable = colName.endsWith('_id') ? colName.replace('_id', '') + 's' : 'users';
                        typeExtra.innerHTML = `<input type="text" class="form-control form-control-sm col-foreign-table" placeholder="target_table (e.g. ${autoTable})" value="${foreignTable || autoTable}">`;
                    } else {
                        typeExtra.innerHTML = '';
                    }
                    syncGuiToRaw();
                });

                // Wire up action change for alter mode
                const actionSelect = tr.querySelector('.col-action');
                actionSelect.addEventListener('change', function () {
                    const renameInput = tr.querySelector('.col-rename-to');
                    if (this.value === 'rename') {
                        renameInput.classList.remove('d-none');
                    } else {
                        renameInput.classList.add('d-none');
                    }
                    syncGuiToRaw();
                });

                // Auto-suggest table name on foreignId column typing
                const colNameInput = tr.querySelector('.col-name');
                colNameInput.addEventListener('input', function () {
                    if (typeSelect.value === 'foreignId' || this.value.endsWith('_id')) {
                        if (typeSelect.value !== 'foreignId') {
                            typeSelect.value = 'foreignId';
                        }
                        const suggested = this.value.replace('_id', '') + 's';
                        typeExtra.innerHTML = `<input type="text" class="form-control form-control-sm col-foreign-table" placeholder="target_table" value="${suggested}">`;
                    }
                    syncGuiToRaw();
                });

                tbody.appendChild(tr);
                updateRowIndexes();
                return tr;
            }

            function updateRowIndexes() {
                tbody.querySelectorAll('tr').forEach((row, i) => {
                    const indexEl = row.querySelector('.row-index');
                    if (indexEl) indexEl.innerText = i + 1;
                });
            }

            // Collect all fields from GUI into structured array
            function getFieldsFromGui() {
                const fields = [];
                tbody.querySelectorAll('tr').forEach(tr => {
                    const name = tr.querySelector('.col-name')?.value.trim() || '';
                    const type = tr.querySelector('.col-type')?.value || 'string';
                    const action = tr.querySelector('.col-action')?.value || 'add';
                    const renameTo = tr.querySelector('.col-rename-to')?.value.trim() || '';
                    const nullable = tr.querySelector('.mod-nullable')?.classList.contains('active') || false;
                    const unique = tr.querySelector('.mod-unique')?.classList.contains('active') || false;
                    const index = tr.querySelector('.mod-index')?.classList.contains('active') || false;
                    const unsigned = tr.querySelector('.mod-unsigned')?.classList.contains('active') || false;
                    const defaultVal = tr.querySelector('.col-default')?.value || '';
                    const lengthVal = tr.querySelector('.col-length')?.value || '';
                    const commentVal = tr.querySelector('.col-comment')?.value || '';
                    const foreignTable = tr.querySelector('.col-foreign-table')?.value.trim() || '';
                    const enumValues = tr.querySelector('.col-enum-values')?.value.trim() || '';

                    if (name || ['timestamps', 'softDeletes', 'rememberToken'].includes(type)) {
                        fields.push({
                            name: name,
                            type: type,
                            action: action,
                            rename_to: renameTo,
                            nullable: nullable,
                            unique: unique,
                            index: index,
                            unsigned: unsigned,
                            default: defaultVal,
                            length: lengthVal,
                            comment: commentVal,
                            foreign_table: foreignTable,
                            enum_values: enumValues
                        });
                    }
                });
                return fields;
            }

            // Sync GUI state to raw shorthand textarea & hidden form input
            function syncGuiToRaw() {
                const fields = getFieldsFromGui();
                hiddenPayload.value = JSON.stringify(fields);

                const shorthandTokens = fields.map(f => {
                    let s = `${f.name}:${f.type}`;
                    if (f.foreign_table) s += `:foreign(${f.foreign_table}):cascade`;
                    if (f.nullable) s += `:nullable`;
                    if (f.unique) s += `:unique`;
                    if (f.index) s += `:index`;
                    if (f.unsigned) s += `:unsigned`;
                    if (f.default) s += `:default(${f.default})`;
                    return s;
                });
                rawShorthandText.value = shorthandTokens.join(", ");
            }

            // Event Listeners for Row Actions
            tbody.addEventListener('click', function (e) {
                const removeBtn = e.target.closest('.remove-row');
                if (removeBtn) {
                    if (tbody.rows.length > 1) {
                        removeBtn.closest('tr').remove();
                        updateRowIndexes();
                        syncGuiToRaw();
                    } else {
                        alert("At least one column is required.");
                    }
                    return;
                }

                const dupBtn = e.target.closest('.duplicate-row');
                if (dupBtn) {
                    const row = dupBtn.closest('tr');
                    const name = row.querySelector('.col-name').value;
                    const type = row.querySelector('.col-type').value;
                    createRow({
                        name: name + '_copy',
                        type: type,
                        nullable: row.querySelector('.mod-nullable').classList.contains('active'),
                        unique: false,
                        index: row.querySelector('.mod-index').classList.contains('active')
                    });
                    syncGuiToRaw();
                }
            });

            // Add Column Button
            document.getElementById('btnAddColumn').addEventListener('click', function () {
                createRow();
                syncGuiToRaw();
            });

            // Quick Shortcut Buttons
            document.getElementById('btnQuickFk').addEventListener('click', function () {
                createRow({ name: 'user_id', type: 'foreignId', foreign_table: 'users', index: true });
                syncGuiToRaw();
            });

            document.getElementById('btnQuickEnum').addEventListener('click', function () {
                createRow({ name: 'status', type: 'enum', enum_values: 'pending,active,inactive', default: 'pending', index: true });
                syncGuiToRaw();
            });

            document.getElementById('btnQuickSoftDeletes').addEventListener('click', function () {
                createRow({ name: 'deleted_at', type: 'softDeletes' });
                syncGuiToRaw();
            });

            // Reset Builder
            document.getElementById('btnResetBuilder').addEventListener('click', function () {
                if (confirm('Reset all columns in builder?')) {
                    tbody.innerHTML = '';
                    createRow({ name: 'title', type: 'string' });
                    createRow({ name: 'description', type: 'text', nullable: true });
                    syncGuiToRaw();
                }
            });

            // Starter Preset Loaders
            document.querySelectorAll('.preset-load').forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const presetKey = this.dataset.preset;
                    if (STARTER_PRESETS[presetKey]) {
                        tbody.innerHTML = '';
                        if (presetKey === 'ecommerce') tableNameInput.value = 'products';
                        else if (presetKey === 'blog') tableNameInput.value = 'posts';
                        else if (presetKey === 'customer') tableNameInput.value = 'customers';
                        else if (presetKey === 'audit') tableNameInput.value = 'audit_logs';

                        STARTER_PRESETS[presetKey].forEach(col => createRow(col));
                        syncGuiToRaw();
                    }
                });
            });

            // Form Submit Sync
            form.addEventListener('submit', function (e) {
                syncGuiToRaw();
                if (!tableNameInput.value.trim()) {
                    e.preventDefault();
                    alert("Please enter a Table Name.");
                    tableNameInput.focus();
                    return;
                }
            });

            // Populate initial row
            createRow({ name: 'title', type: 'string' });
            createRow({ name: 'description', type: 'text', nullable: true });
            syncGuiToRaw();

            // ==========================================
            // ERD RENDERING & ZOOM CONTROLS (Feature 4)
            // ==========================================
            let erdZoom = 1;
            let erdRendered = false;
            const mermaidContainer = document.getElementById('mermaidContainer');

            async function renderMermaidErd() {
                if (!mermaidContainer) return;
                const mermaidCode = @json($erdData['mermaid']);
                try {
                    mermaidContainer.innerHTML = `<div class="text-center py-5 text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i><p class="mb-0 fw-semibold">Rendering Schema Radar...</p></div>`;
                    const { svg } = await mermaid.render('erdSvgChart', mermaidCode);
                    mermaidContainer.innerHTML = `<div id="mermaidDiagramElement" style="transition: transform 0.2s ease;">${svg}</div>`;
                } catch (err) {
                    console.error("Mermaid ERD error:", err);
                    mermaidContainer.innerHTML = `<div class="alert alert-warning m-4 text-start"><i class="fa-solid fa-triangle-exclamation me-2"></i>Could not render visual diagram for current migrations: ${err.message || err}. You can still export Markdown or JSON schema.</div>`;
                }
            }

            // Lazy render when tab becomes active and visible
            document.getElementById('tab-erd-btn')?.addEventListener('shown.bs.tab', function () {
                if (!erdRendered) {
                    renderMermaidErd();
                    erdRendered = true;
                }
            });

            document.getElementById('btnZoomIn')?.addEventListener('click', function () {
                const el = document.getElementById('mermaidDiagramElement');
                if (!el) return;
                erdZoom += 0.15;
                el.style.transform = `scale(${erdZoom})`;
                el.style.transformOrigin = 'center top';
            });

            document.getElementById('btnZoomOut')?.addEventListener('click', function () {
                const el = document.getElementById('mermaidDiagramElement');
                if (!el) return;
                if (erdZoom > 0.4) {
                    erdZoom -= 0.15;
                    el.style.transform = `scale(${erdZoom})`;
                    el.style.transformOrigin = 'center top';
                }
            });

            document.getElementById('btnZoomReset')?.addEventListener('click', function () {
                const el = document.getElementById('mermaidDiagramElement');
                if (!el) return;
                erdZoom = 1;
                el.style.transform = `scale(1)`;
            });

            document.getElementById('btnCopyMermaid')?.addEventListener('click', function () {
                const code = @json($erdData['mermaid']);
                navigator.clipboard.writeText(code).then(() => {
                    alert("Mermaid ERD code copied to clipboard!");
                });
            });

            // ==========================================
            // CODE STUDIO MODAL (Feature 3)
            // ==========================================
            const codeStudioModalEl = document.getElementById('codeStudioModal');
            const codeStudioModal = new bootstrap.Modal(codeStudioModalEl);
            const studioEditor = document.getElementById('studioEditorTextarea');
            const studioSql = document.getElementById('studioSqlBlock');
            const studioFilename = document.getElementById('codeStudioFilename');
            const studioSaveStatus = document.getElementById('studioSaveStatus');
            const studioLastModified = document.getElementById('studioLastModified');
            let currentStudioFile = '';

            document.querySelectorAll('.open-code-studio').forEach(btn => {
                btn.addEventListener('click', function () {
                    const filename = this.dataset.filename;
                    currentStudioFile = filename;
                    studioFilename.innerText = filename;
                    studioSaveStatus.innerText = "Loading code...";

                    fetch(`/migration/code/${filename}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                return;
                            }
                            studioEditor.value = data.code;
                            studioSql.innerText = data.sql || '-- SQL Pretend preview';
                            studioLastModified.innerText = "Last modified: " + (data.last_modified || '-');
                            studioSaveStatus.innerText = "Ready to edit";
                            codeStudioModal.show();
                            Prism.highlightElement(studioSql);
                        })
                        .catch(err => {
                            alert("Failed to load migration: " + err);
                        });
                });
            });

            // Save studio changes
            document.getElementById('btnSaveStudioChanges').addEventListener('click', function () {
                if (!currentStudioFile) return;
                studioSaveStatus.innerHTML = `<span class="text-warning"><i class="fa-solid fa-spinner fa-spin me-1"></i> Saving changes...</span>`;

                fetch(`/migration/update/${currentStudioFile}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        code: studioEditor.value
                    })
                })
                .then(res => res.json())
                .then(data => {
                    studioSaveStatus.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i> Saved successfully!</span>`;
                    setTimeout(() => {
                        studioSaveStatus.innerText = "Ready to edit";
                    }, 3000);
                })
                .catch(err => {
                    studioSaveStatus.innerHTML = `<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Save failed: ${err}</span>`;
                });
            });

            // Copy studio code
            document.getElementById('btnCopyStudioCode').addEventListener('click', function () {
                navigator.clipboard.writeText(studioEditor.value).then(() => {
                    alert("PHP Migration code copied to clipboard!");
                });
            });

            // Download from studio
            document.getElementById('btnDownloadStudioFile').addEventListener('click', function () {
                if (currentStudioFile) {
                    window.location.href = `/migration/download/${currentStudioFile}`;
                }
            });

        });

        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            if (el) {
                navigator.clipboard.writeText(el.innerText).then(() => {
                    alert("Copied to clipboard!");
                });
            }
        }
    </script>

</body>

</html>
