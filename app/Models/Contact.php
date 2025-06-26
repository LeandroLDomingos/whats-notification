<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'phone'];

    /**
     * Define o relacionamento onde um Contato PODE TER MUITAS Cobranças.
     * ESTE É O MÉTODO QUE ESTAVA FALTANDO.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }
    /**
     * Define para qual número de telefone as notificações do WhatsApp devem ser enviadas.
     * O número deve estar no formato internacional, ex: 5531999998888.
     */
    public function routeNotificationForWhatsapp($notification)
    {
        return $this->phone;
    }

    /**
     * Relacionamento para todas as mensagens agendadas de um contato.
     */
    public function scheduledMessages(): HasMany
    {
        return $this->hasMany(ScheduledMessage::class);
    }

    /**
     * Relacionamento para obter apenas a mensagem mais recente de um contato.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ScheduledMessage::class)->latestOfMany('scheduled_for');
    }
}