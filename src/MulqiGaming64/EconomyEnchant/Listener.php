<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant;

use pocketmine\block\EnchantingTable;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerInteractEvent;

class Listener implements PMListener
{
	/** @var bool $enchantTable */
	private $enchantTable = true;

	/** @param bool $enchantTable */
	public function __construct(bool $enchantTable){
		$this->enchantTable = $enchantTable;
	}

	/** @priorities HIGHEST */
	public function onInteract(PlayerInteractEvent $event) : bool
	{
		if($event->isCancelled()) return false;

		$player = $event->getPlayer();
		$block = $event->getBlock();
		if($this->enchantTable){
			if($block instanceof EnchantingTable){
				$event->cancel();
				EconomyEnchant::getInstance()->sendShop($player);
			}
		}
		return true;
	}
}
