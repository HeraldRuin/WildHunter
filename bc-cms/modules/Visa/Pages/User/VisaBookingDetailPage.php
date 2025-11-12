<?php
namespace Modules\Visa\Pages\User;

use Modules\Booking\Models\Booking;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class VisaBookingDetailPage extends Component
{
    public $code;
    public $passengerId;

    #[Url]
    public $source;

    public function mount($code)
    {
        $this->code = $code;

        if(!$this->booking){
            abort(404);
        }

        $this->passengerId = $this->booking->passengers()->first()->id ?? 0;
    }
    public function render()
    {
        $breadcrumbs = [
            ['name' => __('Booking History'), 'url' => route('user.booking_history')],
            ['name' => __('Visa Booking Detail')],
        ];
        if($this->source == 'report'){
            $breadcrumbs = [
                ['name' => __('Booking Report'), 'url' => route('vendor.bookingReport')],
                ['name' => __('Visa Booking Detail')],
            ];
        }
        $data = [
            'booking' => $this->booking,
            'page_title' => __('Visa Booking Detail'),
            'breadcrumbs' => $breadcrumbs,
        ];
        return view('Visa::frontend.user.booking-detail', $data)->extends('Layout::user', $data);
    }

    #[Computed]
    public function booking()
    {
        return app(Booking::class)->where('code', $this->code)->first();
    }

    #[Computed]
    public function currentPassenger()
    {
        return $this->booking->passengers()->where('id', $this->passengerId)->first();
    }

    public function setPassenger($passengerId)
    {
        $this->passengerId = $passengerId;
    }
}
