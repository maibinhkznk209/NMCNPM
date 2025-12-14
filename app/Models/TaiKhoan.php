<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'TAIKHOAN';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'HoVaTen',
        'Email',
        'MatKhau',
        'vaitro_id',
    ];

    protected $hidden = [
        'MatKhau',
        'remember_token',
    ];

    protected $casts = [
        'MatKhau' => 'hashed',
    ];

    public $timestamps = false;

    /**
     * Get the password attribute name for authentication
     */
    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    /**
     * Relationship with VaiTro (Many-to-One)
     * Một tài khoản thuộc về một vai trò
     */
    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'vaitro_id');
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('vaitro_id');
    }

    /**
     * Scope for specific role
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('vaitro_id', $roleId);
    }

    /**
     * Get account by email
     */
    public static function findByEmail($email)
    {
        return static::where('Email', $email)->first();
    }

    /**
     * Check if account has specific role
     */
    public function hasRole($roleName)
    {
        return $this->vaiTro && $this->vaiTro->VaiTro === $roleName;
    }

    /**
     * Check if account is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }

    /**
     * Check if account is librarian
     */
    public function isLibrarian()
    {
        return $this->hasRole('Thủ thư');
    }
}
