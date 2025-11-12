<?php


namespace App\Traits;

trait HasAddress
{

    public function billing_address(){
        return $this->getMeta('billing_address');
    }

    public function billing_addresses(){

        // TODO: support multiple addresses
        return [];
    }

    public function shipping_address(){
        return $this->getMeta('shipping_address');
    }

    public function shipping_addresses(){

        // TODO: support multiple addresses
        return [];
    }

    public function saveAddress($data,$type = 'billing'){
        $this->addMeta($type.'_address',$data);

        // TODO: support multiple addresses
    }

    public function getDefaultAddress(){
        return [
            'email'=>$this->email,
            'first_name'=>$this->first_name,
            'last_name'=>$this->last_name,
            'phone'=>$this->phone,
            'country'=>$this->country,
            'address'=>$this->address,
            'address_2'=>$this->address_2,
            'state_code'=>$this->state_code,
            'state_name'=>$this->state_name,
            'city'=>$this->city,
            'zip'=>$this->zip,
            'company'=>$this->company,
        ];
    }
}
