<?php

declare(strict_types=1);

namespace XanderID\EconomyEnchant\Transaction\Shop;

use XanderID\EconomyEnchant\EconomyEnchant;
use XanderID\EconomyEnchant\Manager\EnchantManager;
use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

use function ksort;
use function str_replace;

class UI
{
    /** @var array */
    private $form = [];

    public function __construct()
    {
        $this->form = EconomyEnchant::getInstance()->getConfig()->get("form");
    }

    /** @param Player $player */
    public function sendShop(Player $player): void
    {
        $item = $player->getInventory()->getItemInHand();

        // List all enchantment by Item
        $list = EnchantManager::getEnchantByItem($item);

        // sort enchant names alphabetically
        ksort($list);

        // Check if item cannot be enchant
        if(empty($list)) {
            $player->sendMessage(EconomyEnchant::getMessage("err-item"));
            return;
        }

        // Create Form
        $form = new SimpleForm(function (Player $player, $data = null) use ($list) {
            if ($data === null) {
                $player->sendMessage(EconomyEnchant::getMessage("exit"));
                return;
            }
            $newData = $list[$data];
            $newData["display"] = $data;
            $this->submit($player, $newData);
        });
        $form->setTitle($this->form["buy-shop"]["title"]);
        $form->setContent($this->form["buy-shop"]["content"]);
        foreach ($list as $display => $enchant) {
            // Get Price from Enchant
            $price = $enchant["price"];
            // Button style
            $button = $this->getButton(0, [$display, $price]);
            $button2 = $this->getButton(1, [$display, $price]);
            $form->addButton($button . "\n" . $button2, -1, "", $display);
        }
        $player->sendForm($form);
    }

    /**
     * Submit Enchant
     */
    private function submit(Player $player, array $encdata): void
    {
        // Preparing Enchant
        $enchantment = $encdata["enchant"];
        $display = $encdata["display"];

        $item = $player->getInventory()->getItemInHand();
        $nowlevel = (int) $item->hasEnchantment($enchantment) ? $item->getEnchantmentLevel($enchantment) : 0;
        $maxlevel = (int) $enchantment->getMaxLevel();

        // Preparing form
        $form = new CustomForm(function (Player $player, $data = null) use ($encdata, $display) {
            if ($data === null) {
                $player->sendMessage(EconomyEnchant::getMessage("exit"));
                return;
            }
            // If Item Level Max
            if ($data[1] === null) {
                $player->sendMessage(EconomyEnchant::getMessage("max"));
                return;
            }

            $reqlevel = (int) $data[1]; // get requested level
            $price = (int) $encdata["price"] * $reqlevel; // multiply level by price
            $provider = EconomyEnchant::getInstance()->getProvider();
            // Process Transaction With callable
            $provider->process($player, $price, $display, function (int $status) use ($player, $encdata, $display, $price, $reqlevel) {
                if ($status == EconomyEnchant::STATUS_SUCCESS) {
                    $item = $player->getInventory()->getItemInHand();
                    $msg = str_replace(
                        ["{price}", "{item}", "{enchant}"],
                        ["" . $price, $item->getVanillaName(), $display . " " . EnchantManager::numberToRoman($reqlevel)],
                        EconomyEnchant::getMessage("success")
                    );
                    $player->sendMessage($msg);
                    EnchantManager::enchantItem($player, $encdata["enchant"], $reqlevel);
                    EnchantManager::sendSound($player);
                } else {
                    $msg = str_replace("{need}", "" . $price, EconomyEnchant::getMessage("enough"));
                    $player->sendMessage($msg);
                }
                return;
            });
            return;
        });
        $form->setTitle($this->form["submit"]["title"]);
        $form->addLabel(str_replace("{price}", "" . $encdata["price"], $this->form["submit"]["content"]));
        if ($nowlevel < $maxlevel) {
            $form->addSlider($this->form["submit"]["slider"], ($nowlevel + 1), $maxlevel);
        } else {
            $form->addLabel("\n" . $this->form["submit"]["max-content"]);
        }
        $player->sendForm($form);
    }

    public function getButton(int $index, array $data): string
    {
        // Get Button from Config
        $button = $this->form["buy-shop"]["button"];
        return str_replace(["{enchant}", "{price}"], [$data[0], $data[1]], $button[$index]);
    }
}
