<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider;

use pocketmine\player\Player;

abstract class Provider
{
	public function __construct()
	{
		// I'm alone here :(
	}

	/** @param callable $callable */
	abstract public function setCallable(callable $callable) : void;

	/** @param Player $player */
	abstract public function process(Player $player, int $amount, string $enchantName) : void;

	/** @param int $status */
	abstract public function handle(int $status) : void;
}
