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

    /**
     * Compatibility aliases: controller/view/test đang dùng theLoai/tacGias.
     */
    public function theLoai()
    {
        return $this->DS_TL();
    }

    public function tacGias()
    {
        return $this->DS_TG();
    }

    // N- N tác giả qua CT_TACGIA
    public function DS_TG()
    {
        return $this->belongsToMany(
            TacGia::class,
            'CT_TACGIA',
            'MaDauSach',
            'MaTacGia'
        );
    }

    // 1 đầu sách thuộc 1 thể loại
    public function DS_TL()
    {
        return $this->belongsTo(TheLoai::class, 'MaTheLoai', 'MaTheLoai');
    }

    // 1 đầu sách có nhiều bản ghi SACH (theo NXB/năm/trị giá…)
    public function DS_S()
    {
        return $this->hasMany(Sach::class, 'MaDauSach', 'MaDauSach');
    }
}
