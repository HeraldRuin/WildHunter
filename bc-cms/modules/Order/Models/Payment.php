<?php


namespace Modules\Order\Models;


use App\BaseModel;
use App\Traits\HasMeta;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Order\Events\PaymentUpdated;
use Modules\Order\Models\Order;
use Carbon\Carbon;


class Payment extends BaseModel
{
    use SoftDeletes, HasMeta;

    protected $meta_parent_key = 'payment_id';
    protected $metaClass = PaymentMeta::class;

    const COMPLETED = 'completed';
    const PENDING = 'pending';
    const FAILED = 'failed';

    protected $table = 'core_payments';

    protected $attributes = [
        'status' => 'draft'
    ];

    protected $casts = [
        'logs' => 'array'
    ];


    public function getDetailUrl()
    {
        switch ($this->object_model) {
            case "order":
                $order = Order::find($this->object_id);
                $url = $order->getDetailUrl();
                break;
        }
        return $url;
    }

    public function order()
    {
        switch ($this->object_model) {
            default:
                return $this->belongsTo(Order::class, 'object_id');
        }
    }

    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();

        PaymentUpdated::dispatch($this);
    }

    public function addPaymentLog($val): void
    {
        $this->addMeta('payment_logs', $val, true);
    }


    public function markAsPending($data = null)
    {

        // Allow to save more data to payment
        if (!empty($data['gateway_transaction_id'])) {
            $this->gateway_transaction_id = $data['gateway_transaction_id'];
        }

        $this->updateStatus(Payment::PENDING);
    }

    /**
     * @param string|array $logs
     * @return void
     */
    public function markAsFailed($logs = null)
    {
        $this->updateStatus(Payment::FAILED);

        if (!empty($logs)) {
            $this->addPaymentLog($logs);
        }

        $order = $this->order;
        if($order){

            // TODO: Add some logs to order
            $order->updateStatus(Order::FAILED, $this->status);
            $order->addPaymentLog($logs);

            return $order;
        }
    }

    public function markAsCompleted($data = null)
    {
        if(in_array($this->status, [Payment::COMPLETED, Payment::FAILED])){
            // Already completed or failed
            return;
        }

        // Update paid
        // TODO: paid amount should be get from gateway response
        $this->paid = $this->amount;

        $this->updateStatus(Payment::COMPLETED);

        if (!empty($data['logs'])) {
            $this->addPaymentLog($data['logs']);
        }

        // Flag to update order status when payment is completed
        if (!empty($data['updateOrder'])) {
            $order = $this->order;
            if(!$order){
                return;
            }

            // Update paid
            // TODO: paid amount should be get from gateway response
            $order->paid  = min($order->total, $order->paid + $this->amount);

            // Also add a log to order
            if(!empty($data['logs'])){
                $order->addPaymentLog($data['logs']);
            }

            // Add some notes to order
            if($order->isExpired()){
                $order->addNote(OrderNote::ORDER_EXPIRED,__("Payment was success but Order has been expired"));
                $order->updateStatus(Order::FAILED);
            }else{
                $order->pay_date = Carbon::now();

                // TODO: Use Processing as status when order need shipping
                // for now, we use completed as status when payment is completed
                $order->updateStatus(Order::COMPLETED, $this->status);
            }

            // Return order
            return $order;
        }
    }


    public function markAsCancelled($data = null)
    {
        $this->updateStatus(Payment::CANCELLED);

        if (!empty($data['logs'])) {
            $this->addPaymentLog($data['logs']);
        }

        $order = $this->order;

        if($order){

            // TODO: Add note to order

            $order->updateStatus(Order::CANCELLED, $this->status);
            $order->addPaymentLog($data['logs']);

            return $order;
        }
    }
}
