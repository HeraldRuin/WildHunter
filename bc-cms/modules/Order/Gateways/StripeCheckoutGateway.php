<?php


namespace Modules\Order\Gateways;


use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mockery\Exception;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\Order;
use Stripe\PaymentIntent;

class StripeCheckoutGateway extends BaseGateway
{
    protected $id = 'stripe_checkout';

    public $name = 'Stripe Checkout';

    protected $gateway;

    public function getOptionsConfigs()
    {
        return [
            [
                'type' => 'checkbox',
                'id' => 'enable',
                'label' => __('Enable Stripe Checkout?')
            ],
            [
                'type' => 'input',
                'id' => 'name',
                'label' => __('Custom Name'),
                'std' => __("Stripe"),
                'multi_lang' => "1"
            ],
            [
                'type' => 'upload',
                'id' => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type' => 'editor',
                'id' => 'html',
                'label' => __('Custom HTML Description'),
                'multi_lang' => "1"
            ],
            [
                'type' => 'input',
                'id' => 'stripe_secret_key',
                'label' => __('Secret Key'),
            ],
            [
                'type' => 'input',
                'id' => 'stripe_publishable_key',
                'label' => __('Publishable Key'),
            ],
            [
                'type' => 'checkbox',
                'id' => 'stripe_enable_sandbox',
                'label' => __('Enable Sandbox Mode'),
            ],
            [
                'type' => 'input',
                'id' => 'stripe_test_secret_key',
                'label' => __('Test Secret Key'),
            ],
            [
                'type' => 'input',
                'id' => 'stripe_test_publishable_key',
                'label' => __('Test Publishable Key'),
            ],
            [
                'type' => 'input',
                'readonly'=>1,
                'value'=>route('order.callback',['gateway'=>$this->id]),
                'label' => __('Webhook URL'),
                'desc'=>__("Copy this url and use it for Stripe Webhook Url")
            ],
            [
                'type' => 'input',
                'id' => 'endpoint_secret',
                'label' => __('Endpoint Secret for Webhooks'),
            ],

        ];
    }


    public function process(Order $order)
    {
        if (in_array($order->status, [
            Order::PAID,
            Order::COMPLETED,
            Order::PROCESSING,
            Order::CANCELLED
        ])) {

            throw new Exception(__("Order status does need to be paid"));
        }
        if (!$order->total) {
            throw new Exception(__("Order total is zero. Can not process payment gateway!"));
        }
        $this->setupStripe();
        $items = $order->items;
        $billing = $order->getJsonMeta('billing');
        $order->updateStatus(Order::ON_HOLD);

        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => setting_item('currency_main'),
                    'product_data' => [
                        'name' => $item->product->getBuyableName() ?? '',
                        'images' => [$item->product->getBuyableImage()]
                    ],
                    'unit_amount' => (float)$item->price * 100
                ],
                'quantity' => $item->qty
            ];
        }

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'success_url' => $this->getReturnUrl() . '?oid=' . $order->id . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->getCancelUrl() . '?oid=' . $order->id,
            'customer_email' => $billing['email'] ?? "",
            'line_items' => $lineItems
        ]);
        $order->addMeta('stripe_session_id', $session->id);
        $order->save();

        return ['url'=>$session->url ?? $order->getDetailUrl()];
    }

    public function confirmPayment(Request $request)
    {
        $id = $request->query('oid');
        /**
         * @var Order $order
         */
        $order = Order::find($id);
        $this->setupStripe();
        if (!empty($order)) {
            $session_id = $request->query('session_id');
            if (empty($session_id)) {
                return redirect($order->getDetailUrl());
            }
            if (in_array($order->status, [Order::UNPAID,Order::ON_HOLD])) {
                $session = \Stripe\Checkout\Session::retrieve($session_id);
                if (empty($session)) {
                    return redirect($order->getDetailUrl());
                }
                if ($session->payment_status == 'paid') {
                    if (empty($stripe_charge_id = $order->getMeta('stripe_charge_id'))) {
                        $order->addMeta('stripe_charge_id',$this->getChargeId($session->payment_intent));
                    }

                    $order->addMeta('stripe_setup_intent', $session->setup_intent);
                    $order->addMeta('stripe_intent_id', $session->payment_intent);
                    $order->addMeta('stripe_cs_complete', 1);
                    $order->gateway_transaction_id = $session->payment_intent;
                    $order->addPaymentLog($session);

                    if($order->isExpired()){
                        $order->addNote(OrderNote::ORDER_EXPIRED,__("Payment was success but Order has been expired"));
                        $order->updateStatus(Order::FAILED);
                        return redirect($order->getDetailUrl())->with("success", __("Payment was success but Order has been expired"));
                    }else{
                        $order->pay_date = Carbon::now();
                        $order->updateStatus(Order::PROCESSING);
                        return redirect($order->getDetailUrl())->with("success", __("Your order has been processed successfully"));
                    }

                }
            }
            return redirect($order->getDetailUrl());

        } else {
            return redirect(url('/'));
        }
    }


    public function cancelPayment(Request $request)
    {
        $id = $request->query('oid');
        $order = Order::find($id);
        if (!empty($order) ) {
            if (in_array($order->status, [Order::UNPAID , Order::ON_HOLD])) {
                $order->addPaymentLog(['customer_cancel' => 1]);
                $order->updateStatus(Order::CANCELLED);
            }
            return redirect($order->getDetailUrl())->with("error", __("You cancelled the payment"));
        } else {
            return redirect(url('/'));
        }
    }


    public function tryCreateUser(Order $order)
    {
        $billing = $order->getJsonMeta('billing');
        $customer = \Stripe\Customer::create([
            'address' => $billing['address'] ?? "",
            'email' => $billing['email'] ?? "",
            'phone' => $billing['phone'] ?? "",
            'name' => $billing['first_name'] ?? "" . ' ' . $billing['last_name'] ?? "",
        ]);

        $user = auth()->user();
        $user->stripe_customer_id = $customer->id;
        $user->save();
        return $customer->id;

    }


    public function setupStripe()
    {
        \Stripe\Stripe::setApiKey($this->getSecretKey());
    }

    public function getPublicKey()
    {
        if ($this->getOption('stripe_enable_sandbox')) {
            return $this->getOption('stripe_test_publishable_key');
        }
        return $this->getOption('stripe_public_key');
    }

    public function getSecretKey()
    {
        if ($this->getOption('stripe_enable_sandbox')) {
            return $this->getOption('stripe_test_secret_key');
        }
        return $this->getOption('stripe_secret_key');
    }


    public function getDisplayHtml()
    {
        $locale = app()->getLocale();
        if (!empty($locale)) {
            $html = $this->getOption("html_" . $locale);
        }
        if (empty($html)) {
            $html = $this->getOption("html");
        }

        return $html;
    }

    public function callback(Request $request)
    {
        $this->setupStripe();
        $endpoint_secret = $this->getOption('endpoint_secret');
        $event = NULL;
        try {
            $event = \Stripe\Event::constructFrom($request->all());
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => __('Webhook error while parsing basic request.')], 400);
            // Invalid payload
        }
        if ($endpoint_secret) {
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            try {
                $event = \Stripe\Webhook::constructEvent(
                    json_encode($request->all()), $sig_header, $endpoint_secret
                );
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                return response()->json(['message' => __('Webhook error while validating signature.')], 400);
            }
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $orderIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                /**
                 * @var Order $order
                 */
                $order = Order::where('gateway_transaction_id',$orderIntent->id)->first();
                if (!$order) {
                    return response()->json(['message' => __('Payment not found')], 400);
                }
                $order->paid = (float)$orderIntent->total / 100;
                $order->updateStatus(Order::PROCESSING);
                if (!empty($orderIntent->charges->data)) {
                    $chargeArr= [];
                    foreach ($orderIntent->charges->data as $charge) {
                        if ($charge['paid'] == true) {
                            $chargeArr[]=  $charge['id'];
                        }
                    }
                    if(!empty($chargeArr)){
                        $order->addMeta('stripe_charge_id',$chargeArr);
                    }
                }

                $order->addPaymentLog($orderIntent);
                $order->pay_date = Carbon::now();
                $order->updateStatus(Order::PROCESSING);

            break;
            default:
                return response()->json(['message' => __('Received unknown event type')], 400);
        }
    }
    public function getChargeId($orderIntentId)
    {
        $chargeId = '';
        $this->setupStripe();
        $order_intent = PaymentIntent::retrieve($orderIntentId);
        if (!empty($order_intent->charges->data)) {
            foreach ($order_intent->charges->data as $charge) {
                if ($charge['paid'] == true) {
                    $chargeId = $charge['id'];
                }
            }
        }
        return $chargeId;
    }


}
