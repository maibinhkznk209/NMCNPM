<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DauSach extends Model
{
    use HasFactory;

    protected $table = 'DAUSACH';
    protected $primaryKey = 'MaDauSach';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'TenDauSach',
        'MaTheLoai',
        'NgayNhap',
    ];

    protected $casts = [
        'NgayNhap' => 'date',
    ];

    
    public function theLoai()
    {
        return $this->DS_TL();
    }

    public function tacGias()
    {
        return $this->DS_TG();
    }


    public function DS_TG()
    {
        return $this->belongsToMany(
            TacGia::class,
            'CT_TACGIA',
            'MaDauSach',
            'MaTacGia'
        );
    }


    public function DS_TL()
    {
        return $this->belongsTo(TheLoai::class, 'MaTheLoai', 'MaTheLoai');
    }


    public function DS_S()
    {
        return $this->hasMany(Sach::class, 'MaDauSach', 'MaDauSach');
    }
}
