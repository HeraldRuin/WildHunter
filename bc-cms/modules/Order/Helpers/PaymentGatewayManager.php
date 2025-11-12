<?php


namespace Modules\Order\Helpers;


class PaymentGatewayManager
{
    protected static $_all = [];

    protected static $_cached = [];

    public static function register($id,$class,$priority = 1){
        if(isset(static::$_all[$id]) and (static::$_all[$id]['priority'] ?? 1) > $priority) return;
        static::$_all[$id] = [
            'class'=>$class,
            'priority'=>$priority
        ];
    }

    public static function all(){
        if(!empty(static::$_cached)){
            return static::$_cached;
        }

        $all = [];
        foreach (static::$_all as $id=>$config){
            $all[$id] = new $config['class']($id);
        }

        static::$_cached = \Illuminate\Support\Arr::sort($all, function ($value) {
            return $value->position ?? 0;
        });

        return static::$_cached;
    }
    public static function available(){
        $all = static::all();
        foreach ($all as $k=>$item){
            if(!$item->isAvailable()){
                unset($all[$k]);
            }
        }
        return $all;
    }
}
