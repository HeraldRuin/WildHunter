<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Hotel\Models\Hotel;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AttachFoodPricesToHotels extends Command
{
    protected $signature = 'hotels:attach-food-prices';

    protected $description = 'Создаёт для каждого админа базы копии всех записей с типом food';

    public function handle(): int
    {
        $foodPrices = AddetionalPrice::query()
            ->where('type', AddetionalPrice::FOOD)
            ->orderByRaw('hotel_id is null desc')
            ->orderBy('id')
            ->get()
            ->unique('name')
            ->values();

        if ($foodPrices->isEmpty()) {
            $this->info('Записей с типом food нет.');

            return CommandAlias::SUCCESS;
        }

        $created = 0;

        Hotel::query()
            ->whereNotNull('admin_base')
            ->orderBy('id')
            ->each(function (Hotel $hotel) use ($foodPrices, &$created) {
                $existingNames = AddetionalPrice::query()
                    ->where('hotel_id', $hotel->id)
                    ->where('type', AddetionalPrice::FOOD)
                    ->pluck('name');

                foreach ($foodPrices as $foodPrice) {
                    if ($existingNames->contains($foodPrice->name)) {
                        continue;
                    }

                    $copy = $foodPrice->replicate();
                    $copy->hotel_id = $hotel->id;
                    $copy->user_id = $hotel->admin_base;
                    $copy->save();
                    $created++;
                }
            });

        $this->info("Создано записей: {$created}.");

        return CommandAlias::SUCCESS;
    }
}
