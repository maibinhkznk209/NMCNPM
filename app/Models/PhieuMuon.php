<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\DocGia;
use App\Models\CT_PHIEUMUON;

class PhieuMuon extends Model
{
    use HasFactory;

    protected $table = 'PHIEUMUON';
    protected $primaryKey = 'MaPhieuMuon';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
    'MaDocGia',
    'MaCuonSach',
    'quantity', // Add this line to track quantity borrowed

        'MaPhieuMuon',
        'MaDocGia',
        'NgayMuon',
        'NgayHenTra',
    ];

    protected $casts = [
        'NgayMuon' => 'date',
        'NgayHenTra' => 'date',
    ];

    protected $appends = ['TrangThai'];


    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($phieuMuon) {
            $phieuMuon->PM_S()->delete();
        });
    }

    public function PM_DG()
    {
        return $this->belongsTo(DocGia::class, 'MaDocGia', 'MaDocGia');
    }

   public function PM_S()
{
    return $this->hasMany(
        CT_PHIEUMUON::class,
        'MaPhieuMuon',
        'MaPhieuMuon'
    );
}

    // Method to generate unique MaPhieuMuon
    public static function generateMaPhieu()
    {
        $prefix = 'PM';
        $year = date('Y');
        

        $latestPhieu = self::where('MaPhieuMuon', 'LIKE', $prefix . $year . '%')
                          ->orderBy('MaPhieuMuon', 'desc')
                          ->first();
        
        if ($latestPhieu) {

            $lastNumber = (int)substr($latestPhieu->MaPhieuMuon, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: PM2025-0001, PM2025-0002, ...
        return $prefix . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }


    public function getTrangThaiAttribute()
    {
        $chiTiet = $this->PM_S;
        
        if ($chiTiet->isEmpty()) {
            return 'active';
        }

        $allReturned = $chiTiet->every(function ($item) {
            return !is_null($item->NgayTra);
        });

        if ($allReturned) {
            return 'returned';
        }

        // Check for overdue books
        $hasOverdue = $chiTiet->filter(function ($item) {
            return is_null($item->NgayTra) && $this->isOverdue($item);
        })->isNotEmpty();

        if ($hasOverdue) {
            return 'overdue';
        }

        // Check for due soon books
        $hasDueSoon = $chiTiet->filter(function ($item) {
            return is_null($item->NgayTra) && $this->isDueSoon($item);
        })->isNotEmpty();

        if ($hasDueSoon) {
            return 'due-soon';
        }

        return 'active';
    }

    private function isOverdue($chiTiet)
    {
        $dueDate = Carbon::parse($this->NgayMuon)->addDays(14); // Assuming 14 days loan period
        return Carbon::now()->isAfter($dueDate);
    }

    private function isDueSoon($chiTiet)
    {
        $dueDate = Carbon::parse($this->NgayMuon)->addDays(14);
        $daysDiff = Carbon::now()->diffInDays($dueDate, false);
        return $daysDiff >= 0 && $daysDiff <= 3; // Due within 3 days
    }

    public function getFormattedNgayMuon()
    {
        return $this->NgayMuon ? $this->NgayMuon->format('d/m/Y') : '';
    }

    public function getDueDate()
    {
        if (!$this->NgayMuon) {
            return null;
        }
        
        $borrowDurationDays = QuyDinh::getBorrowDurationDays();
        return $this->NgayMuon->copy()->addDays($borrowDurationDays);
    }

    public function getFormattedDueDate()
    {
        $dueDate = $this->getDueDate();
        return $dueDate ? $dueDate->format('d/m/Y') : '';
    }
}
