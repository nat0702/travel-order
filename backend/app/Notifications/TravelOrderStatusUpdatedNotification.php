<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TravelOrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(    
        protected TravelOrder $travelOrder
    )
    { }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'destination' => $this->travelOrder->destination,
            'status' => $this->travelOrder->status,
            'message' => $this->travelOrder->status === 'aprovado'
                ? "Seu pedido de viagem para {$this->travelOrder->destination} foi aprovado."
                : "Seu pedido de viagem para {$this->travelOrder->destination} foi cancelado.",
        ];
    }
}
