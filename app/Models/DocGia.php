<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoaiDocGia;
use App\Models\PhieuPhat;
use App\Models\PhieuMuon;

class DocGia extends Model
{
    use HasFactory;

    protected $table = 'DOCGIA';
    protected $primaryKey = 'MaDocGia';
    protected $fillable = [
        'HoTen',
        'MaLoaiDocGia',
        'NgaySinh',
        'DiaChi',
        'Email',
        'NgayLapThe',
        'NgayHetHan',
        'TongNo'
    ];

    // Accessor for compatibility
    public function getTenAttribute()
    {
        return $this->HoTen;
    }

    protected $casts = [
        'NgaySinh' => 'date',
        'NgayLapThe' => 'date',
        'NgayHetHan' => 'date'
    ];

    public $timestamps = false;

    // Custom date format for JSON serialization
    protected $dateFormat = 'Y-m-d';

    // Specify date format for JSON output
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    // Relationship với LoaiDocGia
    public function DG_LDG()
    {
        return $this->belongsTo(LoaiDocGia::class, 'MaLoaiDocGia', 'MaLoaiDocGia');
    }
     public function DG_PP()
    {
        return $this->hasMany(PhieuPhat::class, 'MaDocGia', 'MaDocGia');
    }
}
