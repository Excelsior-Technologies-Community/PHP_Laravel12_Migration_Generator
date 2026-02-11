# PHP_Laravel12_Migration_Generator

## What is This Project?

This is a small Laravel Web Application that helps you:

* Create database tables without typing artisan commands manually
* Auto‑generate migration files
* Save developer time
* Useful for beginners and interview demos

This project is perfect for learning Laravel internals and showcasing automation skills in your portfolio.

---

## Step 1: Install Laravel 12

Open Terminal / CMD

```
composer create-project laravel/laravel laravel-migration-generator
cd laravel-migration-generator
```

This creates a new Laravel project folder.

---

## Step 2: Run Project

```
php artisan serve
```

Open Browser:

```
http://127.0.0.1:8000
```

You will see the Laravel welcome page.

---

## Step 3: Configure Database

### A. Edit `.env` file

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=migration_gen
DB_USERNAME=root
DB_PASSWORD=
```

### B. Create Database

Open phpMyAdmin → New Database → Name: `migration_gen`

### C. Run Default Migrations

```
php artisan migrate
```

This creates default tables:

* users
* password_reset_tokens
* failed_jobs
* personal_access_tokens
* migrations

---

## Step 4: Create Controller

```
php artisan make:controller MigrationController
```

File created at:

```
app/Http/Controllers/MigrationController.php
```

---

## Step 5: Create Routes

Open:

```
routes/web.php
```

Add:

```
use App\Http\Controllers\MigrationController;

Route::get('/', [MigrationController::class, 'index']);
Route::post('/generate', [MigrationController::class, 'generate']);
```

---

## Step 6: Controller Logic

### Purpose

* Accept table name & fields
* Run artisan command
* Modify migration file
* Insert columns automatically

### Main Features

| Method     | Work                  |
| ---------- | --------------------- |
| index()    | Show form page        |
| generate() | Create migration file |

### Key Functions Used

| Function      | Purpose              |
| ------------- | -------------------- |
| Artisan::call | Run Laravel commands |
| File::get     | Read migration file  |
| File::put     | Update file          |
| preg_replace  | Replace schema code  |

---

## Step 7: Create View (UI)

Create file:

```
resources/views/home.blade.php
```

### UI Features

* Bootstrap 5 design
* Success & error alerts
* Table Name input
* Fields input
* Recent migrations list
* Generate button

---

## Step 8: Test Application

Run server:

```
php artisan serve
```

Open:

```
http://127.0.0.1:8000
```
<img width="1099" height="902" alt="image" src="https://github.com/user-attachments/assets/174c1017-124b-43ad-9c30-fe23d3fc1437" />


### Example Input

| Field      | Value                  |
| ---------- | ---------------------- |
| Table Name | products               |
| Fields     | name,price,description |

Click **Generate Migration**

---

## Step 9: Check Migration File

Go to folder:

```
database/migrations
```

You will see file like:

```
2026_02_11_123456_create_products_table.php
```

---

## Step 10: Run Migration

```
php artisan migrate
```

Now the database table will be created.

---

## Step 11: Verify Database

Open phpMyAdmin → `migration_gen` database.

You will see:

* users
* migrations
* failed_jobs
* personal_access_tokens
* products (generated table)

---

## How It Works Internally

1. User submits form
2. Controller receives data
3. Artisan creates migration
4. Controller edits file
5. Fields are inserted
6. User runs migrate command
7. Table created

---

## Benefits of This Tool

* No need manual artisan commands
* Beginner friendly
* Good portfolio project
* Saves time
* Easy DB structure creation

---

## Optional Enhancements (Advanced Features)

### 1. Field Data Types

Add dropdowns:

* string
* integer
* boolean
* text
* date

### 2. Delete Migration Button

Add file delete option.

### 3. Preview Migration Code

Show code before generating.

### 4. Rollback Button

```
php artisan migrate:rollback
```

### 5. Authentication

Add login system so only admin can generate tables.

### 6. API Version

Create API endpoint to generate migrations.

---

## Real‑World Use Cases

* Rapid Prototyping
* Startup MVPs
* Teaching Laravel
* Interview Demo
* Database Planning

---

## Skills You Learn

* Laravel Controllers
* Routes
* Views (Blade)
* Artisan Commands
* File Handling
* Regex
* Database Migrations
* Bootstrap UI

---

This project demonstrates automation, Laravel core understanding, and practical development workflow, making it an excellent GitHub portfolio addition for PHP Laravel developers.

