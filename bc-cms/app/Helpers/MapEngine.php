<?php
namespace App\Helpers;
class MapEngine
{
    static protected $_init = false;
    public static function scripts($options = [])
    {
        $defer = isset($options['defer']) ? 'defer' : '';
        if(static::$_init) return;

        $html = '';
        $html .= sprintf("<script %s src='%s'></script>", $defer, url('module/core/js/map-engine.js?_ver='.config('app.version')));
        switch (setting_item('map_provider')) {
            case "gmap":
                $html .= sprintf("<script %s src='https://maps.googleapis.com/maps/api/js?key=%s&libraries=places&v=weekly&callback=BCInitMap&loading=async'></script>", $defer, setting_item('map_gmap_key'));
                $html .= sprintf("<script %s src='https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js'></script>", $defer);
                break;
            case "osm":
                $html .= sprintf("<script %s src='%s'></script>", $defer, url('libs/leaflet1.4.0/leaflet.js'));
                $html .= sprintf("<link rel='stylesheet' href='%s'>", url('libs/leaflet1.4.0/leaflet.css'));
                break;
            case "yandex":
                $html .= sprintf("<script %s src='https://api-maps.yandex.ru/2.1/?apikey=%s&lang=ru_RU'></script>",$defer, setting_item('map_yandex_key'));
                break;
        }

        static::$_init = true;

        return $html;
    }
}
