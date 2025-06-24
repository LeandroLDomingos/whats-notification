<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    use HasFactory, Notifiable; 

    protected $fillable = ['name', 'email', 'phone'];

    /**
     * Define para qual número de telefone as notificações do WhatsApp devem ser enviadas.
     * O número deve estar no formato internacional, ex: 5531999998888.
     */
    public function routeNotificationForWhatsapp($notification)
    {
        return $this->phone;
    }
}