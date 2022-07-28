<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Manager\Enchantment;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;

abstract class Enchant
{

	abstract public function __construct();

	/**
	 * $enchant is Enchantment
	 */
	abstract public function isCompatibleWith($enchant, Item $item) : bool;
}
