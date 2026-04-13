<?php

namespace Modules\Booking\Gateways;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Gateways\DTO\PaykeeperOrderDTO;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;
use function Symfony\Component\Translation\t;

class PaykeeperGateway extends BaseGateway
{
    public $name = 'Pay Keeper';

    public function getOptionsConfigs()
    {
        return [
            [
                'type' => 'checkbox',
                'id' => 'enable',
                'label' => __('Enable PayKeeper?')
            ],
            [
                'type'       => 'input',
                'id'         => 'name',
                'label'      => __('Custom Name'),
                'std'        => __("PayKeeper"),
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

    public function processFromBooking($data, $booking): Payment
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
        $payment->object_id = $booking->object_id;
        $payment->object_model = $booking->type;
        $payment->payment_url = $data['payment_url'];
        $payment->invoice_id = $data['invoice_id'];
        $payment->payment_gateway = $this->id;
        $payment->status = Payment::PROCESSING;
        $payment->amount = $data['amount'];
        $payment->attempts = 0;
        $payment->next_check_at = now()->addMinutes(1);
        $payment->last_checked_at = null;
        $payment->save();

        return $payment;
    }

    public function deleteInvoice(string $invoiceId): bool
    {
        $token = $this->getAccessToken();

        $data = array_merge(
            ['id' => $invoiceId, 'token' => $token]
        );

        $response = Http::withBasicAuth($this->getClientId(), $this->getClientSecret())
            ->asForm()
            ->post($this->getUrl('/change/invoice/revoke/'), $data);

        $json = $response->json();

        if (($json['result'] ?? null) === 'fail') {
            $messages = $this->parsePaykeeperError($json);
//            Log::error('PayKeeper error', $messages);
            throw new \Exception(implode(', ', $messages));
        }

        return ($json['result'] ?? null) === 'success';
    }

    public function handlePurchaseData($data, $booking): PaykeeperOrderDTO
    {
        $user = Auth::user();

        return new PaykeeperOrderDTO(
            payAmount: $data['amount'],
            clientId: $user->display_name ?? '',
            orderId: $booking->id,
            serviceName: $booking->service->title ?? '',
            clientEmail: $user->email ?? '',
            clientPhone: $user->phone ?? '',
            expiry: now()->addDays(config('paykeeper.pay_ttl_days'))->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public function createOrder(PaykeeperOrderDTO $dto)
    {
        $token = $this->getAccessToken();

        $data = array_merge(
            $dto->toArray(),
            ['token' => $token]
        );

        $response = Http::withBasicAuth($this->getClientId(), $this->getClientSecret())
            ->asForm()
            ->post($this->getUrl('change/invoice/preview/'), $data);

        $json = $response->json();

        if (($json['result'] ?? null) === 'fail') {
            $messages = $this->parsePaykeeperError($json);
//            Log::error('PayKeeper error', $messages);
            throw new \Exception(implode(', ', $messages));
        }

        return [
            'invoice_id'  => $json['invoice_id'],
            'invoice_url' => $json['invoice_url'],
        ];
    }

    public function getPayKeeperOrder(string $inventedId)
    {
        $response = Http::withBasicAuth(
            $this->getClientId(),
            $this->getClientSecret()
        )
            ->acceptJson()
            ->get($this->getUrl('info/invoice/byid/'), ['id' => $inventedId]);

        return $response->json();
    }

    public function sendCheckToEmail($inventedId)
    {
        $token = $this->getAccessToken();

        $data = array_merge(
            ['id' => $inventedId,
             'token' => $token]
        );

        $response = Http::withBasicAuth($this->getClientId(), $this->getClientSecret())
            ->asForm()
            ->post($this->getUrl('/change/invoice/send/'), $data);

        $json = $response->json();

        return [
            'status' => ($json['result'] ?? null) === 'success',
            'response' => $json,
        ];
    }

    /**
     * @throws \Exception
     */
    protected function getAccessToken()
    {
        // Проверяем кэш, если есть токен — возвращаем
        return Cache::remember(config('paykeeper.cache_key'), config('paykeeper.token_ttl'), function (){
            $response = Http::withBasicAuth($this->getClientId(), $this->getClientSecret())
                ->get($this->getBaseUrl() . '/info/settings/token/');

            $json = $response->json();

            if ($response->successful() && !empty($json['token'])) {
                return $json['token'];
            }

            if (($json['result'] ?? null) === 'fail') {
                $messages = $this->parsePaykeeperError($json);

                Log::error('PayKeeper error', $messages);
                throw new \Exception(implode(', ', $messages));
            }
        });
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

    public function getBaseUrl(): string
    {
        $url = $this->getOption('payment_url');

        if ($this->getOption('test')) {
            $url = $this->getOption('payment_url');
        }
        return $url;
    }
    public function getUrl($path): string
    {
        return $this->getBaseUrl(). $path;
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
    protected function parsePaykeeperError(array $response): array
    {
        $messages = [];

        if (!empty($response['msg'])) {
            $messages[] = $response['msg'];
        }

        if (!empty($response['errors']) && is_array($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $messages[] = $error;
            }
        }

        return $messages ?: ['PayKeeper error'];
    }
}
