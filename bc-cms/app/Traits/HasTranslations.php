<?php
/**
 * Created by PhpStorm.
 * User: dunglinh
 * Date: 6/8/19
 * Time: 22:06
 */

namespace App\Traits;

trait HasTranslations
{

    /**
     * Class name for translation, default is current class
     * @var
     */
    protected $translation_class;

    /**
     * @param false $locale
     * @return boolean
     */
    public function saveTranslation($locale = false,$saveSEO = false){
        if(is_enable_multi_lang()){
            $translation = $this->translate($locale);

            // NOTE: With livewire, request()->input() has no data like normal form
            // So we need to get data from current model
            // we cant use  $this->attributes or $this->getAttributes() here 
            // cuz it will return raw value
            // But we need casted data so fillByAttr() will work

            $attributes = $this->toArray();
            $translation->fillByAttr($translation->fillable, $attributes);
            $translation->save();
            if($saveSEO && $translation->seo_type){
                $translation->saveSEO(request(),$locale);
            }
        }
        return true;
    }

    /**
     * Get Translated Model
     *
     * @param false $locale
     * @return $this
     */
    public function translate($locale = false){
        if(!is_enable_multi_lang()){
            return $this;
        }
        if(empty($locale)) $locale = app()->getLocale();

        $class = $this->getTranslationModelName();

        if($locale == app()->getLocale()){
            $find = $this->translation;
        }else {
            $find = $class::query()->where([
                'origin_id' => $this->getKey(),
                'locale' => $locale,
            ])->first();
        }

        if(!$find){
            $find = app()->make($class);
            $find->locale = $locale;
            $find->origin_id = $this->getKey();

            // Cant use fill() here cuz it wont work with cast keys
            foreach ($find->fillable as $key){
                $find[$key] = $this->getAttribute($key);
            }
        }

        return $find;
    }


    /**
     * @internal will change to private
     */
    public function getTranslationModelName(): string
    {
        $class = $this->translation_class;

        if(!$class and class_exists(get_class($this).'Translation')){
            $class = get_class($this).'Translation';
        }
        return $class;
    }

    public function translation(){
        return $this->hasOne($this->getTranslationModelName(),'origin_id')->where('locale',app()->getLocale());
    }

    /**
     * @todo Save Translation or Origin Language
     * @param bool $locale
     * @param bool $saveSeo
     * @return bool|null
     */
    public function saveOriginOrTranslation($locale = false,$saveSeo = true)
    {
        if(!$locale) $locale = get_main_lang();

        if(is_default_lang($locale)){
            // Main lang, we need to save origin table also
            $this->save();
        }

        if($saveSeo && $this->seo_type){
            $this->saveSEO(request(),is_default_lang($locale) ? null : $locale );
        }

        $res = $this->saveTranslation($locale,$saveSeo);
        return $res;
    }

}
