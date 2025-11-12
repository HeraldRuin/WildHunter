<?php


namespace Modules\Order\Models;

use App\BaseModel;
use App\Traits\HasMeta;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Modules\Coupon\Models\Coupon;
use Modules\Order\Models\CouponOrder;
use Modules\Order\Emails\OrderEmail;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Helpers\CartManager;
use Modules\Order\Notifications\OrderNotification;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShippingZoneMethod;
use Modules\Product\Models\TaxRate;

class Order extends BaseModel
{

    use SoftDeletes;
    use HasMeta;

    public $type = 'order';
    protected $meta_parent_key = 'order_id';
    protected $metaClass = OrderMeta::class;

    protected $table = 'core_orders';
    const COMPLETED = 'completed'; //
    const FAILED = 'failed';
    const ON_HOLD = 'on_hold';
    const PROCESSING = 'processing';
    const DRAFT = 'draft';
    const CANCELLED = 'cancelled';
    const PAID = 'paid';
    const UNPAID = 'unpaid';
    const REFUNDED = 'refunded';

    const CONFIRMED = 'confirmed';

    const SHIPPED = 'shipped'; //  This status means that the order has been shipped from the warehouse and is on its way to the customer

    const AWAITING_PAYMENT = 'awaiting_payment'; // This mean some how order need payment, like re-pay or something

    protected $casts = [
        'order_date' => 'datetime',
        'pay_date' => 'datetime'
    ];

    // Default value
    protected $attributes = [
        'status' => self::DRAFT,
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function syncTotal()
    {
        $this->subtotal = $this->items->sum('subtotal');

        $this->syncDiscount();

        $discount = $this->discount_amount;
        $shipping = $this->shipping_amount;
        $this->total = $this->subtotal + $shipping - $discount;
        if ($this->total < 0) {
            $this->total = 0;
        }
    }

    public function getDetailUrl()
    {
        return route('order.detail', ['code' => $this->code]);
    }

    protected function gatewayObj(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->gateway ? get_payment_gateway_obj($this->gateway) : false;
            }
        );
    }

    protected function gatewayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $obj = $this->gateway_obj;
                if ($obj) return $obj->getDisplayName();
            }
        );
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->first_name . ' ' . $this->last_name;

            }
        );
    }

    public function shippingDisplayName(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return $this->shipping_first_name . ' ' . $this->shipping_last_name;

            }
        );
    }

    public function shippingFullAddress(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $add = [
                    $this->shipping_address,
                    $this->shipping_city,
                    $this->shipping_state,
                    $this->shipping_postcode,
                    $this->shipping_country,
                ];
                return implode(', ', array_filter($add));
            }
        );
    }

    public static function search($filters)
    {
        $query = parent::query();
        return $query;
    }


    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, CouponOrder::getTableName(), 'order_id', 'coupon_code', 'id', 'code');
    }

    public function coupon_orders()
    {
        return $this->hasMany(CouponOrder::class, 'order_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(User::class, OrderItem::getTableName(), 'order_id', 'vendor_id');
    }

    public function sendOrderNotifications($type)
    {

        // Send Email
        if (setting_item('email_c_' . $type . '_enable')) {
            if ($this->customer) {
                $this->customer->notify(new OrderNotification($this, $type));
            } else {
                Notification::route('mail', $this->email)->notify(new OrderNotification($this, $type));
            }
        }
        if (setting_item('email_v_' . $type . '_enable') and is_vendor_enable()) {
            $vendor_ids = $this->items->pluck('author_id')->all();
            $vendors = User::query()->whereIn('id', $vendor_ids)->get();
            if ($vendors) {
                foreach ($vendors as $vendor) {
                    $vendor->notify(new OrderNotification($this, $type, 'vendor', $vendor));
                }
            }
        }

        if (setting_item('email_a_' . $type . '_enable') and $address = setting_item('admin_email')) {
            Notification::route('mail', $address)->notify(new OrderNotification($this, $type, 'admin'));
        }
        return true;
    }

    public function getOrderReportData($from, $to)
    {
        $report_data = new \stdClass();
        $dataOrder = parent::query()
            ->selectRaw('sum(`total` + `tax_amount` + IFNULL(`shipping_amount`,0)) as total_price,sum(total) as net_sales, sum(IFNULL(`shipping_amount`,0)) as total_ship,sum(discount_amount) as total_discount')
            ->whereBetween('order_date', [$from, $to])
            ->where('status', self::COMPLETED)
            ->first();

        //Gross Sales
        $report_data->gloss_sales = $dataOrder->total_price ?? 0;

        //Net Sales
        $report_data->net_sales = $dataOrder->net_sales ?? 0;

        //Orders Placed
        $report_data->orders_placed = parent::query()
            ->whereBetween('order_date', [$from, $to])
            ->where('status', self::COMPLETED)
            ->get()->count();

        //Items Purchased
        $report_data->items_purchased = OrderItem::query()
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->whereBetween('order_date', [$from, $to]);
                $q->where('status', self::COMPLETED);
            })->get()->count();

        //Charged for shipping
        $report_data->total_shipping = $dataOrder->total_ship ?? 0;

        //Worth of coupons used
        $report_data->coupons_used = $dataOrder->total_discount ?? 0;

        return $report_data;
    }

    public function statues()
    {
        $order_statuses = array(
            static::DRAFT => __('Draft'),
            static::ON_HOLD => __('On-hold'),
            static::PROCESSING => __('Processing'),
            static::COMPLETED => __('Completed'),
            static::CANCELLED => __('Cancelled'),
            static::FAILED => __('Failed'),
            static::REFUNDED => __('Refunded'),
        );
        return apply_filters('order_statues', $order_statuses);
    }

    public function save(array $options = [])
    {
        if (empty($this->code)) {
            $this->code = $this->generateCode();
        }

        if (!$this->order_date) {
            $this->order_date = Carbon::now();
        }
        if (!$this->locale) {
            $this->locale = app()->getLocale();
        }
        return parent::save($options); // TODO: Change the autogenerated stub
    }

    public function saveItems($data = [])
    {
        $order_item_ids = [];
        foreach ($data as $item) {
            if (!empty($item['id'])) {
                $order_item = OrderItem::query()->where('id', $item['id'])->where('order_id', $this->id)->first();
                if (!$order_item) {
                    continue;
                }
            } else {
                $order_item = new OrderItem();
                $order_item->order_id = $this->id;
            }

            /**
             * @var Product $product
             */
            $product = Product::find($item['product_id']);
            $variation = null;
            if ($product->product_type == 'variable') {
                $variation = $product->variations()->whereId($item['variation_id'])->first();
            }

            $order_item->object_id = $product->id;
            $order_item->object_model = 'product';
            $order_item->price = $variation ? $variation->sale_price : $product->sale_price;

            $order_item->qty = $item['qty'];
            $order_item->subtotal = $order_item->price * $item['qty'];
            $order_item->status = $this->status;
            $order_item->variation_id = $item['variation_id'];
            $order_item->vendor_id = $product->author_id;
            $order_item->locale = app()->getLocale();
            $order_item->order_date = $this->order_date;
            $order_item->title = $product->title;
            $order_item->calculateCommission();
            $order_item->save();

            $order_item_ids[] = $order_item->id;
        }
        OrderItem::query()->whereNotIn('id', $order_item_ids)->where('order_id', $this->id)->delete();

        $this->syncTotal();
        $this->save();
    }

    public function saveTax($tax_lists)
    {

        $tax_percent = 0;
        if (!empty($tax_lists)) {
            foreach ($tax_lists as $k => $tax) {
                if (!empty($tax['active']) and !empty($tax['tax_rate'])) {
                    $tax_percent += $tax['tax_rate'];
                } else {
                    unset($tax_lists[$k]);
                }
            }
        }
        $subtotal = $this->subtotal + $this->shipping_amount;
        $this->tax_amount = $subtotal * $tax_percent / 100;
        if (!TaxRate::isPriceInclude()) {
            $this->total += $this->tax_amount;
        }
        $this->save();
        $this->addMeta('tax', $tax_lists);
    }

    public function generateCode()
    {
        return md5(uniqid() . rand(0, 99999));
    }

    public function delete()
    {
        $this->items()->delete();
        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function notes()
    {
        return $this->hasMany(OrderNote::class, 'order_id');
    }

    /**
     * Change Order Status, Add note, Dispatch event
     *
     * @param $status
     * @param $payment_status
     */
    public function updateStatus($status, $payment_status = null)
    {

        if ($status === $this->status) {
            return;
        }

        $old_status = $this->status;
        $this->status = $status;

        // Allow to update payment status along with order status
        if($payment_status){
            $this->payment_status = $payment_status;
        }

        $this->save();

        // Change for Items, update bulk for now
        // TODO: We may need to trigger event for each item, but for now not needed
        $this->items()->update([
            'status' => $this->status
        ]);

        $this->addNote(OrderNote::STATUS_CHANGED, __("Order status changed from :old_status to :new_status", ['old_status' => $old_status,
            'new_status' => $status]), ['old_status' => $old_status,
            'new_status' => $status]);

        OrderStatusUpdated::dispatch($this, $old_status, $status);

    }

    public function addNote($name, $val = '', $extra = [])
    {
        $note = new OrderNote([
            'name' => $name,
            'val' => $val,
            'extra' => $extra
        ]);

        $this->notes()->save($note);
    }

    // This is to check if order need payment, like failed, on hold, draft, awaiting payment ...
    // and total amount is greater than 0
    public function needPayment()
    {
        if (in_array($this->status, [$this::FAILED, $this::ON_HOLD, $this::DRAFT, $this::AWAITING_PAYMENT]) and $this->total) {
            return true;
        }
        return false;
    }

    // This is to check if order need checkout, like cart is not draft or awaiting payment ... 
    // This does not check for total amount, it only checks for status
    public function needCheckout()
    {
        if (in_array($this->status, [$this::FAILED, $this::DRAFT , $this::AWAITING_PAYMENT])) {
            return true;
        }
        return false;
    }

    /**
     * Check if order is expired
     * @return bool
     */
    public function isExpired()
    {
        return $this->expired_at and strtotime($this->expired_at) <= time();
    }

    public function addPaymentLog($val): void
    {
        $this->addMeta('payment_logs', $val, true);
    }

    /**
     * Validate stock of all order items
     *
     */
    public function validate()
    {
        foreach ($this->items as $item) {
            $model = $item->product;
            if ($model) {
                $model->addToCartValidate($item->qty, $item->variation_id);
            }
        }
    }

    public function getTaxRate($billing_country, $shipping_country)
    {
        $data = [
            'status' => 0,
            'tax' => ''
        ];
        switch (setting_item("tax_based_on", 'billing')) {
            case"billing":
                $country = $billing_country;
                break;
            case"shipping":
                if ($shipping_country)
                    $country = $shipping_country;
                break;
            default:
                $country = "";
        }
        // Find Tax By Country
        $tax = TaxRate::select("id", "name", "tax_rate", "city", "postcode", "country", "state")
            ->where("country", $country)
            ->orWhere("country", "*")->get();
        if (!empty($tax)) {
            $data = [
                'status' => 1,
                'prices_include_tax' => setting_item("prices_include_tax", 'yes'),
                'tax' => $tax->toArray(),
            ];
        }
        return $data;
    }

    public function syncTaxChange()
    {
        //Tax
        if (!empty($taxItems = $this->tax)) {

            $tax_rate = $taxItems->sum('tax_rate');

            $total_amount = $this->total;
            $tax_amount = ($total_amount / 100) * $tax_rate;
            if (setting_item("prices_include_tax", 'yes') == "no") {
                $total_amount += $tax_amount;
            }
            $this->total = $total_amount;
            $this->tax_amount = $tax_amount;
            $this->addMeta('prices_include_tax', setting_item("prices_include_tax", 'yes'));
        }
    }

    public function addTax($billing_country, $shipping_country)
    {
        if (TaxRate::isEnable()) {
            $tax = $this->getTaxRate($billing_country, $shipping_country);
            $this->deleteMeta('tax');
            if (!empty($tax['tax'])) {
                foreach ($tax['tax'] as $tax_item) {
                    $this->addMeta('tax', $tax_item, true);
                }

            }
        }
    }

    public function storeCoupon(Coupon $coupon)
    {
        $couponOrder = $this->coupon_orders()->where('coupon_code', $coupon->code)->first();
        if (!$couponOrder) {
            $couponOrder = new CouponOrder();
        }
        $couponOrder->order_id = $this->id;
        $couponOrder->order_status = $this->status;
        $couponOrder->coupon_code = $coupon->code;
        $couponOrder->coupon_amount = $coupon->amount;
        $couponOrder->coupon_discount_type = $coupon->discount_type;
        $couponOrder->save();

        $this->load('coupons');
        $this->load('coupon_orders');

        $this->syncTotal();
        $this->save();
    }

    public function removeCoupon(Coupon $coupon)
    {
        $this->coupon_orders()->where('coupon_code', $coupon->code)->delete();

        $this->load('coupons');
        $this->load('coupon_orders');
        $this->syncTotal();
        $this->save();
    }

    public function syncDiscount()
    {
        // Todo: enable this when we enable coupon system later
        return;


        $items = $this->items;
        $coupons = $this->coupons;
        $totalDiscount = 0;
        $resetDiscount = true;
        if (!empty($items) and $coupons->count() > 0) {
            foreach ($coupons as $c => $coupon) {
                /**
                 * @var Coupon $coupon
                 */
                if (!empty($coupon)) {
                    $services = $coupon->services()->get(['object_id', 'object_model'])->toArray();
                    if (!empty($services)) {
                        foreach ($items as $cart_item_id => $item) {
                            $check = \Arr::where($services, function ($value, $key) use ($item) {
                                if ($value['object_id'] == $item['object_id'] and $value['object_model'] == $item['object_model']) {
                                    return $value;
                                }
                            });
                            if (!empty($check)) {
                                $discount = $coupon->calculatePrice($item->price);
                                if ($resetDiscount) {
                                    //reset discount_amount
                                    $item->discount_amount = 0;
                                }
                                $item->discount_amount += $discount;
                                $item->save();

                                $totalDiscount += $discount;
                            }
                        }
                    } else {
                        $totalDiscount += $coupon->calculatePrice($this->subtotal);
                    }
                }
                $resetDiscount = false;
            }
        }
        if ($totalDiscount < 0) {
            $totalDiscount = 0;
        }
        $this->discount_amount = $totalDiscount;
    }

    public function addShipping($country, $shipping_method)
    {
        $cartManager = app(CartManager::class);
        // if no method setting
        if (ShippingZoneMethod::countMethodAvailable() == 0) {
            return ['status' => 1];
        }
        // find method in zone
        if (empty($shipping_method)) {
            return ['status' => 0, 'message' => 'Please select shipping method.'];
        }
        $list_methods = $cartManager->getMethodShipping($country);
        if (!empty($list_methods['shipping_methods'])) {
            foreach ($list_methods['shipping_methods'] as $method) {
                if ($method['method_id'] == $shipping_method) {
                    $this->shipping_amount = $method['method_cost'];
                    $this->shipping_method = $method;
                    return ['status' => 1];
                }
            }
        }
        // if method not in zone
        return ['status' => 0, 'message' => 'There are no shipping options available.'];
    }


    public function getEditableStatues()
    {
        switch ($this->status) {
            case static::PROCESSING :
                return [static::CANCELLED, static::COMPLETED];
                break;
            case static::ON_HOLD :
                return [static::PROCESSING, static::FAILED];
                break;
            case static::COMPLETED :
                return [static::REFUNDED];
                break;
            case static::FAILED :
                return [self::CANCELLED];
                break;
        }
    }

    public function isEditable()
    {
        return in_array($this->status, [self::DRAFT, self::ON_HOLD]);
    }

    public function getCrossSells()
    {
        $all = [];
        foreach ($this->items as $item) {
            $model = $item->product;
            if ($model and $model->cross_sell) {
                foreach ($model->cross_sell as $product) {
                    $all[$product->id] = $product;
                }
            }
        }

        return collect($all);
    }

    public function tax(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $res = [];
                $meta = $this->getMeta('tax', true, true);
                foreach ($meta as $item) {
                    $res[] = json_decode($item->val, true);
                }
                return collect($res);
            }
        );
    }

    /**
     * Check if order needs shipping or not
     *
     * @return bool
     */
    public function needShipping()
    {
        foreach ($this->items as $item) {
            $model = $item->product;
            if ($model and is_callable($model, 'needShipping') and $model->needShipping()) return true;
        }
        return false;
    }

    public function isPaymentRequired()
    {
        return $this->total > $this->paid;
    }


    public function paymentStatusText(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => status_to_text($attributes['payment_status'] ?? ''),
        );
    }

    public function subtotal()
    {
        return $this->subtotal;
    }

    public function discountTotal()
    {
        return $this->discount_amount;
    }


    // Use to update payment status when payment is change
    public function updatePaymentStatus($status)
    {
        $this->payment_status = $status;
        $this->save();
    }
}

