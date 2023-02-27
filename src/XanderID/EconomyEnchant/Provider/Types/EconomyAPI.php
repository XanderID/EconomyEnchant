<?php

declare(strict_types=1);

namespace XanderID\EconomyEnchant\Provider\Types;

use XanderID\EconomyEnchant\EconomyEnchant;
use XanderID\EconomyEnchant\Provider\Provider;
use onebone\economyapi\EconomyAPI as EconomyAPIPL;
use pocketmine\player\Player;

class EconomyAPI extends Provider
{
	/** @var EconomyAPIPL */
	private $economyAPI;

	public function __construct()
	{
		$this->economyAPI = EconomyAPIPL::getInstance();
	}

	public function process(Player $player, int $amount, string $enchantName, callable $callable) : void
	{
		if ($this->economyAPI->myMoney($player) >= $amount) {
			$this->economyAPI->reduceMoney($player, $amount);
			$callable(EconomyEnchant::STATUS_SUCCESS);
		} else {
			$callable(EconomyEnchant::STATUS_ENOUGH);
		}
	}
}
