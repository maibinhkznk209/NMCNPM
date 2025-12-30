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
    public function borrowBook($bookId, $quantity) {
        $book = Sach::find($bookId);
        if ($book && $book->isAvailable($quantity)) {
            // Logic to create a new borrowing record
            // Update the book quantity
            $book->quantity -= $quantity;
            $book->save();
            // Create a new PhieuMuon record
        } else {
            // Handle the case where the book is not available
        }
    }

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
