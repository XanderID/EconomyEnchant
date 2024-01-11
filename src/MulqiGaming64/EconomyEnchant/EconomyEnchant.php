<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant;

use DavidGlitch04\VanillaEC\Main as VanillaEC;
use JackMD\ConfigUpdater\ConfigUpdater;

use JackMD\UpdateNotifier\UpdateNotifier;

use MulqiGaming64\EconomyEnchant\Commands\EconomyEnchantCommands;
use MulqiGaming64\EconomyEnchant\Manager\EnchantManager;
use MulqiGaming64\EconomyEnchant\Manager\Enchantment\VanillaEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use MulqiGaming64\EconomyEnchant\Provider\Types\BedrockEconomy;
use MulqiGaming64\EconomyEnchant\Provider\Types\XP;
use MulqiGaming64\EconomyEnchant\Transaction\Shop\GUI;
use MulqiGaming64\EconomyEnchant\Transaction\Shop\UI;
use muqsit\invmenu\InvMenu;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\VanillaEnchantments;

use MulqiGaming64\EconomyEnchant\Item\EnchantedBook;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\StringToItemParser;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\io\GlobalItemDataHandlers;

use jojoe77777\FormAPI\FormAPI;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use function class_exists;
use function implode;
use function str_replace;
use function strtolower;
use function ucwords;

class EconomyEnchant extends PluginBase
{
    /** XP Provider does not need to be included here because it is not a Plugin */
    public const availableEconomy = ["BedrockEconomy"];

    /** All status Provider */
    public const STATUS_SUCCESS = 0;
    public const STATUS_ENOUGH = 1;

    /** Config Version */
    private const CONFIG_VERSION = 3;

    /** @return EconomyEnchant */
    private static EconomyEnchant $instance;

    /** @var Provider $provider */
    private $provider;

    protected function onLoad(): void
    {
        self::$instance = $this; // Preparing Instance
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        if(!$this->checkVirion()) {
            return;
        }

        // Checking New version
        UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());

        // Checking Config version
        if(ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION)) {
            $this->reloadConfig();
        }

        $economy = $this->getEconomyType();
        if($economy !== null) {
            $this->registerProvider($economy);

            $mode = (bool) $this->getConfig()->get("mode"); // Getting mode in Config

            // Registering Enchantment to Shop
            $this->registerVanillaEnchant($mode);

            // Checking softdepend
            if (class_exists(VanillaEC::class)) {
                $this->registerVanillaCEnchant($mode);
            }

            $this->getServer()->getPluginManager()->registerEvents(new Listener((bool) $this->getConfig()->get("enchant-table")), $this);
            $this->getServer()->getCommandMap()->register("EconomyEnchant", new EconomyEnchantCommands($this));

            if(StringToItemParser::getInstance()->parse("enchanted_book") === null) {
                self::registerBook();
                $this->getServer()->getAsyncPool()->addWorkerStartHook(function (int $worker): void {
                    $this->getServer()->getAsyncPool()->submitTaskToWorker(new class () extends AsyncTask {
                        public function onRun(): void
                        {
                            EconomyEnchant::registerBook();
                        }
                    }, $worker);
                });
            }
        }
    }

    public function checkVirion(): bool
    {
        /** Checking available Virion */
        $virion = [
            "ConfigUpdater" => ConfigUpdater::class,
            "UpdateNotifier" => UpdateNotifier::class
        ];

        // for log to Console
        $notInstalled = [];
        foreach($virion as $name => $class) {
            if(!class_exists($class)) {
                $notInstalled[] = $name;
            }
        }

        if(!empty($notInstalled)) {
            $log = implode(", ", $notInstalled);
            $this->getLogger()->warning("Virion: " . $log . " not installed please install or Download EconomyEnchant from Poggit CI, Disabling Plugin!");

            // Disable Plugin
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }

        // Checking form Type and Depending Libs
        $formType = strtolower($this->getConfig()->get("form-type"));
        if($formType == "gui") {
            if(!class_exists(InvMenu::class)) {
                $this->getLogger()->warning("InvMenu not installed, If you want use GUI please install or Download EconomyEnchant from Poggit CI, Disabling Plugin!");
                // Disable Plugin
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return false;
            } else {
                if(!InvMenuHandler::isRegistered()) {
                    InvMenuHandler::register($this);
                }
            }
        } else {
            if(!class_exists(FormAPI::class)) {
                $this->getLogger()->warning("FormAPI not installed, If you want use UI please install or Download EconomyEnchant from Poggit CI, Disabling Plugin!");
                // Disable Plugin
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return false;
            }
        }
        return true;
    }

    /** @return null|string */
    public function getEconomyType()
    {
        $economys = strtolower($this->getConfig()->get("economy"));
        $economy = null;
        $plugin = $this->getServer()->getPluginManager();

        switch ($economys) {
            case "bedrockeconomy":
                if ($plugin->getPlugin("BedrockEconomy") == null) {
                    $this->getLogger()->alert("Your Economy's plugin: BedrockEconomy, Not found Disabling Plugin!");
                    $plugin->disablePlugin($this);
                    return null;
                }
                $economy = "BedrockEconomy";
                break;
            case "xp":
                $economy = "XP";
                break;
            case "auto":
                $found = false;
                foreach (self::availableEconomy as $eco) {
                    if ($plugin->getPlugin($eco) !== null) {
                        $economy = $eco;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $this->getLogger()->alert("all economy plugins could not be found, Using XP as an alternative!");
                    $economy = "XP";
                }
                break;
            default:
                $this->getLogger()->info("No economy plugin Selected, Detecting");
                $found = false;
                foreach (self::availableEconomy as $eco) {
                    if ($plugin->getPlugin($eco) !== null) {
                        $economy = $eco;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $this->getLogger()->alert("all economy plugins could not be found, Using XP as an alternative!");
                    $economy = "XP";
                }
                break;
        }
        return $economy;
    }

    /**
     * @internal
     */
    public static function getInstance(): EconomyEnchant
    {
        return self::$instance;
    }

    /**
     * Get message type
     * To static for easy getting
     */
    public static function getMessage(string $type): string
    {
        return self::$instance->getConfig()->get("message")[$type];
    }

    /**
     * Register all Available enchantment from VanillaPocketmine
     */
    public function registerVanillaEnchant(bool $mode = true): void
    {
        // Get all Available enchantment Vanilla
        $all = VanillaEnchantments::getAll();

        // add only if not Blacklisted
        foreach ($all as $name => $enchant) {
            $sname = strtolower($name);

            // Display name for Button
            // _ replaced to space
            $display = str_replace("_", " ", $name);
            $displayname = ucwords(strtolower($display));

            // Getting price and pass if the mode is false and the price is not set
            $price = EnchantManager::getPriceInConfig($sname);
            if($price == null) {
                if(!$mode) {
                    continue;
                }

                $price = EnchantManager::getPriceInConfig("default");
            }

            // Registering Enchant to Shop
            EnchantManager::register($sname, $displayname, $price, $enchant, VanillaEnchant::class);
        }
    }

    /**
     * Register all Available enchantment from VanillaCE
     */
    public function registerVanillaCEnchant(bool $mode = true): void
    {
        // Instance from EnchantIDMap
        $encmap = EnchantmentIdMap::getInstance();

        // All VanillaEC Enchantment
        $all = [
            EnchantmentIds::BANE_OF_ARTHROPODS => $encmap->fromId(EnchantmentIds::BANE_OF_ARTHROPODS),
            EnchantmentIds::LOOTING => $encmap->fromId(EnchantmentIds::LOOTING),
            EnchantmentIds::SMITE => $encmap->fromId(EnchantmentIds::SMITE)
        ];

        // add only if not Blacklisted
        foreach ($all as $id => $enchant) {
            $name = strtolower(str_replace(" ", "_", $enchant->getId())); // Replace space name with underline

            // Display name for Button
            // _ replaced to space
            $display = str_replace("_", " ", $name);
            $displayname = ucwords(strtolower($display));

            // Getting price and pass if the mode is false and the price is not set
            $price = EnchantManager::getPriceInConfig($name);
            if($price == null) {
                if(!$mode) {
                    continue;
                }

                $price = EnchantManager::getPriceInConfig("default");
            }

            // Registering Enchant to Shop
            EnchantManager::register($name, $displayname, $price, $enchant, VanillaEnchant::class);
        }
    }

    public static function registerBook()
    {
        $item = EnchantedBook::ENCHANTED_BOOK();
        $id = ItemTypeNames::ENCHANTED_BOOK;

        GlobalItemDataHandlers::getDeserializer()->map($id, fn () => clone $item);
        GlobalItemDataHandlers::getSerializer()->map($item, fn () => new SavedItemData($id));
        StringToItemParser::getInstance()->register("enchanted_book", fn () => clone $item);
    }

    private function registerProvider(string $name = "XP"): void
    {
        if ($name == "BedrockEconomy") {
            $provider = new BedrockEconomy();
        } elseif ($name == "XP") {
            $provider = new XP();
        }

        $this->provider = $provider;
    }

    /** @return Provider */
    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function sendShop(Player $player): void
    {
        $formType = strtolower($this->getConfig()->get("form-type"));
        ($formType === "gui") ? (new GUI())->sendShop($player, 0) : (new UI())->sendShop($player);
    }
}
