<?php


namespace Modules\Order\Notifications;


use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Order\Emails\OrderEmail;
use Modules\Order\Models\Order;
use function Symfony\Component\Mime\to;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;


    const NEW_ORDER = 'new_order';
    const CANCELLED_ORDER = 'cancelled_order';
    const REFUNDED_ORDER = 'refunded_order';
    const COMPLETED_ORDER = 'completed_order';

    /**
     * @var Order $_order
     */
    protected Order $_order;

    protected $_to;

    protected $_type;

    protected $_vendor;

    public function __construct(Order $order,$type = 'new_order',$to = 'customer',User $vendor = null)
    {
        $this->_order = $order;
        $this->_to = $to;
        $this->_type = $type;
        $this->_vendor = $vendor;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        $address = $notifiable instanceof AnonymousNotifiable
            ? $notifiable->routeNotificationFor('mail')
            : $notifiable->email;

        $mail =  (new OrderEmail($this->_type,$this->_order,$this->_to,$this->_vendor))->to($address);

        if($this->_to == 'customer'){
            $mail->locale($this->_order->locale);
        }
        return $mail;
    }
}
