<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Trash</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            font-family: 'Segoe UI', sans-serif;
        }

        .main-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
        }

        .card-header {
            background: linear-gradient(90deg, #dc3545, #b02a37);
            color: #fff;
            padding: 20px 30px;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 700;
        }

        .table th {
            background: #212529;
            color: #fff;
            text-align: center;
        }

        .table td {
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f5f9ff;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-success {
            background: #198754;
            border: none;
        }

        .btn-danger {
            border: none;
        }

        .btn-primary {
            border: none;
        }

        .btn-success:hover {
            background: #157347;
        }

        .btn-danger:hover {
            background: #bb2d3b;
        }

        .btn-primary:hover {
            background: #0b5ed7;
        }

        .empty-box {
            padding: 60px;
            text-align: center;
        }

        .empty-box i {
            font-size: 70px;
            color: #adb5bd;
        }

        .footer {
            background: #f8f9fa;
            text-align: center;
            padding: 15px;
            font-weight: 600;
        }

        .badge-count {
            background: #dc3545;
            color: #fff;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 15px;
        }
    </style>

</head>

<body>

    <div class="container py-5">

        <div class="card main-card">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h3>🗑 Migration Trash</h3>
                        <small>Restore or permanently delete migration files.</small>
                    </div>

                    <div>

                        <span class="badge-count">
                            Total : {{ $files->count() }}
                        </span>

                        <a href="{{ route('home') }}"
                            class="btn btn-light ms-3">
                            ← Back
                        </a>

                    </div>

                </div>

            </div>

            <div class="card-body">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">

                    {{ session('success') }}

                    <button class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">

                    {{ session('error') }}

                    <button class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>
                @endif

                @if($files->count())

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>

                            <tr>

                                <th width="8%">#</th>
                                <th>Migration File</th>
                                <th width="22%">Deleted File</th>
                                <th width="25%">Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($files as $file)

                            <tr>

                                <td class="text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td>

                                    <strong>
                                        {{ $file->getFilename() }}
                                    </strong>

                                </td>

                                <td class="text-center">

                                    {{ date('d M Y H:i', $file->getMTime()) }}

                                </td>

                                <td class="text-center">

                                    <form
                                        action="{{ route('migration.restore',$file->getFilename()) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf

                                        <button
                                            class="btn btn-success btn-sm">

                                            Restore

                                        </button>

                                    </form>

                                    <form
                                        action="{{ route('migration.destroy',$file->getFilename()) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            onclick="return confirm('Delete permanently?')"
                                            class="btn btn-danger btn-sm">

                                            Delete Forever

                                        </button>

                                    </form>

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                @else

                <div class="empty-box">

                    <div style="font-size:70px;">🗑</div>

                    <h3 class="mt-3">

                        Trash is Empty

                    </h3>

                    <p class="text-muted">

                        No deleted migration files found.

                    </p>

                    <a href="{{ route('home') }}"
                        class="btn btn-primary">

                        Back to Home

                    </a>

                </div>

                @endif

            </div>

            <div class="footer">

                Laravel 12 Migration Generator • Trash Management

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>