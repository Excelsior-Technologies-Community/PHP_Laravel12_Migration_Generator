<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Migration Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center mb-0">Laravel Migration Generator</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="/generate">
                            @csrf
                            <div class="mb-3">
                                <label for="table" class="form-label">Table Name:</label>
                                <input type="text" class="form-control" id="table" name="table" 
                                       placeholder="e.g., products" required>
                                <div class="form-text">Use lowercase, underscores allowed</div>
                            </div>

                            <div class="mb-3">
                                <label for="fields" class="form-label">Fields (comma separated):</label>
                                <input type="text" class="form-control" id="fields" name="fields" 
                                       placeholder="e.g., name,price,description" required>
                                <div class="form-text">Enter field names separated by commas</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Generate Migration</button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="mt-3">
                            <h5>Generated Migrations:</h5>
                            <div class="list-group">
                                @php
                                    $migrations = glob(database_path('migrations/*.php'));
                                @endphp
                                @foreach(array_slice($migrations, -5) as $migration)
                                    <div class="list-group-item">
                                        {{ basename($migration) }}
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(count($migrations) > 0)
                                <div class="mt-3">
                                    <form action="/generate" method="POST">
                                        @csrf
                                        <input type="hidden" name="table" value="run_migrations">
                                        <input type="hidden" name="fields" value="dummy">
                                        <button type="submit" class="btn btn-success w-100" 
                                                onclick="event.preventDefault(); 
                                                        alert('Run in terminal: php artisan migrate')">
                                            Run Migrations
                                        </button>
                                    </form>
                                    <div class="form-text text-center mt-1">
                                        Note: Actually run migrations via terminal
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <small class="text-muted">Laravel 12 Migration Generator Tool</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>