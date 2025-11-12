<?php


namespace Modules\Order\Rules;

use App\Helpers\ReCaptchaEngine;
use Illuminate\Contracts\Validation\Rule;
use Modules\Product\Models\Product;

class ValidOrderItems implements Rule
{
    protected $messages = [];
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(!is_array($value))
        {
            $this->messages[] = __('Please select items');
            return false;
        }
        foreach ($value as $k=>$item){
            if(empty($item['product_id'])){
                continue;
            }
            $product = Product::find($item['product_id']);
            if(!$product){
                $this->messages[] = __('Please select product for item :number',['number'=>$k + 1]);
                continue;
            }
            if($product->product_type == 'variable' and  empty($item['variation_id'])){
                $this->messages[] = __('Please select variation for item :number',['number'=>$k + 1]);
                continue;
            }
            try{
                $product->addToCartValidate($item['qty'],$item['variation_id']);
            }catch (\Exception $exception){

                $this->messages[] = $product->title.': '.$exception->getMessage();
                continue;
            }

        }
        if(!empty($this->messages)) return false;
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return implode('<br>',$this->messages);
    }
}
