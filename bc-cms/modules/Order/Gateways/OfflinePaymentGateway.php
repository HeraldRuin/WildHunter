<?php


namespace Modules\Order\Gateways;


use Modules\Order\Models\Order;
class OfflinePaymentGateway extends BaseGateway
{
    public $name = 'Offline Payment';

    /**
     *
     * @param Order $order
     * @return bool
     */
    public function process(Order $order)
    {
        $order->updateStatus(Order::PROCESSING);

        return true;
    }

    public function getOptionsConfigs()
    {
        return [
            [
                'type'  => 'checkbox',
                'id'    => 'enable',
                'label' => __('Enable Offline Payment?')
            ],
            [
                'type'  => 'input',
                'id'    => 'name',
                'label' => __('Custom Name'),
                'std' => "Offline"
            ],
            [
                'type'  => 'upload',
                'id'    => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type'  => 'editor',
                'id'    => 'html',
                'label' => __('Custom HTML Description')
            ],
        ];
    }
}

