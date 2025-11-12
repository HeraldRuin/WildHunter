<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Model;

Trait HasMeta
{
    public function getMeta($key, $default = '',$multiple = false)
    {
        $query = $this->metaClass::query()->where([
            $this->meta_parent_key => $this->id,
            'name'       => $key
        ]);

        if($multiple){
            return $query->get();
        }
        $val = $query->first();
        if (!empty($val)) {
            return $val->val;
        }
        return $default;
    }

    public function getJsonMeta($key, $default = [])
    {
        $meta = $this->getMeta($key, $default);
        if(empty($meta)) return $default;
        return json_decode($meta, true);
    }

    public function addMeta($key, $val, $multiple = false)
    {
        if (is_object($val) or is_array($val))
            $val = json_encode($val);
        if (!$multiple) {
            $find = $this->metaClass::query()->where([
                $this->meta_parent_key => $this->id,
                'name'       => $key
            ])->first();
        }
        if(empty($find)){
            $find = new $this->metaClass([
                'name'       => $key,
            ]);
        }
        $find->setAttribute($this->meta_parent_key,$this->id);
        $find->val = $val;
        return $find->save();
    }

    public function deleteMeta($key){
        $this->metaClass::query()->where([
            $this->meta_parent_key => $this->id,
            'name'       => $key
        ])->delete();
    }
}
