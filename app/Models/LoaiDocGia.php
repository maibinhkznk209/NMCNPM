<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DocGia;
class LoaiDocGia extends Model
{
    use HasFactory;

    protected $table = 'LOAIDOCGIA';
    protected $primaryKey = 'MaDocGia';
    protected $fillable = [
        'TenLoaiDocGia'
    ];

    public $timestamps = false;

    // Relationship với DocGia
    public function LDG_DG()
    {
        return $this->hasMany(DocGia::class, 'MaLoaiDocGia', 'MaLoaiDocGia');
    }
}
