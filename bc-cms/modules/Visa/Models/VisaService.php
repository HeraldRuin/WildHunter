<?php
namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatus;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Traits\CapturesService;
use App\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class VisaService extends Bookable
{
    use SoftDeletes;
    use HasStatus;
    use CapturesService;

    protected $table = 'bc_visa_services';

    public $type = 'visa';
    protected $seo_type = 'visa';
    protected $slugField     = 'slug';
    protected $slugFromField = 'title';
    public $hasLocationFeature = false; // That mean this is not belong to a location
    protected $translation_class = VisaServiceTranslation::class;
    public $checkout_booking_detail_file       = 'Visa::frontend/booking/detail';
    public $checkout_booking_detail_modal_file = 'Visa::frontend/booking/detail-modal';
    public $email_new_booking_file             = 'Visa::emails.new_booking_detail';
    public $booking_passengers_info_file       = 'Visa::frontend/booking/passengers-info';

    public function visaType()
    {
        return $this->belongsTo(VisaType::class, 'type_id');
    }

    public function search($data)
    {
        $query = $this->query();
        if(!empty($data['s'])){
            $query->where('title', 'like', '%'.$data['s'].'%')
            ->orWhere('code', 'like', '%'.$data['s'].'%')
            ->orWhere('to_country', 'like', '%'.$data['s'].'%');
        }
        if(!empty($data['to_country'])){
            $query->where('to_country', $data['to_country']);
        }
        if(!empty($data['visa_type'])){
            $query->where('type_id', $data['visa_type']);
        }

        if(!empty($data['price_range'])){
            $pri_from = Currency::convertPriceToMain(explode(";", $data['price_range'])[0]);
            $pri_to =  Currency::convertPriceToMain(explode(";", $data['price_range'])[1]);
            $query->whereBetween('price', [$pri_from, $pri_to]);
        }

        // review score
        if(!empty($data['review_score']) and is_array($data['review_score'])){
            $this->filterReviewScore($query, $data['review_score']);
        }

        // Order by
        $orderby = $data['orderby'] ?? "";
        switch ($orderby) {
            case "price_low_high":
                $query->orderBy($query->qualifyColumn("price"), "asc");
                break;
            case "price_high_low":
                $query->orderBy($query->qualifyColumn("price"), "desc");
                break;
            case "rate_high_low":
                $query->orderBy($query->qualifyColumn("review_score"), "desc");
                break;
            default:
                $query->orderBy($query->qualifyColumn("id"), "desc");
                break;
        }

        return $query->where('status', 'publish');
    }

    public static function getSeoMetaForPageList()
    {
        $meta['seo_title'] = __("Search for Visa");
        if (!empty($title = setting_item_with_lang("visa_page_list_seo_title", false))) {
            $meta['seo_title'] = $title;
        } else if (!empty($title = setting_item_with_lang("visa_page_search_title"))) {
            $meta['seo_title'] = $title;
        }
        $meta['seo_image'] = null;
        if (!empty($title = setting_item("visa_page_list_seo_image"))) {
            $meta['seo_image'] = $title;
        } else if (!empty($title = setting_item("visa_page_search_banner"))) {
            $meta['seo_image'] = $title;
        }
        $meta['seo_desc'] = setting_item_with_lang("visa_page_list_seo_desc");
        $meta['seo_share'] = setting_item_with_lang("visa_page_list_seo_share");
        $meta['full_url'] = url()->current();
        return $meta;
    }

    public static function getMinMaxPrice()
    {
        $model = parent::selectRaw('MIN(price) as min_price ,
                                    MAX(price) AS max_price ')->where("status", "publish")->first();
        if (empty($model->min_price) and empty($model->max_price)) {
            return [
                0,
                100
            ];
        }
        return [
            $model->min_price,
            $model->max_price
        ];
    }

    public static function isEnable()
    {
        return !setting_item('visa_disable', false);
    }

    public static function getServiceIconFeatured()
    {
        return "icofont-id";
    }

    public static function getModelName()
    {
        return __('Visa');
    }


    public static function countryList()
    {
        $countries = self::select('to_country')->distinct()->get()->pluck('to_country')->toArray();

        $all_countries = get_country_lists();
        return collect($all_countries)->only($countries)->toArray();
    }


    public function getDiscountPercentAttribute()
    {
        $price = $this->price;
        $original_price = $this->original_price;
        if($price < $original_price){
            return round(($original_price - $price) / $original_price * 100);
        }
        return 0;
    }

    public function getDetailUrl($include_param = true)
    {
        if(!$this->slug){
            return '#';
        }
        return route('visa.detail', ['slug' => $this->slug ? :  $this->id]);
    }


    public function getCountryAttribute()
    {
        return get_country_lists()[$this->to_country] ?? $this->to_country;
    }

    public function addToCart(Request $request)
    {
        
        $total = 0;
        $total_guests = $request->input('guests');
        $discount = 0;
        $base_price = $this->price;

        $total = $base_price * $total_guests;

        //Buyer Fees for Admin
        $total_before_fees = $total;
        $total_buyer_fee = 0;
        if (!empty($list_buyer_fees = setting_item('visa_booking_buyer_fees'))) {
            $list_fees = json_decode($list_buyer_fees, true);
            $total_buyer_fee = $this->calculateServiceFees($list_fees, $total_before_fees, $total_guests);
            $total += $total_buyer_fee;
        }

        //Service Fees for Vendor
        $total_service_fee = 0;
        if (!empty($this->enable_service_fee) and !empty($list_service_fee = $this->service_fee)) {
            $total_service_fee = $this->calculateServiceFees($list_service_fee, $total_before_fees, $total_guests);
            $total += $total_service_fee;
        }

        $booking = app(\Modules\Booking\Models\Booking::class);
        $booking->status = 'draft';
        $booking->object_id = $this->id;
        $booking->object_model = $this->type;
        $booking->vendor_id = $this->author_id;
        $booking->customer_id = Auth::id();
        $booking->total = $total;
        $booking->total_guests = $total_guests;
        $booking->start_date = date('Y-m-d H:i:s');
        $booking->end_date = date('Y-m-d H:i:s');

        $booking->vendor_service_fee_amount = $total_service_fee ?? '';
        $booking->vendor_service_fee = $list_service_fee ?? '';
        $booking->buyer_fees = $list_buyer_fees ?? '';
        $booking->total_before_fees = $total_before_fees;
        $booking->total_before_discount = $total_before_fees;

        $booking->calculateCommission();
        if ($this->isDepositEnable()) {
            $booking_deposit_fomular = $this->getDepositFomular();
            $tmp_price_total = $booking->total;
            if ($booking_deposit_fomular == "deposit_and_fee") {
                $tmp_price_total = $booking->total_before_fees;
            }
            switch ($this->getDepositType()) {
                case "percent":
                    $booking->deposit = $tmp_price_total * $this->getDepositAmount() / 100;
                    break;
                default:
                    $booking->deposit = $this->getDepositAmount();
                    break;
            }
            if ($booking_deposit_fomular == "deposit_and_fee") {
                $booking->deposit = $booking->deposit + $total_buyer_fee + $total_service_fee;
            }
        }
        $check = $booking->save();
        if ($check) {
            $booking::clearDraftBookings();
            $booking->addMeta('duration', $this->duration);
            $booking->addMeta('base_price', $base_price);
            if ($this->isDepositEnable()) {
                $booking->addMeta('deposit_info', [
                    'type'    => $this->getDepositType(),
                    'amount'  => $this->getDepositAmount(),
                    'fomular' => $this->getDepositFomular(),
                ]);
            }
            return $this->sendSuccess([
                'url'          => $booking->getCheckoutUrl(),
                'booking_code' => $booking->code,
            ]);
        }
        return $this->sendError(__("Can not check availability"));
    }
}
