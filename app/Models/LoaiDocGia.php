<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\DocGia;

class LoaiDocGia extends Model
{
    use HasFactory;

    protected $table = 'LOAIDOCGIA';
    protected $primaryKey = 'MaLoaiDocGia';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'MaLoaiDocGia',
        'TenLoaiDocGia',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (blank($model->MaLoaiDocGia)) {
                do {
                    $id = 'LDG' . strtoupper(Str::random(6)); 
                } while (self::whereKey($id)->exists());

                $model->MaLoaiDocGia = $id;
            }
        });
    }

    public function LDG_DG()
    {
        return $this->hasMany(DocGia::class, 'MaLoaiDocGia', 'MaLoaiDocGia');
    }

    /**
     * Alias for controllers/tests that call $loaiDocGia->docGias().
     */
    public function docGias()
    {
        return $this->LDG_DG();
    }
}
