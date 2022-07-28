<?php

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Transaction\Shop;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Manager\EnchantManager;
use muqsit\invmenu\InvMenu;

use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use function array_chunk;
use function ksort;
use function str_replace;

class GUI
{

	/** @var array $gui */
	private $gui = [];
	/** @var int $remainingPage */
	private $remainingPage = 0;
	/** @var array $page */
	private $page = [];

	public function __construct(){
		$this->gui = EconomyEnchant::getInstance()->getConfig()->get("gui");
	}

	public function sendShop(Player $player, int $page, ?InvMenu $invmenus = null) : void{
		$item = $player->getInventory()->getItemInHand();

		// Creating Page
		$this->createPage($item);

		// Checking if Page is Empty
		if(empty($this->page)){
			$player->sendMessage(EconomyEnchant::getMessage("err-item"));
			return;
		}

		// Checking if new Page
		if($invmenus !== null){
			$invmenus->getInventory()->setContents($this->getPage($this->remainingPage));
		} else {
			// Creating GUI
			$invmenu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
			$invmenu->setName($this->gui["buy-shop"]["title"]);
			$invmenu->getInventory()->setContents($this->getPage($page));
			$invmenu->setListener(function(InvMenuTransaction $transaction) use($page, $invmenu) : InvMenuTransactionResult{
				$player = $transaction->getPlayer();
			 $item = $transaction->getItemClicked();
			  $name = $item->getName();

				if($name == ("§fNext To Page: " . ($this->remainingPage + 1))){
					// Next Page
					$this->remainingPage += 1;
					$this->sendShop($player, $this->remainingPage, $invmenu);

					return $transaction->discard();
				} elseif($name == ("§fBack To Page: " . ($this->remainingPage - 1))){
					// Previous Page
					$this->remainingPage -= 1;
					$this->sendShop($player, $this->remainingPage, $invmenu);

					return $transaction->discard();
				} elseif($name == ("§fPage: " . $this->remainingPage)){
					return $transaction->discard();
				}

			  $this->submit($player, $item, $invmenu, $this->remainingPage);
			 return $transaction->discard();
		  });
			$invmenu->setInventoryCloseListener(function (Player $player) : void {
				$player->sendMessage(EconomyEnchant::getMessage("exit"));
			});

			// Sending gui To Player
			$invmenu->send($player);
		}
	}

	private function submit(Player $player, Item $itemBook, InvMenu $invmenu, int $page){
		// Variable
		$tag = $itemBook->getNamedTag();
		$display = $tag->getString("displayenchant");
	  $price = $tag->getInt("price");
		$nextlevel = $tag->getInt("nextlevel");
	  $enchant = $this->page[$page]["enchant"][$display];

		// Regenerating InvMenu
		$invmenu->setName($this->gui["submit"]["title"]);

		// Creating Item Content
		$content = [];

		// Buy item
		$paper = VanillaItems::PAPER();
		$paper->setCustomName(str_replace(["{enchant}", "{level}"], [$display, EnchantManager::numberToRoman($nextlevel)], $this->gui["submit"]["buy"]["name"]));
		$paper->setLore(str_replace("{price}", "" . $price, $this->gui["submit"]["buy"]["lore"]));
		$content[28] = $paper;

		// Cancel Item
		$arrow = VanillaItems::ARROW();
		$arrow->setCustomName(str_replace(["{enchant}", "{level}"], [$display, EnchantManager::numberToRoman($nextlevel)], $this->gui["submit"]["cancel"]["name"]));
		$arrow->setLore(str_replace("{price}", "" . $price, $this->gui["submit"]["cancel"]["lore"]));
		$content[34] = $arrow;

		$invmenu->getInventory()->setContents($content);
		$invmenu->setListener(function(InvMenuTransaction $transaction) use($display, $price, $nextlevel, $enchant) : InvMenuTransactionResult{
			$player = $transaction->getPlayer();
		  $item = $transaction->getItemClicked();

		  // Identify Item Paper
		  if($item->getId() == 339){
			$provider = EconomyEnchant::getInstance()->getProvider();
				$provider->process($player, $price, $display, function (int $status) use ($player, $enchant, $display, $price, $nextlevel) {
					if ($status == EconomyEnchant::STATUS_SUCCESS) {
						$item = $player->getInventory()->getItemInHand();
						$msg = str_replace(
							["{price}", "{item}", "{enchant}"],
							["" . $price, $item->getVanillaName(), $display . " " . EnchantManager::numberToRoman($nextlevel)],
							EconomyEnchant::getMessage("success")
						);
						$player->sendMessage($msg);
						EnchantManager::enchantItem($player, $enchant, $nextlevel);
						EnchantManager::sendSound($player);

						// Removing InvMenu
						InvMenuHandler::getPlayerManager()->get($player)->removeCurrentMenu();
					} else {
						// Removing InvMenu
						InvMenuHandler::getPlayerManager()->get($player)->removeCurrentMenu();

						$msg = str_replace("{need}", "" . $price, EconomyEnchant::getMessage("enough"));
						$player->sendMessage($msg);
					}
				});
			} else {
				// Removing InvMenu
				InvMenuHandler::getPlayerManager()->get($player)->removeCurrentMenu();

				$player->sendMessage(EconomyEnchant::getMessage("exit"));
			}
		  return $transaction->discard();
	  });
	}

	/** @var Item $item */
	public function createPage(Item $item) : void{
		$list = EnchantManager::getEnchantByItem($item);

		// Split array if array is more than 26 due to Big Chest Inventory slot
		$splited = array_chunk($list, 26, true);

		// Preparing Page
		$page = [];

		foreach($splited as $index => $splist){
			foreach($splist as $encdata){
				// Why using ItemFactory because in VanillaItems no Enchanted Book
				$itemBook = ItemFactory::getInstance()->get(403, 0, 1);

				// Getting next Level
				$nowlevel = (int) $item->hasEnchantment($encdata["enchant"]) ? $item->getEnchantmentLevel($encdata["enchant"]) : 0;

				// Removing enchant when is Max Level
				if($encdata["enchant"]->getMaxLevel() == $nowlevel) continue;

				// Upping Level
				$nowlevel += 1;

				// Tag
				$tag = CompoundTag::create();
				$tag->setTag("price", new IntTag($encdata["price"] * $nowlevel));
				$tag->setTag("nextlevel", new IntTag($nowlevel));
				$tag->setTag("displayenchant", new StringTag($encdata["display"]));
				$itemBook->setNamedTag($tag);

				$itemBook->setCustomName(str_replace(["{enchant}", "{level}"], [$encdata["display"], EnchantManager::numberToRoman($nowlevel)], $this->gui["buy-shop"]["name"]));
				$itemBook->setLore(str_replace("{price}", "" . $encdata["price"], $this->gui["buy-shop"]["lore"]));

				$page[$index]["items"][$encdata["display"]] = $itemBook;
				$page[$index]["enchant"][$encdata["display"]] = $encdata["enchant"];
			}
		}

		$this->page = $page;
	}

	public function getPage(int $page = 0, bool $item = true) : array{
		if(!$item) return $this->page[$page];

		$items = [];
		$remain = 0;

		// Getting page and Sort by Alphabetically
		$list = $this->page[$page]["items"];

		// Sort
		ksort($list);

		foreach($list as $display => $itm){
			$items[$remain] = $itm;
			$remain++;
		}

		// Checking if Previous page available
		if(isset($this->page[($page - 1)])){
			// Previous Page Item
			$pre = VanillaItems::ARROW();
			$pre->setCustomName("§fBack To Page: " . ($this->remainingPage - 1));
			$items[46] = $pre;
		}
		// Checking if Next page available
		if(isset($this->page[($page + 1)])){
			// Previous Page Item
			$next = VanillaItems::ARROW();
			$next->setCustomName("§fNext To Page: " . ($this->remainingPage + 1));
			$items[52] = $next;
		}

		// For information Page
		$info = VanillaItems::BOOK();
		$info->setCustomName("§fPage: " . $this->remainingPage);
		$items[49] = $info;
		return $items;
	}
}
