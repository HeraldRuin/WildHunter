<?php

namespace Modules\Order\Gateways;

use Illuminate\Http\Request;
use Modules\Order\Models\Order;
use Modules\Order\Models\Payment;

abstract class BaseGateway
{
    protected $id;
    public $name;
    public $is_offline = false;

    public function __construct($id = false)
    {
        if ($id)
            $this->id = $id;
    }

    public function isAvailable()
    {
        return $this->getOption('enable');
    }

    public function getHtml()
    {

    }


    public function cancelPayment(Request $request)
    {

    }

    public function confirmPayment(Request $request)
    {

    }

    public function callbackPayment(Request $request)
    {

    }

    public function getHtmlPayPage(Order $order)
    {

    }

    public function getOptionsConfigs()
    {
        return [];
    }

    public function getOptionsConfigsFormatted()
    {
        $languages = \Modules\Language\Models\Language::getActive();
        $options = $this->getOptionsConfigs();
        if (!empty($options)) {
            foreach ($options as &$option) {
                if (empty($option['readonly'])) {
                    $option['value'] = $this->getOption($option['id'], $option['std'] ?? '');
                }
                if (!empty($option['multi_lang']) && !empty($languages) && setting_item('site_enable_multi_lang') && setting_item('site_locale')) {
                    foreach ($languages as $language) {
                        if (setting_item('site_locale') == $language->locale) continue;
                        $option["value_" . $language->locale] = $this->getOption($option['id'] . "_" . $language->locale, '');
                    }
                }

                if (!empty($option['id'])) {
                    $option['id'] = 'g_' . $this->id . '_' . $option['id'];
                }
            }
        }
        return $options;
    }

    public function getOption($key, $default = '')
    {
        return setting_item('g_' . $this->id . '_' . $key, $default);
    }

    public function getDisplayName()
    {
        $locale = app()->getLocale();
        if (!empty($locale)) {
            $title = $this->getOption("name_" . $locale);
        }
        if (empty($title)) {
            $title = $this->getOption("name", $this->name);
        }
        return $title;
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

    public function getReturnUrl($is_normal = false)
    {
        if ($is_normal) {
            return route('gateway.confirm', ['gateway' => $this->id]);
        }
        $is_api = request()->segment(1) == 'api';
        return url(($is_api ? 'api/' : '') . app_get_locale(false, false, "/") . config('order.order_route_prefix') . '/confirm/' . $this->id);
    }

    public function getCancelUrl($is_normal = false)
    {
        if ($is_normal) {
            return route('gateway.cancel', ['gateway' => $this->id]);
        }
        $is_api = request()->segment(1) == 'api';
        return url(($is_api ? 'api/' : '') . app_get_locale(false, false, "/") . config('order.order_route_prefix') . '/cancel/' . $this->id);
    }

    public function getWebhookUrl()
    {
        return route('order.gateway.webhook', ['gateway' => $this->id]);
    }

    public function getDisplayLogo()
    {
        $logo_id = $this->getOption('logo_id');
        if (!$logo_id) return false;
        return get_file_url($logo_id);
    }

    public function getForm()
    {

    }

    public function getApiOptions()
    {

    }

    public function getApiDisplayHtml()
    {
        return $this->getDisplayHtml();
    }


    public function getValidationRules()
    {
        return [];
    }

    public function getValidationMessages()
    {
        return [];
    }



    // Start a draft payment for an order and save current payment id to order
    public function startPayment(Order $order): Payment
    {
        $payment = new Payment();
        $payment->gateway = $this->id;
        $payment->object_id = $order->id;
        $payment->object_model = $order->type;
        $payment->status = Order::DRAFT;

        $payment->amount = (float)$order->total;
        $payment->currency = $order->currency;

        $payment->save();


        $order->payment_id = $payment->id;
        $order->payment_status = $payment->status;
        $order->save();


        // set relation so it wont query order again
        $payment->setRelation('order', $order);

        return $payment;
    }


    public function getId()
    {
        return $this->id;
    }
}
