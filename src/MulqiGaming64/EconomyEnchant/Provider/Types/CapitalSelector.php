<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use SOFe\Capital\Capital as CapitalPL;

class CapitalSelector
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

	public function getSelector(){
		return $this->selector;
	}
}
