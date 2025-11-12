<?php

namespace Modules\Order\Traits;

trait HasBuyable
{
    // Max quantity of this item in the cart, // default is infinite
    public function getMaxQuantity(): ?int
    {
        return PHP_INT_MAX;
    }


    public function getBuyableName(): string
    {
        return $this->title;
    }

    // after sale
    public function getBuyablePrice(): float
    {
        return $this->price;
    }


    public function getBuyableOriginalPrice(): ?float
    {
        return $this->original_price;
    }

    public function getBuyableImage($size = 'thumb', ...$args): string
    {
        return get_file_url($this->image_id, $size, ...$args);
    }

    public function getBuyableUrl(): string
    {
        return $this->getDetailUrl();
    }

    public function getBuyableDiscount(): float
    {
        return $this->getBuyableOriginalPrice() - $this->getBuyablePrice();
    }

    public function getBuyableDiscountPercent(): float
    {
        if (!$this->getBuyableOriginalPrice()) {
            return 0;
        }
        return $this->getBuyableDiscount() / $this->getBuyableOriginalPrice() * 100;
    }
    
}