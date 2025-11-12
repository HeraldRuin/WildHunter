<?php


namespace Modules\Order\Emails;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Modules\Order\Models\Order;

class OrderEmail extends \Illuminate\Mail\Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    const SHORT_CODES = [
        'site_title','site_url', 'order_date', 'order_number'
    ];

    protected Order $_order;
    protected $email_to;
    protected $vendor;
    protected $email_type;

    const NEW_ORDER = 'new_order';
    const CANCELLED_ORDER = 'cancelled_order';
    const REFUNDED_ORDER = 'refunded_order';

    public function __construct($email_type = '',Order $order = null,$to = 'customer',$vendor = null)
    {
        $this->_order = $order;
        $this->email_to = $to;
        $this->vendor = $vendor;
        $this->email_type = $email_type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'email_to'=>$this->email_to,
            'order'=>$this->_order,
            'vendor'=>$this->vendor,
            'email_type'=>$this->email_type
        ];
        $subject = $this->getSubject();
        switch ($this->email_type){
            case "new_order":
                $view = 'order.emails.'.$this->email_type;
                break;
            default:
                $view = 'order.emails.updated_order';
                break;
        }
        return $this->subject($this->replaceShortCode($subject))->view($view,$data);
    }

    public function getSubject(){
        switch ($this->email_to){
            case "admin":
                return setting_item_with_lang('email_a_'.$this->email_type.'_subject');
                break;
            case "vendor":
                return setting_item_with_lang('email_v_'.$this->email_type.'_subject');
                break;
            case "customer":
            default:
                return setting_item_with_lang('email_c_'.$this->email_type.'_subject');
                break;
        }
    }


    public function replaceShortCode($content){
        foreach (self::SHORT_CODES as $code){
            if(Str::contains($content,'['.$code.']')){

                switch ($code){
                    case "site_title":
                        $value = setting_item('site_title');
                        break;
                    case "site_url": $value = url('/'); break;
                    case "order_date": $value = display_datetime($this->_order->order_date); break;
                    case "order_number": $value = $this->_order->id; break;

                    default:
                        $value = $this->_order->getAttribute($code);
                        break;
                }
                $content = Str::replace('['.$code.']',$value,$content);
            }
        }
        return $content;
    }
}
