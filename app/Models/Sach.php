<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sach extends Model
{
    use HasFactory;

    protected $table = 'SACH';
    protected $primaryKey = 'MaSach';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'MaDauSach',
        'MaNXB',
        'quantity', // Add this line

        'NamXuatBan',
        'TriGia',
        'SoLuong',
    ];

    protected $casts = [
        'SoLuong' => 'integer',
        'TriGia' => 'decimal:2',
    ];

    /**
     * Compatibility aliases:
     * Một số controller/view/test gọi các tên này.
     */
    public function dauSach()
    {
        return $this->S_DS();
    }

    public function nhaXuatBan()
    {
        return $this->S_NXB();
    }

    public function cuonSachs()
    {
        return $this->S_CS();
    }

    // Quan hệ với NXB
    public function S_NXB()
    {
        return $this->belongsTo(NhaXuatBan::class, 'MaNXB', 'MaNXB');
    }

    // 1 SACH có nhiều CUONSACH
    public function S_CS()
    {
        return $this->hasMany(CuonSach::class, 'MaSach', 'MaSach');
    }

    // Chi tiết phiếu mượn theo sách
    public function S_PM()
    {
        return $this->hasMany(CT_PhieuMuon::class, 'MaSach', 'MaSach');
    }

    // Thuộc DAUSACH
    public function S_DS()
    {
        return $this->belongsTo(DauSach::class, 'MaDauSach', 'MaDauSach');
    }
}
