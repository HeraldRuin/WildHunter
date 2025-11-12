<?php
namespace Modules\Booking\Traits;

trait HasDeposit
{
    public function isDepositEnable()
    {
        return (setting_item($this->type.'_deposit_enable') and setting_item($this->type.'_deposit_amount'));
    }
    public function getDepositAmount()
    {
        return setting_item($this->type.'_deposit_amount');
    }
    public function getDepositType()
    {
        return setting_item($this->type.'_deposit_type');
    }
    public function getDepositFomular()
    {
        return setting_item($this->type.'_deposit_fomular', 'default');
    }
}
