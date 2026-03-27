<?php

namespace Modules\Animals\DTO;

use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalPreparation;
use Modules\Animals\Models\AnimalTrophy;

class UpdateEntityData
{
    public string $type;   // 'preparation', 'trophy', 'fine'
    public int $id;
    public ?float $price;
    public int $hotelId;

    private $entity;

    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->id = $data['id'];
        $this->price = $data['price'] ?? null;

        $this->resolveEntity();
    }

    private function resolveEntity(): void
    {
        $this->entity = match ($this->type) {
            Animal::SERVICE_PREPARATIONS => AnimalPreparation::findOrFail($this->id),
            Animal::SERVICE_TROPHIES => AnimalTrophy::findOrFail($this->id),
            Animal::SERVICE_FINES => AnimalFine::findOrFail($this->id),
            default => throw new \InvalidArgumentException("Unknown entity type: {$this->type}"),
        };
    }

    /**
     * Возвращает конкретную модель (Preparation, Trophy, Fine)
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
