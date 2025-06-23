<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'total',
        'installments',
        'first_due_date', // Atualizado
        'notifications_per_installment',
        'notify_days_before',
        'notify_days_before_secondary',
        'status',
    ];

    /**
     * Garante que o campo de data seja tratado como um objeto Date.
     */
    protected $casts = [
        'first_due_date' => 'date', // Adicionado
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}