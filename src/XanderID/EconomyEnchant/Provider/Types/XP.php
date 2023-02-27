<?php

declare(strict_types=1);

namespace XanderID\EconomyEnchant\Provider\Types;

use XanderID\EconomyEnchant\EconomyEnchant;
use XanderID\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;

class XP extends Provider
{

	public function __construct()
	{
		// I'm alone here :(
	}

	public function process(Player $player, int $amount, string $enchantName, callable $callable) : void
	{
		$xp = $player->getXpManager();
		if ($xp->getXpLevel() >= $amount) {
			$xp->subtractXpLevels($amount);
			$callable(EconomyEnchant::STATUS_SUCCESS);
		} else {
			$callable(EconomyEnchant::STATUS_ENOUGH);
		}
	}
}
