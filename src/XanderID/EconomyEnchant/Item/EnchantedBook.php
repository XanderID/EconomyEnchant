<?php

declare(strict_types=1);

namespace XanderID\EconomyEnchant\Item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;

/**
* @method static \pocketmine\item\Item ENCHANTED_BOOK()
*/

class EnchantedBook
{
    use CloningRegistryTrait;

    private function __construct()
    {
        //NOOP
    }

    protected static function register(string $name, Item $item): void
    {
        self::_registryRegister($name, $item);
    }

    /**
     * @return Item[]
     * @phpstan-return array<string, Item>
     */
    public static function getAll(): array
    {
        /** @var Item[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup(): void
    {
        $enchantBookTypeId = ItemTypeIds::newId();
        self::register("enchanted_book", new Item(new ItemIdentifier($enchantBookTypeId), "Enchanted Book"));
    }
}
