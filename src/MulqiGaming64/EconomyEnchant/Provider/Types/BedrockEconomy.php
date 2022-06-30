<?php

/*
 *  __  __       _       _  ____                 _              __   _  _   
 * |  \/  |_   _| | __ _(_)/ ___| __ _ _ __ ___ (_)_ __   __ _ / /_ | || |  
 * | |\/| | | | | |/ _` | | |  _ / _` | '_ ` _ \| | '_ \ / _` | '_ \| || |_ 
 * | |  | | |_| | | (_| | | |_| | (_| | | | | | | | | | | (_| | (_) |__   _|
 * |_|  |_|\__,_|_|\__, |_|\____|\__,_|_| |_| |_|_|_| |_|\__, |\___/   |_|  
 *                    |_|                                |___/              
 *
 * Copyright (c) 2022 MulqiGaming64
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\LegacyBEAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy as BedrockEconomyPL;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\context\ClosureContext;

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
    public function setCallable(callable $callable): void
    {
        $this->callable = $callable;
    }

    public function process(Player $player, int $amount): void
    {
        $this->bedrockEconomyAPI->subtractFromPlayerBalance(
            $player->getName(),
            $amount,
            ClosureContext::create(
                function (bool $wasUpdated): void {
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
    public function handle(int $status): void
    {
        if (is_callable($this->callable)) {
            $call = $this->callable;
            $call($status);
        }
    }
}
