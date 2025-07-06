<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'total',
        'number_of_installments',
        'first_due_date',
        'notifications_per_installment',
        'notify_days_before',
        'notify_days_before_secondary',
        'original_total',
    ];

    protected $casts = [
        'first_due_date' => 'date:Y-m-d',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }
}
