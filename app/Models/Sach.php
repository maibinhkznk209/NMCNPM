<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NhaXuatBan;
use App\Models\CT_PhieuMuon;
use App\Models\CuonSach;

use App\Models\DauSach;

class Sach extends Model
{
    use HasFactory;
    protected $table = 'SACH';
    protected $primaryKey = 'MaSach';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'MaDauSach',
        'MaNXB',
        'NamXuatBan',
        'TriGia',
        'SoLuong'
    ];

    protected $casts = [
        'SoLuong' => 'integer',
        'TriGia' => 'decimal:2',
    ];

    public $timestamps = false;



    // Quan hệ one-to-many với NhaXuatBan (mỗi sách có 1 nhà xuất bản)
    public function S_NXB()
    {
        return $this->belongsTo(NhaXuatBan::class, 'MaNXB', 'MaNXB');
    }
    public function S_CS()
    {
        return $this->hasMany(CuonSach::class, 'MaSach', 'MaSach');
    }
    public function S_PM()
    {
    return $this->hasMany(
        CT_PhieuMuon::class,
        'MaSach',
        'MaSach'
    );
    }
}
 