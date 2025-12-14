<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Sach;

class NhaXuatBan extends Model
{
    use HasFactory;
    protected $table = 'NHAXUATBAN';
    protected $primaryKey = 'MaNXB';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'TenNXB',
    ];

    public $timestamps = false;

    public function NXB_S()
    {
        return $this->hasMany(Sach::class, 'MaNXB', 'MaNXB');
    }
}
