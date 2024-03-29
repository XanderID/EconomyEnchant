<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Manager;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Manager\Enchantment\Enchant;
use pocketmine\item\StringToItemParser;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;

// For getting ItemFlags
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\FishingRod;
use pocketmine\item\FlintSteel;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

use pocketmine\player\Player;

use function array_map;
use function in_array;
use function is_a;
use function str_replace;

class EnchantManager
{
    /** @var array $enchant */
    private static $enchant = [];

    public static function getAll(): array
    {
        return self::$enchant;
    }

    /**
     * @phpstan-param class-string<Enchant> $compatible
     */
    public static function register(string $nameId, string $displayName, int $price, Enchantment $enchant, $compatible): void
    {
        if(!is_a($compatible, Enchant::class, true)) {
            throw new \RuntimeException("Compatible class must be Extends to \MulqiGaming64\EconomyEnchant\Manager\Enchantment\Enchant");
        }

        self::$enchant[$displayName] = ["name" => $nameId, "price" => $price, "enchant" => $enchant, "compatible" => $compatible];
    }

    public static function unregister(string $nameId): bool
    {
        foreach(self::$enchant as $display => $value) {
            if($value["name"] == $nameId) {
                unset(self::$enchant[$display]);
                return true;
            }
        }

        return false;
    }

    public static function getPriceInConfig(string $nameId): ?int
    {
        $allPrice = EconomyEnchant::getInstance()->getConfig()->get("enchantment");
        if(isset($allPrice[$nameId])) {
            return $allPrice[$nameId]["price"];
        }

        return null;
    }

    public static function getEnchantByItem(Item $item): array
    {
        $result = [];

        foreach(self::$enchant as $display => $value) {
            if(self::isEnchantBlacklisted($value["name"])) {
                continue;
            }
            if(self::isItemBlacklisted($item, $value["name"])) {
                continue;
            }

            $check = new $value["compatible"]();
            if($check->isCompatibleWith($value["enchant"], $item)) {
                $result[$display] = ["display" => $display, "price" => $value["price"], "enchant" => $value["enchant"]];
            }
        }

        return $result;
    }

    public static function isEnchantBlacklisted(string $nameId): bool
    {
        $blacklist = array_map("strtolower", EconomyEnchant::getInstance()->getConfig()->get("blacklist")); // Getting all List blacklisted Config

        return in_array($nameId, $blacklist, true);
    }

    public static function isItemBlacklisted(Item $item, string $nameId): bool
    {
        $blacklist = EconomyEnchant::getInstance()->getConfig()->get("blacklist-item"); // Getting all List blacklisted Config

        if (isset($blacklist[$nameId])) { // check if enchantment blacklist
            foreach ($blacklist[$nameId] as $itemall) {
                $itemb = StringToItemParser::getInstance()->parse($itemall);
                if ($item->equals($itemb, true, false)) { // check if item same
                    return true;
                }
            }
        }
        return false;
    }

    /** @return void */
    public static function enchantItem(Player $player, Enchantment $enchant, int $level): void
    {
        $item = $player->getInventory()->getItemInHand();
        $item->addEnchantment(new EnchantmentInstance($enchant, $level)); // Add Enchantment
        $player->getInventory()->setItemInHand($item); // Send back item to Player
    }

    /**
     * @var Player $player
     */
    public static function sendSound(Player $player): void
    {
        // Checking if Sound play is true
        if(!EconomyEnchant::getInstance()->getConfig()->get("sound")) {
            return;
        }

        $pos = $player->getPosition();
        $packet = PlaySoundPacket::create("random.anvil_use", $pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), 1.0, 1.0);
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public static function getItemFlags(Item $item): ?int
    {
        if ($item instanceof Armor) {
            $slot = $item->getArmorSlot();
            $slotMapping = [
                ArmorInventory::SLOT_HEAD => ItemFlags::HEAD,
                ArmorInventory::SLOT_CHEST => ItemFlags::TORSO,
                ArmorInventory::SLOT_LEGS => ItemFlags::LEGS,
                ArmorInventory::SLOT_FEET => ItemFlags::FEET
            ];
            return $slotMapping[$slot] ?? null;
        } else {
            $itemClass = get_class($item);
            $itemFlags = [
                Sword::class => ItemFlags::SWORD,
                Axe::class => ItemFlags::AXE,
                Pickaxe::class => ItemFlags::PICKAXE,
                Shovel::class => ItemFlags::SHOVEL,
                Hoe::class => ItemFlags::HOE,
                Shears::class => ItemFlags::SHEARS,
                FlintSteel::class => ItemFlags::FLINT_AND_STEEL,
                FishingRod::class => ItemFlags::FISHING_ROD,
                Bow::class => ItemFlags::BOW
            ];

            return $itemFlags[$itemClass] ?? null;
        }

        return null;
    }

    /** @return string */
    public static function numberToRoman(int $number): string
    {
        $roman = ["M" => 1000, "CM" => 900, "D" => 500, "CD" => 400, "C" => 100, "XC" => 90, "L" => 50, "XL" => 40, "X" => 10, "IX" => 9, "V" => 5, "IV" => 4, "I" => 1];
        $return = "";
        while ($number > 0) {
            foreach ($roman as $value => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $return .= $value;
                    break;
                }
            }
        }
        return $return;
    }
}
