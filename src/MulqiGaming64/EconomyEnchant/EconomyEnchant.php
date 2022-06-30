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

namespace MulqiGaming64\EconomyEnchant;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;

use MulqiGaming64\EconomyEnchant\Commands\EconomyEnchantCommands;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use MulqiGaming64\EconomyEnchant\Provider\Types\EconomyAPI;
use MulqiGaming64\EconomyEnchant\Provider\Types\Capital;
use MulqiGaming64\EconomyEnchant\Provider\Types\BedrockEconomy;

use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\utils\Utils as PiggyUtils;

use MulqiGaming64\EconomyEnchant\libs\Vecnavium\FormsUI\SimpleForm;
use MulqiGaming64\EconomyEnchant\libs\Vecnavium\FormsUI\CustomForm;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\block\EnchantingTable;
// List Item can Enchanted
use pocketmine\item\ItemBlock;
use pocketmine\item\Armor;
use pocketmine\item\Sword;
use pocketmine\item\Axe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Hoe;
use pocketmine\item\FishingRod;
use pocketmine\item\FlintSteel;
use pocketmine\item\Compass;
use pocketmine\item\Shears;
use pocketmine\item\Bow;
use pocketmine\item\Durable;
use pocketmine\inventory\ArmorInventory;
use pocketmine\data\bedrock\LegacyItemIdToStringIdMap;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class EconomyEnchant extends PluginBase implements Listener
{
    public const availableEconomy = ["BedrockEconomy", "Capital", "EconomyAPI"];

    /** All status Provider */
    public const STATUS_SUCCESS = 0;
    public const STATUS_ENOUGH = 1;
    
    /** @return EconomyEnchant */
    private static EconomyEnchant $instance;

    /** @var array $allEnchantment */
    private $allEnchantment = [];
    /** @var array $enchantConfig */
    private $enchantConfig = [];
    /** @var bool $mode */
    private $mode = true;
    /** @var bool $enchantTable */
    private $enchantTable = true;
    /** @var bool $piggyCE */
    private $piggyCE = false;

    /** @var Provider $provider */
    private $provider = null;
    
    protected function onLoad(): void {
		self::$instance = $this; // Preparing Instance
	}

    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $economy = $this->getEconomyType();
        if($economy !== null){
       	 $this->registerProvider($economy);
       
       	if (class_exists(PiggyCustomEnchants::class)) {
        		$this->piggyCE = true;
   	     }

        	$this->mode = $this->getConfig()->get("mode");
        	$this->enchantTable = $this->getConfig()->get("enchant-table");
      	  $this->registerEnchantConfig();

			$this->getServer()->getPluginManager()->registerEvents($this, $this);
        	$this->getServer()->getCommandMap()->register("EconomyEnchant", new EconomyEnchantCommands($this));
        }
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
            case "capital":
      	      if ($plugin->getPlugin("Capital") == null) {
                    $this->getLogger()->alert("Your Economy's plugin: Capital, Not found Disabling Plugin!");
                    $plugin->disablePlugin($this);
                    return null;
                }
                $economy = "Capital";
            break;
            case "economyapi":
                if ($plugin->getPlugin("EconomyAPI") == null) {
                    $this->getLogger()->alert("Your Economy's plugin: EconomyAPI, Not found Disabling Plugin!");
                    $plugin->disablePlugin($this);
                    return null;
                }
                $economy = "EconomyAPI";
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
                    $this->getLogger()->alert("all economy plugins could not be found, Disabling Plugin!");
                    $plugin->disablePlugin($this);
                    return null;
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
                    $plugin->disablePlugin($this);
                    $this->getLogger()->alert("all economy plugins could not be found, Disabling Plugin!");
                    return null;
                }
            break;
        }
        return $economy;
    }
    
    /** @priorities HIGHEST */
    public function onInteract(PlayerInteractEvent $event): bool
	{
		if($event->isCancelled()) return false;
		
    	$player = $event->getPlayer();
    	$block = $event->getBlock();
    	if($this->enchantTable){
    		if($block instanceof EnchantingTable){
    			$event->cancel();
    			$this->sendShop($player);
    		}
    	}
    	return true;
    }
    
    /**
	 * @internal
	 * @return EconomyEnchant
	 */
	public static function getInstance(): EconomyEnchant
	 {
		return self::$instance;
	}
	
	/**
	* @return array
	*/
	public function getSelector(): array
	{
		return $this->getConfig()->get("selector");
	}

    /**
    * Get message type
    * @return string
    */
    public function getMessage(string $type): string
    {
        return $this->getConfig()->get("message")[$type];
    }

    /**
    * Get Form Message
    * @return mixed
    */
    public function getForm(string $type, string $var)
    {
        return $this->getConfig()->get("form")[$type][$var];
    }

    /**
    * retrieve all configured data for easy access
    * @return void
    */
    public function registerEnchantConfig(): void
    {
        $cache = ["enchantment" => [], "blacklist" => []];

        // Get All data from config
        // why strtolower? because in_array must be exact
        foreach ($this->getConfig()->get("enchantment") as $enchant => $data) {
            $cache["enchantment"][strtolower($enchant)] = $data;
        }

        foreach ($this->getConfig()->get("blacklist") as $name) {
            $cache["blacklist"][] = strtolower($name);
        }

        $this->enchantConfig = $cache["enchantment"];
        $this->addAllEnchant($cache["blacklist"]);
    }

    /**
    * Add all Available enchantment which is not blacklisted
    * @return void
    */
    public function addAllEnchant(array $blacklist): void
    {
        // Get all Available enchantment Vanilla
        $all = VanillaEnchantments::getAll();

        // add only if not Blacklisted
        foreach ($all as $name => $enchant) {
            if (!in_array(strtolower($name), $blacklist)) {
                // Display name for Button
                // _ replaced to space and 1 letter uppercase
                $display = str_replace("_", " ", $name);
                $display = explode(" ", $display);
                $displayname = ucfirst(strtolower($display[0]));
                if (isset($display[1])) {
                    $displayname .= " " . ucfirst(strtolower($display[1]));
                }

                $this->allEnchantment[$displayname] = ["name" => strtolower($name), "enchant" => $enchant];
            }
        }
        
        if($this->piggyCE) $this->addPiggyEnchant($blacklist);
    }
    
    /**
    * Add all Available enchantment which is not blacklisted
    * @return void
    */
    public function addPiggyEnchant(array $blacklist): void
    {
        // Get all Available enchantment PiggyCE
        $all = CustomEnchantManager::getEnchantments();

        // add only if not Blacklisted
        foreach ($all as $id => $enchant) {
        	$name = str_replace(" ", "_", $enchant->name); // Replace space name with underline
            if (!in_array(strtolower($name), $blacklist)) {
                // Display name for Button
                // _ replaced to space and 1 letter uppercase
                $display = str_replace("_", " ", $name);
                $display = explode(" ", $display);
                $displayname = ucfirst(strtolower($display[0]));
                if (isset($display[1])) {
                    $displayname .= " " . ucfirst(strtolower($display[1]));
                }

                $this->allEnchantment[$displayname] = ["name" => strtolower($name), "enchant" => $enchant];
            }
        }
    }

    /**
    * Get Item Flag from Item But No for ItemBlock
    * @return null|int
    */
    public static function getItemFlags(Item $item): ?int
    {
        if ($item instanceof Armor) {
            $slot = $item->getArmorSlot();
            if ($slot == ArmorInventory::SLOT_HEAD) {
                return ItemFlags::HEAD;
            } elseif ($slot == ArmorInventory::SLOT_CHEST) {
                return ItemFlags::TORSO;
            } elseif ($slot == ArmorInventory::SLOT_LEGS) {
                return ItemFlags::LEGS;
            } elseif ($slot == ArmorInventory::SLOT_FEET) {
                return ItemFlags::FEET;
            }
        } elseif ($item instanceof Sword) {
            return ItemFlags::SWORD;
        } elseif ($item instanceof Axe) {
            return ItemFlags::AXE;
        } elseif ($item instanceof Pickaxe) {
            return ItemFlags::PICKAXE;
        } elseif ($item instanceof Shovel) {
            return ItemFlags::SHOVEL;
        } elseif ($item instanceof Hoe) {
            return ItemFlags::HOE;
        } elseif ($item instanceof Shears) {
            return ItemFlags::SHEARS;
        } elseif ($item instanceof FlintSteel) {
            return ItemFlags::FLINT_AND_STEEL;
        } elseif ($item instanceof FishingRod) {
            return ItemFlags::FISHING_ROD;
        } elseif ($item instanceof Bow) {
            return ItemFlags::BOW;
        }
        return null;
    }

    /**
    * Get Blacklisted Item
    * @return bool
    */
    public function getBlacklistItem(Item|null $item, string $enchant): bool
    {
        if ($item == null) {
            return false;
        } // check if not item

        $blacklist = $this->getConfig()->get("blacklist-item");
        if (isset($blacklist[$enchant])) { // check if enchantment blacklist
            foreach ($blacklist[$enchant] as $itemall) {
                // Preparing get Item Legacy String
                $itemname = LegacyItemIdToStringIdMap::getInstance()->legacyToString($item->getId());
                $itemname = str_replace("minecraft:", "", $itemname);
                if ($itemname == $itemall) { // check if item same
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Get Available enchantment by Flag
    * Useless flags on PiggyCustonEnchants
    * @return array
    */
    public function getEnchantList(int $flag, Item $item = null): array
    {
        $result = [];
        foreach ($this->allEnchantment as $display => $enchant) {
            if (!$this->getBlacklistItem($item, $enchant["name"])) {// check if blacklisted item
				// Sorry for double method and double function
				if($enchant["enchant"] instanceof CustomEnchant){
					if ($item !== null) {
						if(PiggyUtils::itemMatchesItemType($item, $enchant["enchant"]->getItemType())){
                	 	   if ($this->mode) {
                     	   	$result[$display] = $enchant;
              	 	     } else {
                      	  	if (isset($this->enchantConfig[$enchant["name"]])) {
                            		$result[$display] = $enchant;
                            	}
                            }
                        }
                    }
				} else {
               	 if ($enchant["enchant"]->hasPrimaryItemType($flag) || $enchant["enchant"]->hasSecondaryItemType($flag)) {
                	    if ($this->mode) {
                     	   $result[$display] = $enchant;
              	      } else {
                        	if (isset($this->enchantConfig[$enchant["name"]])) {
                            	$result[$display] = $enchant;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
    * Get button content
    * @return string
    */
    public function getButton(int $index, array $data): string
    {
        // Get Button from Config
        $button = $this->getForm("buy-shop", "button");
        return str_replace(["{enchant}", "{price}"], [$data[0], $data[1]], $button[$index]);
    }

    /**
    * Get Enchantment data
    * @return array
    */
    public function getEnchantData(string $name): array
    {
        if (isset($this->enchantConfig[$name])) {
            return $this->enchantConfig[$name];
        }
        return $this->enchantConfig["default"];
    }

    /** @return bool */
    public function sendShop(Player $player): bool
    {
        $item = $player->getInventory()->getItemInHand();
        // Get Flags on Item
        $flag = $this->getItemFlags($item);
        if ($flag == null) {
            $flag = ItemFlags::NONE;
        }

        // List all enchantment by Flag
        $list = $this->getEnchantList($flag, $item);
        
        // sort enchant names alphabetically
		ksort($list);

        // Check if item cannot be enchant
		if(empty($list)){
			$player->sendMessage($this->getMessage("err-item"));
            return false;
         }
         
        // Create Form
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                $player->sendMessage($this->getMessage("exit"));
                return false;
            }
            $this->submit($player, $data);
            return true;
        });
        $form->setTitle($this->getForm("buy-shop", "title"));
        $form->setContent($this->getForm("buy-shop", "content"));
        foreach ($list as $display => $enchant) {
            // Get Price from Enchant
            $price = $this->getEnchantData($enchant["name"])["price"];
            // Button style
            $button = $this->getButton(0, [$display, $price]);
            $button2 = $this->getButton(1, [$display, $price]);
            $form->addButton($button . "\n" . $button2, -1, "", $display);
        }
        $player->sendForm($form);
        return true;
    }

    /**
    * Submit Enchant
    * @return bool
    */
    private function submit(Player $player, string $display): bool
    {
        // Preparing Enchant
        $enchant = $this->allEnchantment[$display];
        $enchantment = $enchant["enchant"];
        $encdata = $this->getEnchantData($enchant["name"]);

        // Player Item Hand
        $item = $player->getInventory()->getItemInHand();
        $nowlevel = (int) $item->hasEnchantment($enchantment) ? $item->getEnchantmentLevel($enchantment) : 0;
        $maxlevel = (int) $enchantment->getMaxLevel();
        $price = (int) $encdata["price"];

        // Preparing form
        $form = new CustomForm(function (Player $player, $data = null) use ($enchant, $encdata, $display) {
            if ($data === null) {
                $player->sendMessage($this->getMessage("exit"));
                return false;
            }
            // If Item Level Max
            if ($data[1] === null) {
                $player->sendMessage($this->getMessage("max"));
                return false;
            }

            $reqlevel = (int) $data[1]; // get requested level
            $price = (int) $encdata["price"] * $reqlevel; // multiply level by price
            $provider = $this->getProvider();
            // Callable from Provider
            $provider->setCallable(function (int $status) use ($player, $enchant, $encdata, $display, $price, $reqlevel) {
                if ($status == self::STATUS_SUCCESS) {
                    $item = $player->getInventory()->getItemInHand();
                    $msg = str_replace(
                        ["{price}", "{item}", "{enchant}"],
                        ["" . $price, $item->getVanillaName(), $display . " " . $this->numberToRoman($reqlevel)],
                        $this->getMessage("success")
                    );
                    $player->sendMessage($msg);
                    $this->enchantItem($player, $enchant["enchant"], $reqlevel);
                } else {
                    $msg = str_replace("{need}", "" . $price, $this->getMessage("enough"));
                    $player->sendMessage($msg);
                }
                return true;
            });
            // Process Transaction
            $provider->process($player, $price);
            return true;
        });
        $form->setTitle($this->getForm("submit", "title"));
        $form->addLabel(str_replace("{price}", "" . $encdata["price"], $this->getForm("submit", "content")));
        if ($nowlevel < $maxlevel) {
            $form->addSlider($this->getForm("submit", "slider"), ($nowlevel + 1), $maxlevel);
        } else {
            $form->addLabel("\n" . $this->getForm("submit", "max-content"));
        }
        $player->sendForm($form);
        return true;
    }

    /** @return string */
    public function numberToRoman(int $number): string
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

    /** @return void */
    private function enchantItem(Player $player, Enchantment $enchant, int $level): void
    {
        $item = $player->getInventory()->getItemInHand();
        $item->addEnchantment(new EnchantmentInstance($enchant, $level)); // Add Enchantment
        $player->getInventory()->setItemInHand($item); // Send back item to Player
    }
    
    /** @return void */
    private function registerProvider(string $provider): void
    {
        if ($provider == "EconomyAPI") {
            $call = new EconomyAPI();
        } elseif ($provider == "BedrockEconomy") {
            $call = new BedrockEconomy();
        } elseif ($provider == "Capital") {
            $call = new Capital();
        }
        
        $this->provider = $call;
    }

    /** @return Provider */
    public function getProvider(): Provider
    {
        return $this->provider;
    }
}
