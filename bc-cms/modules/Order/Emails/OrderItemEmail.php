<?php


namespace Modules\Order\Emails;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;

class OrderItemEmail extends \Illuminate\Mail\Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    const SHORT_CODES = [
        'site_title','site_url', 'order_date', 'order_number'
    ];

    /**
     * @var OrderItem|null
     */
    protected $_order_item;
    protected $email_to;
    protected $vendor;
    protected $email_type;

    const CANCELLED_ORDER = 'cancelled_order';
    const REFUNDED_ORDER = 'refunded_order';

    public function __construct($email_type = '',OrderItem $order = null,$to = 'customer',$vendor = null)
    {
        $this->_order_item = $order;
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
            'orderItem'=>$this->_order_item,
            'vendor'=>$this->vendor,
            'email_type'=>$this->email_type,
            'order'=>$this->_order_item->order
        ];
        $subject = $this->getSubject();
        switch ($this->email_type){
            default:
                $view = 'order.emails.order-item.updated_order';
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
                    case "order_date": $value = display_datetime($this->_order_item->order_date); break;
                    case "order_number": $value = $this->_order_item->id; break;

                    default:
                        $value = $this->_order_item->getAttribute($code);
                        break;
                }
                $content = Str::replace('['.$code.']',$value,$content);
            }
        }
        return $content;
    }
}
