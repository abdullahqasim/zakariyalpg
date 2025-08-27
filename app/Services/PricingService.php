<?php

namespace App\Services;

class PricingService
{
    private const BASE_SIZE_KG = 11.8;
    private const AVAILABLE_SIZES = [6.0, 11.8, 15.0, 45.4];
    
    /**
     * Calculate proportional price for a given cylinder size based on 11.8kg base price
     */
    public function calculateProportionalPrice(float $basePrice11_8, float $sizeKg, bool $roundToNearest = true): float
    {
        if ($sizeKg <= 0) {
            return 0;
        }
        
        $proportionalPrice = $basePrice11_8 * ($sizeKg / self::BASE_SIZE_KG);
        
        return $roundToNearest ? round($proportionalPrice, 2) : $proportionalPrice;
    }
    
    /**
     * Calculate prices for all available cylinder sizes
     */
    public function calculateAllPrices(float $basePrice11_8, bool $roundToNearest = true): array
    {
        $prices = [];
        
        foreach (self::AVAILABLE_SIZES as $size) {
            $prices[$size] = $this->calculateProportionalPrice($basePrice11_8, $size, $roundToNearest);
        }
        
        return $prices;
    }
    
    /**
     * Get all available cylinder sizes
     */
    public function getAvailableSizes(): array
    {
        return self::AVAILABLE_SIZES;
    }
    
    /**
     * Validate if a size is available
     */
    public function isValidSize(float $size): bool
    {
        return in_array($size, self::AVAILABLE_SIZES);
    }
    
    /**
     * Calculate line total for a sale item
     */
    public function calculateLineTotal(float $unitPrice, int $quantity): float
    {
        return round($unitPrice * $quantity, 2);
    }
    
    /**
     * Calculate sale totals
     */
    public function calculateSaleTotals(array $items, float $discountAmount = 0): array
    {
        $subTotal = 0;
        
        foreach ($items as $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $lineTotal = $this->calculateLineTotal($item['unit_price'], $item['quantity']);
                $subTotal += $lineTotal;
            }
        }
        
        $grandTotal = $subTotal - $discountAmount;
        
        return [
            'sub_total' => round($subTotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
