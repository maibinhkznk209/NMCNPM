<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\TheLoai;
use App\Models\TacGia;
use App\Models\Sach;


class DauSach extends Model
{
    use HasFactory;
    protected $table = 'DAUSACH';
    protected $primaryKey = 'MaDauSach';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'TenDauSach',
        'MaTheLoai',
        'NgayNhap'
    ];

    protected $casts = [
        'NgayNhap' => 'date',
    ];

    public $timestamps = false;

    

    // Quan hệ one-to-many với TacGia (mỗi sách có 1 tác giả)
   public function DS_TG()
{
    return $this->belongsToMany(
        TacGia::class,
        'CT_TACGIA',
        'MaDauSach',
        'MaTacGia'
    );
}

    // Quan hệ many-to-many với TheLoai (sách có thể thuộc nhiều thể loại)
    public function DS_TL()
    {
        return $this->belongsTo(TheLoai::class, 'MaTheLoai', 'MaTheLoai');
    }
     public function DS_S()
    {
        return $this->hasMany(Sach::class, 'MaDauSach', 'MaDauSach');
    }
}
 

   