<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;
use function is_callable;

class XP extends Provider
{

	/** @var callable $callable */
	private $callable;

	public function __construct()
	{
		// I'm alone here :(
	}

	/** @return void */
	public function setCallable(callable $callable) : void
	{
		$this->callable = $callable;
	}

	public function process(Player $player, int $amount, string $enchantName) : void
	{
		$xp = $player->getXpManager();
		if ($xp->getXpLevel() >= $amount) {
			$this->handle(EconomyEnchant::STATUS_SUCCESS);
			$xp->subtractXpLevels($amount);
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
