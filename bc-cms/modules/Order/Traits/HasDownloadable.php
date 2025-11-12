<?php


namespace Modules\Order\Traits;


use Modules\Order\Models\Order;

trait HasDownloadable
{

    public static function searchDownloadable($filters)
    {
        $me = new self();

        $query = parent::query()->select($me->qualifyColumn('*'));
        $query->join('products',function($join) use ($me){
            return $join->on('products.id','=',$me->qualifyColumn('object_id'))->where('products.downloadable',1);
        });
        $query->join('product_downloadable',function($join) use ($filters){
            $join->on('products.id','=','product_downloadable.product_id');
        });

        if(!empty($filters['customer_id']))
        {
            $query->join('core_orders',function($join) use ($filters){
                $join->on('core_orders.id','=','core_order_items.order_id')->where('core_orders.customer_id',$filters['customer_id']);
            });
        }
        $query->whereIn($me->qualifyColumn('status'),[Order::PROCESSING,Order::COMPLETED]);

        return $query;
    }

    public function isValid(){

        $meta = $this->getMeta('download_expired_at');
        if(!$meta or !strtotime($meta) or strtotime($meta) > time()) return true;

        return false;
    }

    public function getExpiredAt(){
        return $this->getMeta('download_expired_at');
    }
}
