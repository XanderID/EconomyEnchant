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

namespace MulqiGaming64\EconomyEnchant\Commands;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

use pocketmine\plugin\PluginOwned;

class EconomyEnchantCommands extends Command implements PluginOwned
{
	/** @var EconomyEnchant $plugin */
	private $plugin;

	public function __construct(EconomyEnchant $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct("eshop", "Economy EnchantShop", "/eshop", []);
		$this->setPermission("economyenchant.cmd");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
	{
		if (!$sender instanceof Player) {
			return false;
		}
		if (!$this->testPermission($sender)) {
			return false;
		}

		$this->getOwningPlugin()->sendShop($sender);
		return true;
	}

	public function getOwningPlugin() : EconomyEnchant
	{
		return $this->plugin;
	}
}
