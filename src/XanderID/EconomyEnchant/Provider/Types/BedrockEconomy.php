<?php

declare(strict_types=1);

namespace XanderID\EconomyEnchant\Provider\Types;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\LegacyBEAPI;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;

use XanderID\EconomyEnchant\EconomyEnchant;
use XanderID\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;

class BedrockEconomy extends Provider
{
    /** @var LegacyBEAPI $bedrockEconomyAPI */
    private $bedrockEconomyAPI;

    public function __construct()
    {
        $this->bedrockEconomyAPI = BedrockEconomyAPI::legacy();
    }

    public function process(Player $player, int $amount, string $enchantName, callable $callable): void
    {
        $this->bedrockEconomyAPI->subtractFromPlayerBalance(
            $player->getName(),
            $amount,
            ClosureContext::create(
                function (bool $wasUpdated) use ($callable): void {
                    if($wasUpdated) {
                        $callable(EconomyEnchant::STATUS_SUCCESS);
                    } else {
                        $callable(EconomyEnchant::STATUS_ENOUGH);
                    }
                }
            )
        );
    }
}
