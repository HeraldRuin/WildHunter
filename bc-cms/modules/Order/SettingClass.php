<?php
namespace  Modules\Order;

use Modules\Core\Abstracts\BaseSettingsClass;
use Modules\Order\Hook;
use Modules\Order\Helpers\PaymentGatewayManager;

class SettingClass extends BaseSettingsClass
{
    public static function getSettingPages()
    {
        $configs = [
            'order' => [
                'id' => 'order',
                'title' => __("Order Settings"),
                'position' => 40,
                'view' => "Order::admin.settings.order",
                "keys" => [
                    'order_enable_recaptcha',
                    'order_term_conditions',
                    'logo_invoice_id',
                    'invoice_company_info',
                    'booking_guest_checkout',
                    'booking_why_book_with_us'
                ],
                'html_keys' => [
    
                ],
                'filter_demo_mode' => [
                    'order_term_conditions',
                    'invoice_company_info',
                ]
            ]
        ];

        $keys = [
            'currency_main',
            'currency_format',
            'currency_decimal',
            'currency_thousand',
            'currency_no_decimal',
            'extra_currency'
        ];

        $gateways = PaymentGatewayManager::all();
        foreach ($gateways as $k => $gateway) {
            $options = $gateway->getOptionsConfigs();
            if (!empty($options)) {
                foreach ($options as $option) {
                    if (empty($option['id'])) continue;
                    $keys[] = 'g_' . $k . '_' . $option['id'];
                    if (!empty($option['multi_lang']) && !empty($languages) && setting_item('site_enable_multi_lang') && setting_item('site_locale')) {
                        foreach ($languages as $language) {
                            if (setting_item('site_locale') == $language->locale) continue;
                            $keys[] = 'g_' . $k . '_' . $option['id'] . '_' . $language->locale;
                        }
                    }
                    if ($option['type'] == 'textarea' && $option['type'] == 'editor') {
                        $htmlKeys[] = 'g_' . $k . '_' . $option['id'];
                    }
                }
            }
        }
        $paymentSettings = [
            'id' => 'payment',
            'title' => __("Payment Settings"),
            'position' => 40,
            'view' => "Order::admin.settings.payment",
            "keys" => $keys,
            'html_keys' => [

            ],
            'filter_demo_mode' => [
            ]
        ];
        $configs['payment'] = $paymentSettings;
        
        return apply_filters(Hook::ORDER_SETTING_CONFIG, $configs);
    }
}
