<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuyDinh extends Model
{
    protected $table = 'THAMSO';
    protected $primaryKey = 'MaThamSo';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'TenThamSo',
        'GiaTri',
    ];

    public $timestamps = false;

    // Constants for parameter codes
    const MIN_AGE = 'TuoiToiThieu';
    const MAX_AGE = 'TuoiToiDa';
    const CARD_VALIDITY_MONTHS = 'ThoiHanThe';
    const MAX_BOOKS_PER_BORROW = 'SoSachToiDa';
    const BORROW_DURATION_DAYS = 'NgayMuonToiDa';
    const BOOK_PUBLICATION_YEARS = 'SoNamXuatBan';
    const LATE_FINE_PER_DAY = 'TienPhatTreNgay';

    /**
     * Get parameter value by code name
     */
    public static function getValue($name, $default = null)
    {
        $parameter = static::where('TenThamSo', $name)->first();
        return $parameter ? $parameter->GiaTri : $default;
    }

    /**
     * Set parameter value
     */
    public static function setValue($name, $value)
    {
        return static::updateOrCreate(
            ['TenThamSo' => $name],
            ['GiaTri' => $value]
        );
    }

    /**
     * Get all parameters as key-value pairs
     */
    public static function getAllValues()
    {
        return static::pluck('GiaTri', 'TenThamSo')->toArray();
    }

    /**
     * Helper methods for specific parameters
     */
    public static function getMinAge()
    {
        return (int) static::getValue('TuoiToiThieu', 18);
    }

    public static function getMaxAge()
    {
        return (int) static::getValue('TuoiToiDa', 55);
    }

    public static function getCardValidityMonths()
    {
        return (int) static::getValue('ThoiHanThe', 6);
    }

    public static function getMaxBooksPerBorrow()
    {
        return (int) static::getValue('SoSachToiDa', 5);
    }

    public static function getBorrowDurationDays()
    {
        return (int) static::getValue('NgayMuonToiDa', 14);
    }

    public static function getBookPublicationYears()
    {
        return (int) static::getValue('SoNamXuatBan', 8);
    }

    public static function getLateFinePerDay()
    {
        return (int) static::getValue('TienPhatTreNgay', 1000);
    }
}
