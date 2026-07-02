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

    public function indexTimerCollection(Request $request)
    {
        $this->checkPermission('settings_view');

        return view('Settings::user.timers.collection_timer', $this->timerPageData(
            __('Таймер сбора'),
            route('settings.vendor.collection-timer'),
            CollectionTimerSettings::TYPE_COLLECT
        ));
    }

    public function indexTimerBeds(Request $request)
    {
        $this->checkPermission('settings_view');

        return view('Settings::user.timers.beds_timer', $this->timerPageData(
            __('Таймер койко-мест'),
            route('settings.vendor.beds-timer'),
            CollectionTimerSettings::TYPE_BEDS
        ));
    }

    public function indexTimerPaid(Request $request)
    {
        $this->checkPermission('settings_view');

        return view('Settings::user.timers.paid_timer', $this->timerPageData(
            __('Таймер предоплаты'),
            route('settings.vendor.paid-timer'),
            CollectionTimerSettings::TYPE_PAID
        ));
    }

    public function store(Request $request)
    {
        $this->checkPermission('settings_create');

        $hotelId = get_user_hotel_id();

        if (!$hotelId) {
            return redirect()->back()->with('error', __('animal.errors.hotel_required'));
        }

        $data = $request->validate([
            'timer_hours' => 'required|integer|min:1',
        ]);
        CollectionTimerSettings::saveTimerHours($data['timer_hours'], $request->input('type'), $hotelId);

        return redirect()->back()->with('success', __('Настройки успешно сохранены'));
    }

    private function timerPageData(string $pageTitle, string $settingsUrl, string $type): array
    {
        $hotelId = get_user_hotel_id();

        return [
            'breadcrumbs' => [
                [
                    'name' => __('Настройки'),
                    'url'  => $settingsUrl,
                ],
                [
                    'name'  => $pageTitle,
                    'class' => 'active',
                ],
            ],
            'page_title'  => $pageTitle,
            'timer_hours' => $hotelId
                ? CollectionTimerSettings::getTimerHours($type, $hotelId)
                : CollectionTimerSettings::DEFAULT_TIMER_HOURS,
            'hotel_id'    => $hotelId,
        ];
    }
}
