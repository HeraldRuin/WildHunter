<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Mechanisms\ComponentRegistry;
use Modules\Core\Models\Settings;
use App\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

//include '../../custom/Helpers/CustomHelper.php';

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);

function setting_item($item, $default = '', $isArray = false)
{

    $res = Settings::item($item, $default);

    if ($isArray and !is_array($res)) {
        $res = (array) json_decode($res, true);
    }

    return $res;
}
function setting_item_array($item, $default = '')
{
    return setting_item($item, $default, true);
}

function setting_item_with_lang($item, $locale = '', $default = '', $withOrigin = true, $forceLocale = false)
{
    if (empty($locale)) {
        $locale = app()->getLocale();
    }

    if (!$withOrigin && $locale == setting_item('site_locale')) {
        return $default;
    }

    if (!$forceLocale) {
        if (
            empty(setting_item('site_locale'))
            || empty(setting_item('site_enable_multi_lang'))
            || $locale == setting_item('site_locale')
        ) {
            $locale = '';
        }
    }
    return Settings::item(
        $item . ($locale ? '_' . $locale : ''),
        $withOrigin ? setting_item($item, $default) : $default
    );
}

function setting_item_with_lang_raw($item, $locale = '', $default = '')
{

    return setting_item_with_lang($item, $locale, $default, false);
}
function setting_update_item($item, $val)
{

    $s = Settings::where('name', $item)->first();
    if (empty($s)) {
        $s = new Settings();
        $s->name = $item;
    }

    if (is_array($val) or is_object($val)) $val = json_encode($val);
    $s->val = $val;

    $s->save();

    Cache::forget('setting_' . $item);

    return $s;
}

function app_get_locale($locale = false, $before = false, $after = false)
{
    if (setting_item('site_enable_multi_lang') and app()->getLocale() != setting_item('site_locale')) {
        return $locale ? $before . $locale . $after : $before . app()->getLocale() . $after;
    }
    return '';
}

function format_money($price)
{

    return Currency::format((float)$price);
}
function format_money_main($price)
{

    return Currency::format((float)$price, true);
}

function currency_symbol()
{

    $currency_main = get_current_currency('currency_main');

    $currency = Currency::getCurrency($currency_main);

    return $currency['symbol'] ?? '';
}

function generate_menu($location = '', $options = [])
{
    $options['walker'] = $options['walker'] ?? '\\Modules\\Core\\Walkers\\MenuWalker';

    $setting = json_decode(setting_item('menu_locations'), true);

    if (!empty($setting)) {
        foreach ($setting as $l => $menuId) {
            if ($l == $location and $menuId) {
                $menu = (new \Modules\Core\Models\Menu())->findById($menuId);
                if ($menu) {

                    $translation = $menu->translate();

                    $walker = new $options['walker']($translation);

                    if (!empty($translation)) {
                        $walker->generate($options);
                    }
                }
            }
        }
    }
}

function set_active_menu($item)
{
    \Modules\Core\Walkers\MenuWalker::setCurrentMenuItem($item);
}

function get_exceprt($string, $length = 200, $more = "[...]")
{
    $string = strip_tags($string);
    if (str_word_count($string) > 0) {
        $arr = explode(' ', $string);
        $excerpt = '';
        if (count($arr) > 0) {
            $count = 0;
            if ($arr) foreach ($arr as $str) {
                $count += strlen($str);
                if ($count > $length) {
                    $excerpt .= $more;
                    break;
                }
                $excerpt .= ' ' . $str;
            }
        }
        return $excerpt;
    }
}

function get_file_url($file_id, $size = "thumb", $resize = true)
{
    if (empty($file_id)) return null;
    return \Modules\Media\Helpers\FileHelper::url($file_id, $size, $resize);
}

function get_image_tag($image_id, $size = 'thumb', $options = [])
{
    $options = array_merge([
        'lazy' => true
    ], $options);
    $url = get_file_url($image_id, $size);

    if ($url) {
        $alt = $options['alt'] ?? '';
        $attr = '';
        $class = $options['class'] ?? '';
        if (!empty($options['lazy'])) {
            $class .= ' lazy';
            $attr .= " data-src=" . e($url) . " ";
        } else {
            $attr .= " src='" . e($url) . "' ";
        }
        return sprintf("<img class='%s' %s alt='%s'>", e($class), $attr, e($alt));
    }
    return '';
}
function get_date_format()
{
    return setting_item('date_format', 'd.m.Y');
}
function get_moment_date_format()
{
    return php_to_moment_format(get_date_format());
}
function php_to_moment_format($format)
{

    $replacements = [
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => '', // no equivalent
        'L' => '', // no equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => '', // no equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => 'zz', // deprecated since version 1.6.0 of moment.js
        'I' => '', // no equivalent
        'O' => '', // no equivalent
        'P' => '', // no equivalent
        'T' => '', // no equivalent
        'Z' => '', // no equivalent
        'c' => '', // no equivalent
        'r' => '', // no equivalent
        'U' => 'X',
    ];
    $momentFormat = strtr($format, $replacements);
    return $momentFormat;
}

function display_date($time)
{

    if ($time) {
        if (is_string($time)) {
            $time = strtotime($time);
        }

        if (is_object($time)) {
            return $time->format(get_date_format());
        }
    } else {
        $time = strtotime(today());
    }

    return date(get_date_format(), $time);
}

function display_datetime($time)
{

    if (!$time) return null;

    if (is_string($time)) {
        $time = strtotime($time);
    }

    if (is_object($time)) {
        return $time->format(get_date_format() . ' H:i');
    }

    return date(get_date_format() . ' H:i', $time);
}

function human_time_diff($from, $to = false)
{

    if (is_string($from)) $from = strtotime($from);
    if (is_string($to)) $to = strtotime($to);

    if (empty($to)) {
        $to = time();
    }

    $diff = (int) abs($to - $from);

    if ($diff < HOUR_IN_SECONDS) {
        $mins = round($diff / MINUTE_IN_SECONDS);
        if ($mins <= 1) {
            $mins = 1;
        }
        /* translators: Time difference between two dates, in minutes (min=minute). %s: Number of minutes */
        if ($mins) {
            $since = __(':num mins', ['num' => $mins]);
        } else {
            $since = __(':num min', ['num' => $mins]);
        }
    } elseif ($diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS) {
        $hours = round($diff / HOUR_IN_SECONDS);
        if ($hours <= 1) {
            $hours = 1;
        }
        /* translators: Time difference between two dates, in hours. %s: Number of hours */
        if ($hours) {
            $since = __(':num hours', ['num' => $hours]);
        } else {
            $since = __(':num hour', ['num' => $hours]);
        }
    } elseif ($diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS) {
        $days = round($diff / DAY_IN_SECONDS);
        if ($days <= 1) {
            $days = 1;
        }
        /* translators: Time difference between two dates, in days. %s: Number of days */
        if ($days) {
            $since = __(':num days', ['num' => $days]);
        } else {
            $since = __(':num day', ['num' => $days]);
        }
    } elseif ($diff < MONTH_IN_SECONDS && $diff >= WEEK_IN_SECONDS) {
        $weeks = round($diff / WEEK_IN_SECONDS);
        if ($weeks <= 1) {
            $weeks = 1;
        }
        /* translators: Time difference between two dates, in weeks. %s: Number of weeks */
        if ($weeks) {
            $since = __(':num weeks', ['num' => $weeks]);
        } else {
            $since = __(':num week', ['num' => $weeks]);
        }
    } elseif ($diff < YEAR_IN_SECONDS && $diff >= MONTH_IN_SECONDS) {
        $months = round($diff / MONTH_IN_SECONDS);
        if ($months <= 1) {
            $months = 1;
        }
        /* translators: Time difference between two dates, in months. %s: Number of months */

        if ($months) {
            $since = __(':num months', ['num' => $months]);
        } else {
            $since = __(':num month', ['num' => $months]);
        }
    } elseif ($diff >= YEAR_IN_SECONDS) {
        $years = round($diff / YEAR_IN_SECONDS);
        if ($years <= 1) {
            $years = 1;
        }
        /* translators: Time difference between two dates, in years. %s: Number of years */
        if ($years) {
            $since = __(':num years', ['num' => $years]);
        } else {
            $since = __(':num year', ['num' => $years]);
        }
    }

    return $since;
}

function human_time_diff_short($from, $to = false)
{
    if (!$to) $to = time();
    $today = strtotime(date('Y-m-d 00:00:00', $to));

    $diff = $from - $to;

    if ($from > $today) {
        return date('h:i A', $from);
    }

    if ($diff < 5 * DAY_IN_SECONDS) {
        return date('D', $from);
    }

    return date('M d', $from);
}

function _n($l, $m, $count)
{
    if ($count) {
        return $m;
    }
    return $l;
}
function get_country_lists()
{
    return \App\Country::list();
}

function get_country_name($name)
{
    $name = strtoupper($name);
    $all = get_country_lists();

    return $all[$name] ?? $name;
}

function get_page_url($page_id)
{
    if (empty($page_id)) return null;

    $page = \Modules\Page\Models\Page::find($page_id);

    if ($page) {
        return $page->getDetailUrl();
    }
    return false;
}

function get_payment_gateway_obj($payment_gateway)
{

    $gateways = get_payment_gateways();

    if(is_object($gateways[$payment_gateway])){
        return $gateways[$payment_gateway]; //  support instanceof
    }

    if (empty($gateways[$payment_gateway]) or !class_exists($gateways[$payment_gateway])) {
        return false;
    }

    $gatewayObj = new $gateways[$payment_gateway]($payment_gateway);

    return $gatewayObj;
}

function recaptcha_field($action)
{
    return \App\Helpers\ReCaptchaEngine::captcha($action);
}

function add_query_arg($args, $uri = false)
{

    if (empty($uri)) $uri = request()->url();

    $query = request()->query();

    if (!empty($args)) {
        foreach ($args as $k => $arg) {
            $query[$k] = $arg;
        }
    }

    return $uri . '?' . http_build_query($query);
}

function is_default_lang($lang = '')
{
    if (!$lang) $lang = request()->query('lang');
    if (!$lang) $lang = request()->route('lang');

    if (empty($lang) or $lang == setting_item('site_locale')) return true;

    return false;
}

function get_lang_switcher_url($locale = false)
{

    $request =  request();
    $data = $request->query();
    $data['set_lang'] = $locale;

    $url = url()->current();

    $url .= '?' . http_build_query($data);

    return url($url);
}
function get_currency_switcher_url($code = false)
{

    $request =  request();
    $data = $request->query();
    $data['set_currency'] = $code;

    $url = url()->current();

    $url .= '?' . http_build_query($data);

    return url($url);
}


function translate_or_origin($key, $settings = [], $locale = '')
{
    if (empty($locale)) $locale = request()->query('lang');

    if ($locale and $locale == setting_item('site_locale')) $locale = false;

    if (empty($locale)) return $settings[$key] ?? '';
    else {
        return $settings[$key . '_' . $locale] ?? '';
    }
}

function get_services(){

    $all = [];

    // Modules
    $custom_modules = \Modules\ServiceProvider::getActivatedModules();
    if (!empty($custom_modules)) {
        foreach ($custom_modules as $moduleData) {
            $moduleClass = $moduleData['class'];
            if (class_exists($moduleClass) and method_exists($moduleClass, 'getServices')) {
                $services = call_user_func([$moduleClass, 'getServices']);
                $all = array_merge($all, $services);
            }
        }
    }


    // Plugin Menu
    $plugins_modules = \Plugins\ServiceProvider::getModules();
    if (!empty($plugins_modules)) {
        foreach ($plugins_modules as $module) {
            $moduleClass = "\\Plugins\\" . ucfirst($module) . "\\ModuleProvider";
            if (class_exists($moduleClass) and method_exists($moduleClass, 'getServices')) {
                $services = call_user_func([$moduleClass, 'getServices']);
                $all = array_merge($all, $services);
            }
        }
    }
    foreach ($all as $id => $class) {
        $all[$id] = get_class(app()->make($class));
    }
    return $all;
}

function get_bookable_services()
{

    $all = [];

    // Modules
    $custom_modules = \Modules\ServiceProvider::getActivatedModules();
    if (!empty($custom_modules)) {
        foreach ($custom_modules as $moduleData) {
            $moduleClass = $moduleData['class'];
            if (class_exists($moduleClass)) {
                $services = call_user_func([$moduleClass, 'getBookableServices']);
                $all = array_merge($all, $services);
            }
        }
    }


    // Plugin Menu
    $plugins_modules = \Plugins\ServiceProvider::getModules();
    if (!empty($plugins_modules)) {
        foreach ($plugins_modules as $module) {
            $moduleClass = "\\Plugins\\" . ucfirst($module) . "\\ModuleProvider";
            if (class_exists($moduleClass)) {
                $services = call_user_func([$moduleClass, 'getBookableServices']);
                $all = array_merge($all, $services);
            }
        }
    }
    foreach ($all as $id => $class) {
        $all[$id] = get_class(app()->make($class));
    }
    return $all;
}
function get_payable_services()
{
    $all = get_bookable_services();

    // Modules
    $custom_modules = \Modules\ServiceProvider::getActivatedModules();
    if (!empty($custom_modules)) {
        foreach ($custom_modules as $moduleData) {
            $moduleClass = $moduleData['class'];
            if (class_exists($moduleClass)) {
                $services = call_user_func([$moduleClass, 'getPayableServices']);
                $all = array_merge($all, $services);
            }
        }
    }

    foreach ($all as $id => $class) {
        $all[$id] = get_class(app()->make($class));
    }

    return $all;
}
function get_reviewable_services()
{

    $all = get_services();

    $all = array_merge($all, get_bookable_services());

    // Modules
    $custom_modules = \Modules\ServiceProvider::getActivatedModules();
    if (!empty($custom_modules)) {
        foreach ($custom_modules as $moduleData) {
            $moduleClass = $moduleData['class'];
            if (class_exists($moduleClass)) {
                $services = call_user_func([$moduleClass, 'getReviewableServices']);
                $all = array_merge($all, $services);
            }
        }
    }

    foreach ($all as $id => $class) {
        $all[$id] = get_class(app()->make($class));
    }

    return $all;
}
function get_bookable_service_by_id($id)
{

    $all = get_bookable_services();

    return $all[$id] ?? null;
}

function file_get_contents_curl($url, $isPost = false, $data = [])
{

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    if ($isPost) {
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function size_unit_format($number = '')
{
    switch (setting_item('size_unit')) {
        case "m2":
            return $number . " m<sup>2</sup>";
            break;
        default:
            return $number . " " . __('sqft');
            break;
    }
}

function get_available_gateways()
{
    $all = get_payment_gateways();
    $res = [];
    foreach ($all as $k => $obj) {
        if ($obj->isAvailable()) {
            $res[$k] = $obj;
        }
    }
    return $res;
}
function get_current_currency($need, $default = '')
{
    return Currency::getCurrent($need, $default);
}

/**
 * @deprecated use status_to_text instead
 */
function booking_status_to_text($status)
{
    return status_to_text($status);
}

function status_to_text($status)
{
    switch ($status){
        case "draft":
            return __('Draft');
        case "on_hold":
            return __('On-hold');
            break;
        case "unpaid":
            return __('Unpaid');
            break;
        case "paid":
            return __('Paid');
            break;
        case "processing":
            return __('Processing');
            break;
        case "completed":
            return __('Completed');
            break;
        case "confirmed":
            return __('Confirmed');
            break;
        case "cancelled":
            return __('Cancelled');
            break;
        case "cancel":
            return __('Cancel');
            break;
        case "pending":
            return __('Pending');
            break;
        case "partial_payment":
            return __('Partial Payment');
            break;
        case "collection":
            return __('Collection');
            break;
        case "finished_collection":
            return __('Finished collection');
            break;
        case "invitation":
            return __('Invitation');
            break;
        case "fail":
        case "failed":
            return __('Failed');
            break;
        case "rejected":
            return __('Rejected');
            break;
        case "refunded":
            return __('Refunded');
            break;
        default:
            return ucfirst($status ?? '');
            break;
    }
}
function verify_type_to($type, $need = 'name')
{
    switch ($type) {
        case "phone":
            return __("Phone");
            break;
        case "number":
            return __("Number");
            break;
        case "email":
            return __("Email");
            break;
        case "file":
            return __("Attachment");
            break;
        case "multi_files":
            return __("Multi Attachments");
            break;
        case "text":
        default:
            return __("Text");
            break;
    }
}

function get_all_verify_fields()
{
    return setting_item_array('role_verify_fields');
}
/*Hook Functions*/
function add_action($hook, $callback, $priority = 20, $arguments = 1)
{
    return \Modules\Core\Facades\Hook::addAction($hook, $callback, $priority, $arguments);
}
function add_filter($hook, $callback, $priority = 20, $arguments = 1)
{
    return \Modules\Core\Facades\Hook::addFilter($hook, $callback, $priority, $arguments);
}
function do_action()
{
    return \Modules\Core\Facades\Hook::action(...func_get_args());
}
function apply_filters()
{
    return \Modules\Core\Facades\Hook::filter(...func_get_args());
}
function is_installed()
{
    return file_exists(storage_path('installed'));
}
function is_enable_multi_lang()
{
    return (bool) setting_item('site_enable_multi_lang');
}

function is_enable_language_route()
{
    return (is_installed() and is_enable_multi_lang() and app()->getLocale() != setting_item('site_locale'));
}

/**
 * Format minute to time string, support minute with decimal eg: 1.5 -> 1:30
 * @param float $minute
 * @return string
 */

function minute_format($minute)
{
    // extract minute and second
    $minute = floor($minute);
    $second = round(($minute - floor($minute)) * 60);

    $time = Carbon::createFromTime(0, $minute, $second);
    if ($time->hour > 0) {
        return $time->format('H:i:s');
    }
    return $time->format('i:s');
}

function duration_format($hour, $is_full = false, $format = 'H:i')
{
    $day = floor($hour / 24);
    $hour = $hour % 24;
    $tmp = '';

    if ($day) $tmp = $day . __('D');

    if ($hour)
        $tmp .= $hour . __('H');

    if ($is_full) {
        $tmp = [];
        if ($day) {
            if ($day > 1) {
                $tmp[] = __(':count Days', ['count' => $day]);
            } else {
                $tmp[] = __(':count Day', ['count' => $day]);
            }
        }
        if ($hour) {
            if ($hour > 1) {
                $tmp[] = __(':count Hours', ['count' => $hour]);
            } else {
                $tmp[] = __(':count Hour', ['count' => $hour]);
            }
        }

        $tmp = implode(' ', $tmp);
    }

    return $tmp;
}
function is_enable_guest_checkout()
{
    return setting_item('booking_guest_checkout');
}

function handleVideoUrl($string, $video_id = false)
{
    if ($video_id && !empty($string)) {
        parse_str(parse_url($string, PHP_URL_QUERY), $values);
        return $values['v'];
    }
    if (strpos($string, 'youtu') !== false) {
        preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $string, $matches);
        if (!empty($matches[0])) return "https://www.youtube.com/embed/" . e($matches[0]);
    }
    return $string;
}

function is_api()
{
    return request()->segment(1) == 'api';
}

function is_demo_mode()
{
    return env('DEMO_MODE', false);
}
function credit_to_money($amount)
{
    return $amount * setting_item('wallet_credit_exchange_rate', 1);
}

function money_to_credit($amount, $roundUp = false)
{
    $res = $amount / setting_item('wallet_credit_exchange_rate', 1);

    if ($roundUp) return ceil($res);

    return $res;
}

function clean_by_key($object, $keyIndex, $children = 'children')
{
    if (is_string($object)) {
        return clean($object);
    }

    if (is_array($object)) {
        if (isset($object[$keyIndex])) {
            $newClean = clean($object[$keyIndex]);
            $object[$keyIndex] =  $newClean;
            if (!empty($object[$children])) {
                $object[$children] = clean_by_key($object[$children], $keyIndex);
            }
        } else {
            foreach ($object as $key => $oneObject) {
                if (isset($oneObject[$keyIndex])) {
                    $newClean = clean($oneObject[$keyIndex]);
                    $object[$key][$keyIndex] =  $newClean;
                }

                if (!empty($oneObject[$children])) {
                    $object[$key][$children] = clean_by_key($oneObject[$children], $keyIndex);
                }
            }
        }

        return $object;
    }
    return $object;
}
function periodDate($startDate, $endDate, $day = true, $interval = '1 day')
{
    $begin = new \DateTime($startDate);
    $end = new \DateTime($endDate);
    if ($day) {
        $end = $end->modify('+1 day');
    }
    $interval = \DateInterval::createFromDateString($interval);
    $period = new \DatePeriod($begin, $interval, $end);
    return $period;
}

function _fixTextScanTranslations()
{
    return __("Show on the map");
}


function is_admin()
{
    if (!auth()->check()) return false;
    if (auth()->user()->hasPermission('dashboard_access')) return true;
    return false;
}
function is_vendor()
{
    if (!auth()->check()) return false;
    if (auth()->user()->hasPermission('hunter_dashboard_access')) return true;
    return false;
}
function is_baseAdmin()
{
    if (!auth()->check()) return false;
    if (auth()->user()->hasPermission('baseAdmin_dashboard_access')) return true;
    return false;
}

function get_link_detail_services($services, $id, $action = 'edit')
{
    if (\Route::has($services . '.admin.' . $action)) {
        return route($services . '.admin.' . $action, ['id' => $id]);
    } else {
        return '#';
    }
}

function get_link_vendor_detail_services($services, $id, $action = 'edit')
{
    if (\Route::has($services . '.vendor.' . $action)) {
        return route($services . '.vendor.' . $action, ['id' => $id]);
    } else {
        return '#';
    }
}

function format_interval($d1, $d2 = '')
{
    $first_date = new DateTime($d1);
    if (!empty($d2)) {
        $second_date = new DateTime($d2);
    } else {
        $second_date = new DateTime();
    }


    $interval = $first_date->diff($second_date);

    $result = "";
    if ($interval->y) {
        $result .= $interval->format("%y years ");
    }
    if ($interval->m) {
        $result .= $interval->format("%m months ");
    }
    if ($interval->d) {
        $result .= $interval->format("%d days ");
    }
    if ($interval->h) {
        $result .= $interval->format("%h hours ");
    }
    if ($interval->i) {
        $result .= $interval->format("%i minutes ");
    }
    if ($interval->s) {
        $result .= $interval->format("%s seconds ");
    }

    return $result;
}
function generate_timezone_list()
{
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    );

    $timezones = array();
    foreach ($regions as $region) {
        $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
    }

    $timezone_offsets = array();
    foreach ($timezones as $timezone) {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    // sort timezone by offset
    asort($timezone_offsets);

    $timezone_list = array();
    foreach ($timezone_offsets as $timezone => $offset) {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));

        $pretty_offset = "UTC{$offset_prefix}{$offset_formatted}";

        $timezone_list[$timezone] = "$timezone ({$pretty_offset})";
    }

    return $timezone_list;
}

function is_string_match($string, $wildcard)
{
    $pattern = preg_quote($wildcard, '/');
    $pattern = str_replace('\*', '.*', $pattern);
    return preg_match('/^' . $pattern . '$/i', $string);
}
function getNotify()
{
    $checkNotify = \Modules\Core\Models\NotificationPush::query();
    if (is_admin()) {
        $checkNotify->where(function ($query) {
            $query->where('for_admin', 1);
            $query->orWhere('notifiable_id', Auth::id());
        });
    } else {
        $checkNotify->where('for_admin', 0);
        $checkNotify->where('notifiable_id', Auth::id());
    }
    $notifications = $checkNotify->orderBy('created_at', 'desc')->limit(5)->get();
    $countUnread = $checkNotify->where('read_at', null)->count();
    return [$notifications, $countUnread];
}

function is_enable_registration()
{
    return !setting_item('user_disable_register');
}
function is_enable_vendor_team()
{
    return false;
    return setting_item('vendor_team_enable');
}

function is_enable_plan()
{
    return setting_item('user_plans_enable') == true;
}

function get_main_lang()
{
    return setting_item('site_locale');
}

function is_compatible($current, $list_to_check)
{
    if (in_array($current, $list_to_check)) return true;
    foreach ($list_to_check as $v) {
        if (version_compare($current, $v) >= 0) return true;
    }
    return false;
}


function is_mobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"] ?? '');
}


function is_agency_owner($id = false)
{

    if (!$id) $id = auth()->id();

    return \Modules\Agency\Models\Agency::query()->where('id', $id)->count();
}

/**
 * Generate inline style from Block data
 *
 * @param $model
 * @return string
 */
function generate_css($model)
{
    $inline_css = '';

    // Make sure data is in correct format
    if (!is_array($model['padding'])) $model['padding'] = [];
    if (!is_array($model['margin'])) $model['margin'] = [];


    //Padding
    if (isset($model['padding']['t']) && $model['padding']['t'] != '') $inline_css .= "padding-top: " . $model['padding']['t'] . "px;";
    if (isset($model['padding']['b']) && $model['padding']['b'] != '') $inline_css .= "padding-bottom: " . $model['padding']['b'] . "px;";
    if (isset($model['padding']['l']) && $model['padding']['l'] != '') $inline_css .= "padding-left: " . $model['padding']['l'] . "px;";
    if (isset($model['padding']['r']) && $model['padding']['r'] != '') $inline_css .= "padding-right: " . $model['padding']['r'] . "px;";
    //Margin
    if (isset($model['margin']['t']) && $model['margin']['t'] != '') $inline_css .= "margin-top: " . $model['margin']['t'] . "px;";
    if (isset($model['margin']['b']) && $model['margin']['b'] != '') $inline_css .= "margin-bottom: " . $model['margin']['b'] . "px;";
    if (isset($model['margin']['l']) && $model['margin']['l'] != '') $inline_css .= "margin-left: " . $model['margin']['l'] . "px;";
    if (isset($model['margin']['r']) && $model['margin']['r'] != '') $inline_css .= "margin-right: " . $model['margin']['r'] . "px;";
    //Background
    if (!empty($model['background'])) $inline_css .= "background: " . $model['background'] . ";";

    return $inline_css;
}

function get_pro_modules()
{
    if (file_exists(base_path('/pro/ServiceProvider.php')) and is_callable(['\\Pro\\ServiceProvider', 'getModules'])) {
        return call_user_func(['\\Pro\\ServiceProvider', 'getModules']);
    }
    return [];
}

function get_payment_gateways(){
    return \Modules\Booking\Helpers\PaymentGatewayManager::all();
}

function get_active_payment_gateways(){
    return \Modules\Booking\Helpers\PaymentGatewayManager::available();
}


function format_percent($number){
    return number_format($number, 0).'%';
}

function is_vendor_enable(){
    return setting_item('vendor_enable') == 1;
}

if (!function_exists('componentExists')) {
    function componentExists(string $name): bool
    {
        try {
            return app(ComponentRegistry::class)->getName($name);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

/**
 * Check if file is image
 */
function is_image_data($data){
    if(empty($data)) return false;
    $checkMimeType = strpos($data['file_type'], 'image/') !== false;
    $checkExtension = in_array($data['file_extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    return $checkMimeType && $checkExtension;
}

function get_user_hotel_id() {
    $authUser = Auth::user();

    if ($authUser && $authUser->hotels->isNotEmpty()) {
        $hotel = $authUser->hotels
            ->where('status', '!=', 'draft')
            ->first();

        return $hotel?->id;
    }

    return null;
}




