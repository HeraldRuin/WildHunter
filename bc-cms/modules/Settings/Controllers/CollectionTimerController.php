<?php
namespace Modules\Settings\Controllers;

use Illuminate\Http\Request;
use Modules\FrontendController;
use Modules\Settings\Models\CollectionTimerSettings;

class CollectionTimerController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        $this->setActiveMenu(route('settings.vendor.collection-timer'));
    }

    public function index(Request $request)
    {
        $this->checkPermission('settings_view');

        $hotelId = get_user_hotel_id();

        if (!$hotelId) {
            return redirect()->back()->with('error', __('Отель не найден'));
        }

        $data = [
            'breadcrumbs' => [
                [
                    'name' => __('Настройки'),
                    'url'  => route('settings.vendor.collection-timer')
                ],
                [
                    'name'  => __('Таймер сбора'),
                    'class' => 'active'
                ],
            ],
            'page_title' => __('Таймер сбора'),
            'timer_hours' => CollectionTimerSettings::getTimerHours($hotelId),
            'hotel_id' => $hotelId
        ];

        return view('Settings::user.collection-timer.index', $data);
    }

    public function store(Request $request)
    {
        $this->checkPermission('settings_create');

        $hotelId = get_user_hotel_id();

        if (!$hotelId) {
            return redirect()->back()->with('error', __('Отель не найден'));
        }

        $data = $request->validate([
            'timer_hours' => 'required|integer|min:1',
        ]);
        CollectionTimerSettings::saveTimerHours($data['timer_hours'], $hotelId);

        return redirect()->back()->with('success', __('Настройки успешно сохранены'));
    }
}
