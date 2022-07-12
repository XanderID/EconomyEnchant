<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;
use SOFe\Capital\Capital as CapitalPL;
use SOFe\Capital\CapitalException;
use SOFe\Capital\LabelSet;
use function is_callable;

class Capital extends Provider
{
	const CAPITAL_VERSION = "0.1.0";

	/** @var callable $callable */
	private $callable;

	private $selector;

	public function __construct($selector)
	{
		$this->selector = $selector;
	}

	/** @return void */
	public function setCallable(callable $callable) : void
	{
		$this->callable = $callable;
	}

	public function process(Player $player, int $amount, string $enchantName) : void
	{
		CapitalPL::api(self::CAPITAL_VERSION, function(CapitalPL $api) use($player, $amount, $enchantName){
			try {
				yield from $api->takeMoney(
					"EconomyEnchant",
					$player,
					$this->selector,
					$amount,
					new LabelSet(["reason" => EconomyEnchant::getInstance()->getLabel($player->getName(), $amount, $enchantName)]),
				);
				$this->handle(EconomyEnchant::STATUS_SUCCESS);
			} catch(CapitalException $error) {
				$this->handle(EconomyEnchant::STATUS_ENOUGH);
		   }
		});
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
