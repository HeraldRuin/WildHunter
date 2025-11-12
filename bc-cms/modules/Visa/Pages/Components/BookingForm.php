<?php   
namespace Modules\Visa\Pages\Components;

use App\BaseComponent;
use Illuminate\Http\JsonResponse;
use Modules\Visa\Models\VisaService;
use Livewire\Attributes\Url;
use Illuminate\Http\Request;

class BookingForm extends BaseComponent
{
    public $row;

    #[Url]
    public $guests = 1;


    public $bookingData = [
        'price' => 0,
        'buyer_fees' => [],
    ];

    public function mount(){
        $this->bookingData['price'] = $this->row->price;
    }
    
    public function render()
    {
        return view('Visa::frontend.components.booking-form');
    }

    public function addToCart(){

        $data = $this->validate([
            'guests' => 'required|numeric|min:1',
        ]);

        $guests = $data['guests'];
        $row = app(VisaService::class)->find($this->row->id);

        if(!$row){
            $this->sendError(__("Service not found"));
            return;
        }

        $request = app(Request::class);
        $request->merge([
            'guests' => $guests,
        ]);

        $res = $row->addToCart($request);

        // If response is JsonResponse, try to get data from it, cuz old ways is not support livewire
        if($res instanceof JsonResponse){
            $res = $res->getData(true);
        }

        if(!empty($res['message'])){
            if(!empty($res['status'])){
                $this->sendSuccess($res['message']);
            }else{
                $this->sendError($res['message']);
            }
            return;
        }

        // Redirect to applications page
        
        if(!empty($res['booking_code'])){
            return $this->redirectRoute('visa.applications', ['code' => $res['booking_code'],'slug' => $row->slug]);
        }
    }
}
