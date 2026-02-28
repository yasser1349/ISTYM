<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    protected $table = 'company_settings';
    
    protected $fillable = [
        'name',
        'ice',
        'email',
        'phone',
        'website',
        'address',
        'logo',
        'currency',
        'tax_rate',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
    ];

    /**
     * Get the company settings (singleton pattern)
     */
    public static function getSettings()
    {
        return self::first() ?? self::create([
            'name' => 'ISTYM',
            'email' => 'contact@istym.ma',
        ]);
    }
}
