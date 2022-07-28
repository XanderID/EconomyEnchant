<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;
use SOFe\Capital\Capital as CapitalPL;
use SOFe\Capital\CapitalException;
use SOFe\Capital\LabelSet;

class Capital extends Provider
{
	const CAPITAL_VERSION = "0.1.0";

	private $selector;

	public function __construct()
	{
		$selector = EconomyEnchant::getInstance()->getSelector(); // Load Selector from Config
		CapitalPL::api(self::CAPITAL_VERSION, function(CapitalPL $api) use($selector){
		  $this->selector = $api->completeConfig($selector);
		});
	}

	public function process(Player $player, int $amount, string $enchantName, callable $callable) : void
	{
		CapitalPL::api(self::CAPITAL_VERSION, function(CapitalPL $api) use($player, $amount, $enchantName, $callable){
			try {
				yield from $api->takeMoney(
					"EconomyEnchant",
					$player,
					$this->selector,
					$amount,
					new LabelSet(EconomyEnchant::getInstance()->getLabels($enchantName, $amount)),
				);
				$callable(EconomyEnchant::STATUS_SUCCESS);
			} catch(CapitalException $error) {
				$callable(EconomyEnchant::STATUS_ENOUGH);
		   }
		});
	}
}
