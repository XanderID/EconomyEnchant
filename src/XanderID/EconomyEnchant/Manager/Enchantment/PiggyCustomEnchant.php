<?php

declare(strict_types=1);

namespace XanderID\EconomyEnchant\Manager\Enchantment;

use DaPigGuy\PiggyCustomEnchants\utils\Utils;

use pocketmine\item\Item;

class PiggyCustomEnchant extends Enchant
{

	public function __construct(){
		// I'm alone here :)
	}

	/**
	 * $enchant is Enchantment
	 */
	public function isCompatibleWith($enchant, Item $item) : bool{
		return Utils::itemMatchesItemType($item, $enchant->getItemType());
	}
}
