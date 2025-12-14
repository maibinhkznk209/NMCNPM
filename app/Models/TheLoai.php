<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DauSach;

class TheLoai extends Model
{
    use HasFactory;
    protected $table = 'THELOAI';
    protected $primaryKey = 'MaTheLoai';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'TenTheLoai',
    ];

    public $timestamps = false;

    public function TL_DS()
    {
        return $this->hasMany(DauSach::class, 'MaTheLoai', 'MaTheLoai');
    }
}
