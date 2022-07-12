<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\LegacyBEAPI;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\context\ClosureContext;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;
use function is_callable;

class BedrockEconomy extends Provider
{
	/** @var LegacyBEAPI $bedrockEconomyAPI */
	private $bedrockEconomyAPI;

	/** @var callable $callable */
	private $callable;

	public function __construct()
	{
		$this->bedrockEconomyAPI = BedrockEconomyAPI::legacy();
	}

	/** @return void */
	public function setCallable(callable $callable) : void
	{
		$this->callable = $callable;
	}

	public function process(Player $player, int $amount, string $enchantName) : void
	{
		$this->bedrockEconomyAPI->subtractFromPlayerBalance(
			$player->getName(),
			$amount,
			ClosureContext::create(
				function (bool $wasUpdated) : void {
					if($wasUpdated){
						$this->handle(EconomyEnchant::STATUS_SUCCESS);
					} else {
						$this->handle(EconomyEnchant::STATUS_ENOUGH);
					}
				}
			)
		); // Sorry for the carelessness, the money should have been reduced immediately
	}

	/** @param int $status */
	public function handle(int $status) : void
	{
		if (is_callable($this->callable)) {
			$call = $this->callable;
			$call($status);
		}
	}
}
