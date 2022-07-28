<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\LegacyBEAPI;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\context\ClosureContext;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;

class BedrockEconomy extends Provider
{
	/** @var LegacyBEAPI $bedrockEconomyAPI */
	private $bedrockEconomyAPI;

	public function __construct(callable $callable)
	{
		$this->bedrockEconomyAPI = BedrockEconomyAPI::legacy();
	}

	public function process(Player $player, int $amount, string $enchantName, callable $callable) : void
	{
		$this->bedrockEconomyAPI->subtractFromPlayerBalance(
			$player->getName(),
			$amount,
			ClosureContext::create(
				function (bool $wasUpdated) use($callable) : void {
					if($wasUpdated){
						$callable(EconomyEnchant::STATUS_SUCCESS);
					} else {
						$callable(EconomyEnchant::STATUS_ENOUGH);
					}
				}
			)
		);
	}
}
