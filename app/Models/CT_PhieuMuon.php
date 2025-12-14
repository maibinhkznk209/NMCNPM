<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\PhieuMuon;
use App\Models\Sach;

class CT_PHIEUMUON extends Model
{
    use HasFactory;

    protected $table = 'CT_PHIEUMUON';

    protected $fillable = [
        'MaPhieuMuon',
        'MaSach',
        'NgayTra',
        'TienPhat',
    ];

    protected $casts = [
        'NgayTra' => 'date',
        'TienPhat' => 'decimal:2',
    ];

    public $timestamps = false;

    public function phieuMuon()
    {
        return $this->belongsTo(PhieuMuon::class, 'MaPhieuMuon', 'MaPhieuMuon');
    }

    public function sach()
    {
        return $this->belongsTo(Sach::class, 'MaSach', 'MaSach');
    }

    public function getFormattedNgayTra()
    {
        return $this->NgayTra ? $this->NgayTra->format('d/m/Y') : '';
    }

    public function getDueDate()
    {
        return $this->phieuMuon->getDueDate();
    }

    public function getFormattedDueDate()
    {
        $dueDate = $this->getDueDate();
        return $dueDate ? $dueDate->format('d/m/Y') : '';
    }

    public function isOverdue()
    {
        if ($this->NgayTra) {
            return false; // Already returned
        }
        
        $dueDate = $this->getDueDate();
        return $dueDate && Carbon::now()->isAfter($dueDate);
    }

    public function isDueSoon()
    {
        if ($this->NgayTra) {
            return false; // Already returned
        }
        
        $dueDate = $this->getDueDate();
        if (!$dueDate) {
            return false;
        }
        
        $daysDiff = Carbon::now()->diffInDays($dueDate, false);
        return $daysDiff >= 0 && $daysDiff <= 3; // Due within 3 days
    }

    public function getStatus()
    {
        if ($this->NgayTra) {
            return 'returned';
        }
        
        if ($this->isOverdue()) {
            return 'overdue';
        }
        
        if ($this->isDueSoon()) {
            return 'due-soon';
        }
        
        return 'active';
    }

    public function calculateFine()
    {
        if (!$this->NgayTra) {
            // Not returned yet - calculate based on current date if overdue
            if (!$this->isOverdue()) {
                return 0;
            }
            
            $dueDate = $this->getDueDate();
            $overdueDays = $dueDate->diffInDays(Carbon::now()); // Correct order to avoid negative
            $finePerDay = 1000; // 1,000 VND per day
            
            return $overdueDays * $finePerDay;
        } else {
            // Already returned - calculate based on actual return date
            $dueDate = $this->getDueDate();
            $returnDate = Carbon::parse($this->NgayTra);
            
            if ($returnDate->lte($dueDate)) {
                return 0; // Returned on time or early
            }
            
            $overdueDays = $dueDate->diffInDays($returnDate); // Correct order to avoid negative
            $finePerDay = 1000; // 1,000 VND per day
            
            return $overdueDays * $finePerDay;
        }
    }
}
