<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoaiDocGia;
use App\Models\PhieuPhat;
use App\Models\PhieuMuon;
use Illuminate\Support\Str;

class DocGia extends Model
{
    use HasFactory;

    protected $table = 'DOCGIA';
    protected $primaryKey = 'MaDocGia';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'MaDocGia',
        'TenDocGia',
        'Email',
        'NgaySinh',
        'DiaChi',
        'MaLoaiDocGia',
        'NgayLapThe',
        'NgayHetHan',
        'TongNo',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (blank($model->MaDocGia)) {
                do {
                    $id = 'DG' . strtoupper(Str::random(6)); 
                } while (self::whereKey($id)->exists());

                $model->MaDocGia = $id;
            }
        });
    }
    public function DG_LDG()
    {
        return $this->belongsTo(LoaiDocGia::class, 'MaLoaiDocGia', 'MaLoaiDocGia');
    }
     
    /**
     * Alias for compatibility with controllers/views that call with(\'loaiDocGia\').
     */
    public function loaiDocGia()
    {
        return $this->DG_LDG();
    }

public function DG_PP()
    {
        return $this->hasMany(PhieuPhat::class, 'MaDocGia', 'MaDocGia');
    }
}
