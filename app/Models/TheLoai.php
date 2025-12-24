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
    public $timestamps = false;

    protected $fillable = [
        'MaTheLoai',
        'TenTheLoai',
    ];

    public function dauSaches()
    {
        return $this->hasMany(DauSach::class, 'MaTheLoai', 'MaTheLoai');
    }

    public function TL_DS()
    {
        return $this->dauSaches();
    }
}
