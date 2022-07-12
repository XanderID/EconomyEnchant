<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use onebone\economyapi\EconomyAPI as EconomyAPIPL;
use pocketmine\player\Player;
use function is_callable;

class EconomyAPI extends Provider
{
	/** @var EconomyAPIPL */
	private $economyAPI;

	/** @var callable $callable */
	private $callable;

	public function __construct()
	{
		$this->economyAPI = EconomyAPIPL::getInstance();
	}

	/** @return void */
	public function setCallable(callable $callable) : void
	{
		$this->callable = $callable;
	}

	public function process(Player $player, int $amount, string $enchantName) : void
	{
		if ($this->economyAPI->myMoney($player) >= $amount) {
			$this->handle(EconomyEnchant::STATUS_SUCCESS);
			$this->economyAPI->reduceMoney($player, $amount);
		} else {
			$this->handle(EconomyEnchant::STATUS_ENOUGH);
		}
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
