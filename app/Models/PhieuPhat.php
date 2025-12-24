<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhieuPhat extends Model
{
    use HasFactory;

    protected $table = 'PHIEUPHAT';

    protected $primaryKey = 'MaPhieuPhat';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'MaPhieuPhat',
        'MaDocGia',
        'SoTienNop',
        'NgayThu',
    ];

    protected $casts = [
        'SoTienNop' => 'decimal:2',
        'NgayThu' => 'date',
    ];

    /**
     * Route model binding by MaPhieuPhat (NOT id).
     */
    public function getRouteKeyName()
    {
        return 'MaPhieuPhat';
    }

    /**
     * Relationship with DocGia (canonical name used by controllers/tests)
     */
    public function docGia()
    {
        return $this->belongsTo(DocGia::class, 'MaDocGia', 'MaDocGia');
    }

    /**
     * Backward compatible relationship name (if any old code is using PP_DG()).
     */
    public function PP_DG()
    {
        return $this->docGia();
    }

    /**
     * Alias generator used by controller/tests.
     */
    public static function generateMaPhieu(): string
    {
        return self::generateMaPhieuPhat();
    }

    /**
     * Generate unique MaPhieuPhat
     */
    public static function generateMaPhieuPhat(): string
    {
        $year = now()->format('Y');
        $prefix = "PTP{$year}-";

        return DB::transaction(function () use ($prefix) {
            $last = self::query()
                ->where('MaPhieuPhat', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('MaPhieuPhat', 'desc')
                ->value('MaPhieuPhat');

            $nextNumber = $last ? ((int) substr($last, -4) + 1) : 1;

            return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->SoTienNop, 0, ',', '.') . 'đ';
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('NgayThu', '>=', Carbon::now()->subDays($days));
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('NgayThu', Carbon::now()->month)
            ->whereYear('NgayThu', Carbon::now()->year);
    }
}
