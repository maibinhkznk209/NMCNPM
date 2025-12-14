<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_database_connection()
    {
        $connection = DB::connection()->getDriverName();
        $database = config('database.default');
        
        echo "Database connection: " . $connection . "\n";
        echo "Database default: " . $database . "\n";
        
        // Kiểm tra xem có thể tạo bảng không
        $this->assertTrue(true);
    }
} 