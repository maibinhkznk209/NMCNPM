<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\DocGia;
class PhieuPhat extends Model
{
    use HasFactory;

    protected $table = 'PHIEUPHAT';
    protected $primaryKey = 'MaPhieuPhat';

    protected $fillable = [
        'MaDocGia',
        'SoTienNop',
        'NgayThu'
    ];

    protected $casts = [
        'SoTienNop' => 'decimal:2',
        'NgayThu' => 'date'
    ];

    public $timestamps = false;

    /**
     * Relationship with DocGia
     */
    public function PP_DG()
    {
        return $this->belongsTo(DocGia::class, 'MaDocGia', 'MaDocGia');
    }

    /**
     * Generate unique receipt code
     */
    public static function generateMaPhieuPhat()
    {
        $prefix = 'PTP';
        $year = date('Y');
        
        // Lấy số thứ tự cao nhất trong năm hiện tại
        $latestPhieu = self::where('MaPhieuPhat', 'LIKE', $prefix . $year . '%')
                          ->orderBy('MaPhieuPhat', 'desc')
                          ->first();
        
        if ($latestPhieu) {
            // Lấy số thứ tự từ mã phiếu cuối cùng
            $lastNumber = (int)substr($latestPhieu->MaPhieuPhat, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: PTP2025-0001, PTP2025-0002, ...
        return $prefix . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->SoTienNop, 0, ',', '.') . 'đ';
    }

    /**
     * Scope for recent payments
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('NgayThu', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('NgayThu', Carbon::now()->month)
                    ->whereYear('NgayThu', Carbon::now()->year);
    }

    /**
     * Get payment status text
     */
    public function getPaymentStatusAttribute()
    {
        return $this->TinhTrangThanhToan == 1 ? 'Đã thanh toán' : 'Chưa thanh toán';
    }

    /**
     * Check if paid
     */
    public function isPaid()
    {
        return $this->TinhTrangThanhToan == 1;
    }

    /**
     * Mark as paid
     */
    public function markAsPaid()
    {
        $this->update(['TinhTrangThanhToan' => 1]);
    }

    /**
     * Mark as unpaid
     */
    public function markAsUnpaid()
    {
        $this->update(['TinhTrangThanhToan' => 0]);
    }
}
