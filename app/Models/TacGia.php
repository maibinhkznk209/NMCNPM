<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DauSach;

class TacGia extends Model
{
    use HasFactory;
    protected $table = 'TACGIA';
    protected $primaryKey = 'MaTacGia';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'TenTacGia',
    ];

    public $timestamps = false;
public function TG_DS()
{
    return $this->belongsToMany(
        DauSach::class,
        'CT_TACGIA',
        'MaTacGia',
        'MaDauSach'
    );
}
}