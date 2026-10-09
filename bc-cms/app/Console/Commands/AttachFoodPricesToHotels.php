<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Hotel\Models\Hotel;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AttachFoodPricesToHotels extends Command
{
    protected $signature = 'hotels:attach-food-prices';

    protected $description = 'Привязывает доп. цены с типом food к отелям, у которых их ещё нет';

    public function handle(): int
    {
        $foodPrices = AddetionalPrice::query()
            ->where('type', AddetionalPrice::FOOD)
            ->whereNull('hotel_id')
            ->get()
            ->unique('name')
            ->values();

        if ($foodPrices->isEmpty()) {
            $this->info('Записей с типом food нет.');

            return CommandAlias::SUCCESS;
        }

        $attached = 0;

        Hotel::query()->orderBy('id')->each(function (Hotel $hotel) use ($foodPrices, &$attached) {
            $existingNames = AddetionalPrice::query()
                ->where('hotel_id', $hotel->id)
                ->where('type', AddetionalPrice::FOOD)
                ->pluck('name');

            foreach ($foodPrices as $foodPrice) {
                if ($existingNames->contains($foodPrice->name)) {
                    continue;
                }

                $foodPrice->hotel_id = $hotel->id;
                $foodPrice->save();
                $attached++;
            }
        });

        $this->info("Привязано записей: {$attached}.");

        return CommandAlias::SUCCESS;
    }
}
