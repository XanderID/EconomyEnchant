<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Manager\Enchantment;

use MulqiGaming64\EconomyEnchant\Manager\EnchantManager;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;

class VanillaEnchant extends Enchant
{
    public function __construct()
    {
        // I'm alone here :)
    }

    /**
     * $enchant is Enchantment
     */
    public function isCompatibleWith($enchant, Item $item): bool
    {
        $flag = EnchantManager::getItemFlags($item);
        if($flag == null) {
            return false;
        }

        return $enchant->hasPrimaryItemType($flag) || $enchant->hasSecondaryItemType($flag);
    }
}
