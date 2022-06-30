# General

EconomyEnchant Shop For Pocketmine 4.0.0, With Many features

## Commands

Commands | Default
--- | ---
`/eshop` | True

## Feature
- Support Multiple Economys
- Support PiggyCustomEnchants
- Support EnchantTable Direct
- Auto Check Enchant Available in Hand
- Blacklist System
- Max Level Enchant
- Customable Message
- Customable Form

## Supported Economy Providers

* [EconomyAPI](https://poggit.pmmp.io/p/EconomyAPI) by onebone/poggit-orphanage
* [BedrockEconomy](https://poggit.pmmp.io/p/BedrockEconomy) by cooldogedev
* [Capital](https://poggit.pmmp.io/p/Capital/) By SOF3


# To-Dos
- Add more detail transaction on Capital Label

# Config

``` YAML

---
# Your Economy plugin name
# Available: BedrockEconomy, EconomyAPI, Capital, Auto
economy: "Auto"

# Selector for Capital Economys
selector: []

# Can EnchantTable Redirect to EconomyEnchant
enchant-table: true

# Form
form:
 # Buy Menu
 buy-shop:
  # Title for BuyShop
  title: "EnchantShop"
  # Content for BuyShop
  content: "Select Enchantment:"
  # Button Style
  # Tag: {price} Price Enchantment, {enchant} Name Of Enchantment
  button:
   # Only can 0-1
   0: "{enchant}"
   1: "{price}"
 # Submit Menu
 submit:
  # Title for BuyShop
  title: "EnchantShop"
  # Content
  # Tag: {price} Price of Enchantment
  content: "§aYou will pay {price} Per Level"
  # Max Content
  max-content: "§aYour item has reached the level limit!"
  # Slider Content
  slider: "Level"

# Message
message:
 # Cannot Enchant On This Item
 err-item: "§cYou cannot add Enchantment to This Item!"
 # Exit Message
 exit: "§aThank you for visiting!"
 # Successfully Buy Enchant
 # Tag: {enchant} Name Enchantment, {item} Name Item, {price} Price Enchantment
 success: "§aSuccessfully Enchant {enchant} to {item}, with Price {price}"
 # Error Max Enchant
 max: "§cEnchant failed!, Your item is reached Max Level"
 # Error Money not Enough
 # Tag: {need} Needed Money
 enough: "§cYour money is not enough need {need}"

# Mode
# If false Enchantment those that are not set will not be in the shop
# If true Enchantment Auto added and price will be to Default ( Not Setted )
mode: true

# Enchantment
enchantment:
 # Name Enchantment
 sharpness:
  # Price
  price: 1000
 # Default, If the enchantment is not set above
 # it will be redirected here
 default:
  # Price
  price: 10000
  
# Blacklist Enchantment from Shop
# if you want blacklist just add
# example: ["sharpness", "silk_touch"] or ["sharpness"]
blacklist: []

# Blacklist Enchantment from Item
# if you want blacklist just add
# example: 
# blacklist:
#  sharpness: ["diamond_sword"]
# If you want add meta just item_name:meta
blacklist-item: []
...
```

# Additional Notes

- If you find bugs or want to give suggestions, please visit [here](https://github.com/MulqiGaming64/EconomyEnchant/issues)
