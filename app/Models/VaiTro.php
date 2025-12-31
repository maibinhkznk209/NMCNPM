<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VaiTro extends Model
{
    use HasFactory;

    protected $table = 'VAITRO';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'VaiTro',
    ];

    
    public function taiKhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'vaitro_id');
    }

    /**
     * Get role by name
     */
    public static function findByName($roleName)
    {
        return static::where('VaiTro', $roleName)->first();
    }

    /**
     * Get all available roles
     */
    public static function getAllRoles()
    {
        return static::pluck('VaiTro', 'id')->toArray();
    }

    /**
     * Check if role exists
     */
    public static function roleExists($roleName)
    {
        return static::where('VaiTro', $roleName)->exists();
    }
}
