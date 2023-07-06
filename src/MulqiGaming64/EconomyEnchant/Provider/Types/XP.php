<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;

class XP extends Provider
{
    public function __construct()
    {
        // I'm alone here :(
    }

    public function process(Player $player, int $amount, string $enchantName, callable $callable): void
    {
        $xp = $player->getXpManager();
        if ($xp->getXpLevel() >= $amount) {
            $xp->subtractXpLevels($amount);
            $callable(EconomyEnchant::STATUS_SUCCESS);
        } else {
            $callable(EconomyEnchant::STATUS_ENOUGH);
        }
    }
}
