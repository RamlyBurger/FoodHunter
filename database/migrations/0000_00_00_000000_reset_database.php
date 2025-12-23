<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * This migration runs first and resets the entire database.
     * It drops all tables to ensure a clean slate.
     */
    public function up(): void
    {
        // Disable foreign key checks to allow dropping tables with constraints
        Schema::disableForeignKeyConstraints();

        // Get all table names from the database
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $columnName = "Tables_in_{$dbName}";

        // Drop all tables except migrations table
        foreach ($tables as $table) {
            $tableName = $table->$columnName;
            if ($tableName !== 'migrations') {
                Schema::dropIfExists($tableName);
            }
        }

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // This migration cannot be reversed
        // Running migrations again will recreate all tables
    }
};
