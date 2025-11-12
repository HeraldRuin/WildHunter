<?php
namespace Modules\Booking\Gateways;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Payment;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Models\Booking;

class PaypalGateway extends BaseGateway
{
    public $name = 'Paypal Checkout V2';

    public function getOptionsConfigs()
    {
        return [
            [
                'type' => 'checkbox',
                'id' => 'enable',
                'label' => __('Enable PayPal?')
            ],
            [
                'type'       => 'input',
                'id'         => 'name',
                'label'      => __('Custom Name'),
                'std'        => __("Paypal"),
                'multi_lang' => "1"
            ],
            [
                'type'  => 'upload',
                'id'    => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type'  => 'editor',
                'id'    => 'html',
                'label' => __('Custom HTML Description'),
                'multi_lang' => "1"
            ],
            [
                'type'  => 'checkbox',
                'id'    => 'test',
                'label' => __('Enable Sandbox Mode?')
            ],
            [
                'type'    => 'select',
                'id'      => 'convert_to',
                'label'   => __('Convert To'),
                'desc'    => __('In case of main currency does not support by PayPal. You must select currency and input exchange_rate to currency that PayPal support'),
                'options' => $this->supportedCurrency()
            ],
            [
                'type'       => 'input',
                'input_type' => 'number',
                'id'         => 'exchange_rate',
                'label'      => __('Exchange Rate'),
                'desc'       => __('Example: Main currency is VND (which does not support by PayPal), you may want to convert it to USD when customer checkout, so the exchange rate must be 23400 (1 USD ~ 23400 VND)'),
            ],
            [
                'type'      => 'input',
                'id'        => 'test_client_id',
                'label'     => __('Sandbox Client Id'),
                'condition' => 'g_paypal_test:is(1)'
            ],
            [
                'type'      => 'input',
                'id'        => 'test_client_secret',
                'label'     => __('Sandbox Client Secret'),
                'std'       => '',
                'condition' => 'g_paypal_test:is(1)'
            ],
            [
                'type'      => 'input',
                'id'        => 'client_id',
                'label'     => __('Client Id'),
                'condition' => 'g_paypal_test:is()'
            ],
            [
                'type'      => 'input',
                'id'        => 'client_secret',
                'label'     => __('Client Secret'),
                'std'       => '',
                'condition' => 'g_paypal_test:is()'
            ],
        ];
    }

    public function process(Request $request, $booking, $service)
    {

        if (in_array($booking->status, [
            $booking::PAID,
            $booking::COMPLETED,
            $booking::CANCELLED
        ])) {

            throw new Exception(__("Booking status does need to be paid"));
        }
        if (!$booking->pay_now) {
            throw new Exception(__("Booking total is zero. Can not process payment gateway!"));
        }
        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->payment_gateway = $this->id;
        $payment->status = 'draft';

        $data = $this->handlePurchaseData([
            'amount'        => (float)$booking->pay_now,
            'transactionId' => $booking->code . '.' . time()
        ], $booking, $payment);

        $response = $this->createOrder($data);
        $json = $response->json();

        if ($response->successful() and !empty($json['status']) and $json['status'] == 'CREATED') {
            $url  = '';
            foreach ($json['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    $url = $link['href'];
                }
            }
            $payment->save();
            $booking->status = $booking::UNPAID;
            $booking->payment_id = $payment->id;
            $booking->save();
            try{
                event(new BookingCreatedEvent($booking));
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
            }
            response()->json([
                'url' => $url
            ])->send();
        } else {

            // Log to server
            Log::error('Paypal Process Payment: ' . json_encode($json));

            // This is something with paypal, 
            // Should not update order status or payment status here

            // Use br to display error message in html
            $message = implode("<br>", $this->parsePaypalError($json));

            throw new Exception('Paypal Gateway: ' . $message);
        }
    }


    public function processNormal($payment)
    {
        $payment->payment_gateway = $this->id;
        $data = $this->handlePurchaseDataNormal([
            'amount'        => (float)$payment->amount,
            'transactionId' => $payment->code . '.' . time()
        ],  $payment);

        $response = $this->createOrder($data);
        $json = $response->json();

        if ($response->successful() and !empty($json['status']) and $json['status'] == 'CREATED') {
            $url  = '';
            foreach ($json['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    $url = $link['href'];
                }
            }
            if (empty($url)) {
                return [false, $response->getMessage()];
            } else {
                return [true, false, $url];
            }
        } else {
            // Log to server
            Log::error('Paypal Process Normal Payment: ' . json_encode($json));

            // This is something with paypal, 
            // Should not update order status or payment status here

            // Use br to display error message in html
            $message = implode("<br>", $this->parsePaypalError($json));

            throw new Exception('Paypal Gateway: ' . $message);
        }
    }

    public function confirmPayment(Request $request)
    {
        $response = $this->captureOrder($request->input('token'));
        $json = $response->json();
        if ($response->successful() and !empty($json['status'])) {

            $referenceString = $json['purchase_units'][0]['reference_id']; // Format: b_<booking_id>
            if (!$referenceString) {
                return redirect(url('/'))->with("error", __("Booking not found"));
            }
            $referenceId = str_replace('b_', '', $referenceString);
            $booking = app(Booking::class)->find($referenceId);

            if (!$booking) {
                return redirect(url('/'))->with("error", __("Booking not found"));
            }


            // Document: https://developer.paypal.com/docs/api/orders/v2/#orders_capture
            switch ($json['status']) {
                case 'COMPLETED';

                    // Mark payment as completed
                    $payment = $booking->payment;
                if ($payment) {
                    $payment->status = 'completed';
                        $payment->logs = \GuzzleHttp\json_encode($response->json());
                    $payment->save();
                }
                try{
//                    $oldPaynow = (float)$booking->pay_now;
                    $booking->paid += (float)$booking->pay_now;
//                    $booking->pay_now = (float)($oldPaynow - $data['originalAmount'] < 0 ? 0 : $oldPaynow - $data['originalAmount']);
                    $booking->markAsPaid();
                    } catch (\Exception $e) {
                    Log::warning($e->getMessage());
                }
                    return redirect($booking->getDetailUrl())->with("success", __("You payment has been processed successfully"));

                    // Payment was declined — likely by the bank or fraud checks.
                case "DECLINED":
                    // Mark payment as failed
                    $payment = $booking->payment;
                    if ($payment) {
                        $payment->status = 'fail';
                        $payment->logs = \GuzzleHttp\json_encode($response->json());
                        $payment->save();
                    }
                    try {
                        $booking->markAsPaymentFailed();
                    } catch (\Exception $e) {
                        Log::warning($e->getMessage());
                    }
                    return redirect($booking->getDetailUrl())->with("error", __("Payment Failed"));
            }
        } else {

            // Can not capture the payment
            // This is something with paypal, 
            // Should not update order status or payment status here

            Log::error('Paypal Confirm Payment: ' . json_encode($response->json()));

            if (!empty($booking)) {
                return redirect($booking->getDetailUrl(false));
            }
        }

        // Redirect home
        return redirect(url('/'));
    }


    public function confirmNormalPayment()
    {
        $request = \request();
        $response = $this->captureOrder($request->input('token'));
        $json = $response->json();
        if ($response->successful() and !empty($json['status'])) {

            $referenceString = $json['purchase_units'][0]['reference_id']; // Format: p_<payment_id>
            if (!$referenceString) {
                return redirect(url('/'))->with("error", __("Payment not found"));
            }
            $referenceId = str_replace('p_', '', $referenceString);
            $payment = app(Payment::class)->find($referenceId);

            if (!$payment) {
                return redirect(url('/'))->with("error", __("Payment not found"));
            }

            if($payment->status == 'cancel'){
                return [false,__("Your payment has been canceled")];
            }

            // Document: https://developer.paypal.com/docs/api/orders/v2/#orders_capture
            switch ($json['status']) {
                case 'COMPLETED';

                    return $payment->markAsCompleted(\GuzzleHttp\json_encode($response->json()));

                    // Payment was declined — likely by the bank or fraud checks.
                case "DECLINED":
                    // Mark payment as failed
                    return $payment->markAsFailed(\GuzzleHttp\json_encode($response->json()));
            }
        } else {

            Log::error('Paypal Confirm Normal Payment: ' . json_encode($response->json()));
        }

        return [false];
    }

    public function cancelPayment(Request $request)
    {
        $paypalOrderId = $request->query('token');

        // This is to make sure cancel payment is valid
        $paypalOrder = $this->getPaypalOrder($paypalOrderId);
        $json = $paypalOrder->json();

        if ($paypalOrder->successful()) {
            $referenceString = $json['purchase_units'][0]['reference_id']; // Format: b_<booking_id>
            if (!$referenceString) {
                throw new Exception(__("No reference id found"));

                return redirect(url('/'))->with("error", __("Payment not found"));
            }

            $referenceId = str_replace('b_', '', $referenceString);
            $booking = app(Booking::class)->find($referenceId);

            if (!$booking) {
                return redirect(url('/'))->with("error", __("Payment not found"));
            }

            $payment = $booking->payment;
            if ($payment) {
                $payment->status = 'cancel';
                $payment->logs = \GuzzleHttp\json_encode([
                    'customer_cancel' => 1
                ]);
                $payment->save();
            }

            // Refund without check status
            $booking->tryRefundToWallet(false);
            return redirect($booking->getDetailUrl())->with("error", __("You cancelled the payment"));
        }

        return redirect(url('/'));
    }


    //NOTE: This is for Webhook only
    public function callbackPayment(Request $request)
    {

        // $this->validdateWebhook($request);

        // TODO: apply paypal webhook handling

    }

    protected function validdateWebhook($request)
    {
        // TODO: apply paypal webhook validation
    }


    public function handlePurchaseData($data, $booking, &$payment = null)
    {
        $isLocalDevelopment = env('APP_ENV') == 'local';

        $main_currency = setting_item('currency_main');
        $supported = $this->supportedCurrency();
        $convert_to = $this->getOption('convert_to');

        $data = [
            'amount'        => [
                'value' => (string)$booking->pay_now,
                'currency_code' => strtoupper($main_currency),
                'breakdown' => [
                    'item_total' => [
                        'value' => (string)$booking->pay_now,
                        'currency_code' => strtoupper($main_currency),
                    ]
                ]
            ],
            'reference_id' => 'b_' . $booking->id,
            'custom_id' => $booking->id
        ];

        $data['return_url'] = $this->getReturnUrl();
        $data['cancel_url'] = $this->getCancelUrl();

        $rate = 1;
        $needConvert = false;

        if (!array_key_exists($main_currency, $supported)) {
            $exchange_rate = $this->getOption('exchange_rate');

            if (!$convert_to) {
                throw new Exception(__("PayPal does not support currency: :name", ['name' => $main_currency]));
            }
            if (!$exchange_rate) {
                throw new Exception(__("Exchange rate to :name must be specific. Please contact site owner", ['name' => $convert_to]));
            }

            $booking->addMeta('converted_currency', $convert_to);
            $booking->addMeta('converted_amount', $data['amount']['value'] / $exchange_rate);
            $booking->addMeta('exchange_rate', $exchange_rate);

            $newAmount = $data['amount']['value'] / $exchange_rate;
            $formattedAmount = number_format($newAmount, 2);

            $data['originalAmount'] = (float)$data['amount']['value'];
            $data['amount']['value'] = $formattedAmount;
            $data['amount']['breakdown']['item_total']['value'] = $formattedAmount;
            $data['amount']['currency_code'] = strtoupper($convert_to);
            $data['amount']['breakdown']['item_total']['currency_code'] = strtoupper($convert_to);

            // set rate
            $rate = $exchange_rate;
            $needConvert = true;
        }


        // TODO: add items for non-main currency
        // For now, when need convert, we will not add items cuz we not sure items amount is correct after convert
        // total can be different when convert to other currency with exchange_rate, this can cause error when we format price to 2 decimal places
        // So we will not add items for now
        if (!$needConvert) {
            $data['items'] = [];

            // always format price to 2 decimal places
            $price = number_format($data['amount']['value'] / $rate, 2, '.', '');

            $service = $booking->service;

            $data['items'][] = [
                'name' => $service->title,
                'quantity' => "1", // MUST BE STRING
                'unit_amount' => ['value' => (string)$price, 'currency_code' => strtoupper($data['amount']['currency_code'])],
                "sku" => (string)$booking->id, // MUST BE STRING
                'url' => $service->getDetailUrl() ?? '',

                // NOTE: This is to avoid paypal validate image url is public access and return error
                'image_url' => ($service && $service->image_id) ? get_file_url($service->image_id, 'medium') : get_file_url(setting_item('logo_id'), 'full'),
            ];
        }

        // Payer info
        $data['payer'] = [
            'name' => [
                'given_name' => $booking->first_name,
                'surname' => $booking->last_name
            ],
            'email_address' => $booking->email,
            'address' => [
                'country_code' => $booking->country,
                'address_line_1' => $booking->address,
                'address_line_2' => $booking->address_2,
                'admin_area_1' => $booking->state_code ?? $booking->state_name ?? '',
                'postal_code' => $booking->zip_code,
            ]
        ];

        // To make sure paypal will not return error on empty value
        return $this->removeEmptyValue($data);
    }

    public function handlePurchaseDataNormal($data,  &$payment = null)
    {
        $isLocalDevelopment = env('APP_ENV') == 'local';

        $main_currency = setting_item('currency_main');
        $supported = $this->supportedCurrency();
        $convert_to = $this->getOption('convert_to');

        $data = [
            'amount'        => [
                'value' => (string)$payment->amount,
                'currency_code' => strtoupper($main_currency),
                'breakdown' => [
                    'item_total' => [
                        'value' => (string)$payment->amount,
                        'currency_code' => strtoupper($main_currency),
                    ]
                ]
            ],
            'reference_id' => 'p_' . $payment->id,
            'custom_id' => $payment->id
        ];

        $data['returnUrl'] = $this->getReturnUrl(true) . '?pid=' . $payment->code;
        $data['cancelUrl'] = $this->getCancelUrl(true) . '?pid=' . $payment->code;

        $rate = 1;
        $needConvert = false;

        if (!array_key_exists($main_currency, $supported)) {
            $exchange_rate = $this->getOption('exchange_rate');

            if (!$convert_to) {
                throw new Exception(__("PayPal does not support currency: :name", ['name' => $main_currency]));
            }
            if (!$exchange_rate) {
                throw new Exception(__("Exchange rate to :name must be specific. Please contact site owner", ['name' => $convert_to]));
            }

            $payment->addMeta('converted_currency', $convert_to);
            $payment->addMeta('converted_amount', $data['amount']['value'] / $exchange_rate);
            $payment->addMeta('exchange_rate', $exchange_rate);

            $newAmount = $data['amount']['value'] / $exchange_rate;
            $formattedAmount = number_format($newAmount, 2);

            $data['originalAmount'] = (float)$data['amount']['value'];
            $data['amount']['value'] = $formattedAmount;
            $data['amount']['breakdown']['item_total']['value'] = $formattedAmount;
            $data['amount']['currency_code'] = strtoupper($convert_to);
            $data['amount']['breakdown']['item_total']['currency_code'] = strtoupper($convert_to);

            // set rate
            $rate = $exchange_rate;
            $needConvert = true;
        }


        // TODO: add items for non-main currency
        // For now, when need convert, we will not add items cuz we not sure items amount is correct after convert
        // total can be different when convert to other currency with exchange_rate, this can cause error when we format price to 2 decimal places
        // So we will not add items for now
        if (!$needConvert) {
            $data['items'] = [];

            // always format price to 2 decimal places
            $price = number_format($data['amount']['value'] / $rate, 2, '.', '');


            $data['items'][] = [
                'name' => 'Payment',
                'quantity' => "1", // MUST BE STRING
                'unit_amount' => ['value' => (string)$price, 'currency_code' => strtoupper($data['amount']['currency_code'])],
                "sku" => (string)$payment->id, // MUST BE STRING
                'url' => '',

                // NOTE: This is to avoid paypal validate image url is public access and return error
                'image_url' => get_file_url(setting_item('logo_id'), 'full'),
            ];
        }

        // To make sure paypal will not return error on empty value
        return $this->removeEmptyValue($data);
    }

    public function createOrder($data = [])
    {
        $accessToken = $this->getAccessToken();
        // $this->access_token = $accessToken;
        $params = [
            "intent" => "CAPTURE",
            'purchase_units' => [
                [
                    'reference_id' => $data['reference_id'],
                    'amount' => $data['amount'],
                    'items' => $data['items']
                ]
            ],
            'payer' => $data['payer'],
            'application_context' => [
                'return_url' => $data['return_url'],
                'cancel_url' => $data['cancel_url'],
            ],
        ];
        //dd($params);

        $response = Http::withHeaders(['Accept' => 'application/json', 'content-type' => 'application/json', 'Accept-Language' => 'en_US'])
            ->withToken($accessToken['access_token'])
            ->post($this->getUrl('v2/checkout/orders'), $params);
        return $response;
    }

    public function captureOrder($orderId)
    {
        $accessToken = $this->getAccessToken();
        $response = Http::withHeaders(['Accept' => 'application/json', 'content-type' => 'application/json', 'Accept-Language' => 'en_US'])
            ->withToken($accessToken['access_token'])
            ->asForm()
            ->post($this->getUrl('v2/checkout/orders/' . $orderId . '/capture'));
        return $response;
    }


    public function getAccessToken()
    {
        $clientId = $this->getClientId();
        $secret = $this->getClientSecret();
        $response = Http::withHeaders(['Accept' => 'application/json', 'Accept-Language' => 'en_US'])
            ->withBasicAuth($clientId, $secret)
            ->asForm()
            ->post($this->getUrl('v1/oauth2/token'), ['grant_type' => 'client_credentials']);
        $json = $response->json();
        if ($response->successful() and !empty($json['access_token'])) {
            return $json;
        } else {
            if (!empty($json['error_description'])) {
                $message = $json['error_description'];
            }
            if (!empty($json['message'])) {
                $message = $json['message'];
            }
            throw new \Exception('Paypal Gateway: ' . $message);
        }
    }

    public function getClientId()
    {
        $clientId = $this->getOption('client_id');
        if ($this->getOption('test')) {
            $clientId = $this->getOption('test_client_id');
        }
        return $clientId;
    }

    public function getClientSecret()
    {
        $secret = $this->getOption('client_secret');
        if ($this->getOption('test')) {
            $secret = $this->getOption('test_client_secret');
        }
        return $secret;
    }

    public function getUrl($path)
    {
        if ($this->getOption('test')) {
            return 'https://api-m.sandbox.paypal.com/' . $path;
        }
        return 'https://api-m.paypal.com/' . $path;
    }
    public function supportedCurrency()
    {
        return [
            "aud" => "Australian dollar",
            "brl" => "Brazilian real 2",
            "cad" => "Canadian dollar",
            "czk" => "Czech koruna",
            "dkk" => "Danish krone",
            "eur" => "Euro",
            "hkd" => "Hong Kong dollar",
            "huf" => "Hungarian forint 1",
            "inr" => "Indian rupee 3",
            "ils" => "Israeli new shekel",
            "jpy" => "Japanese yen 1",
            "myr" => "Malaysian ringgit 2",
            "mxn" => "Mexican peso",
            "twd" => "New Taiwan dollar 1",
            "nzd" => "New Zealand dollar",
            "nok" => "Norwegian krone",
            "php" => "Philippine peso",
            "pln" => "Polish złoty",
            "gbp" => "Pound sterling",
            "rub" => "Russian ruble",
            "sgd" => "Singapore dollar ",
            "sek" => "Swedish krona",
            "chf" => "Swiss franc",
            "thb" => "Thai baht",
            "usd" => "United States dollar",
        ];
    }


    public function getPaypalOrder($orderId)
    {
        $accessToken = $this->getAccessToken();
        $response = Http::withHeaders(['Accept' => 'application/json', 'content-type' => 'application/json', 'Accept-Language' => 'en_US'])
            ->withToken($accessToken['access_token'])
            ->get($this->getUrl('v2/checkout/orders/' . $orderId));
        return $response;
    }


    // NOTE: This is to remove empty value from array, so that paypal will not return error
    // Support nested array
    protected function removeEmptyValue($array)
    {
        return array_filter(array_map(function ($value) {
            if (is_array($value)) {
                $value = $this->removeEmptyValue($value);
            }
            return $value;
        }, $array), function ($value) {
            return !empty($value) || $value === 0 || $value === '0'; // Keep 0 and '0' as valid
        });
    }


    protected function parsePaypalError($errorResponse)
    {

        $messages = [];
        $name = $errorResponse['name'] ?? 'Error';
        $message = $errorResponse['message'] ?? 'Unknown error';
        $debugId = $errorResponse['debug_id'] ?? null;

        $messages[] = "$name: $message";

        if (!empty($errorResponse['details']) && is_array($errorResponse['details'])) {
            foreach ($errorResponse['details'] as $detail) {
                $field = $detail['field'] ?? 'Unknown field';
                $issue = $detail['issue'] ?? 'Unknown issue';
                $messages[] = "Field {$field}: {$issue}";
            }
        }

        return $messages;
    }
}
