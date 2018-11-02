<?php

/*
*  __  __       _                             __    ___    ___   _______
* |  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
* | |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
* | |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
* |_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
* All this program is made by hand of metowa1227.
* I certify here that all authorities are in metowa1227.
* Expiration date of certification: unlimited
* Secondary distribution etc are prohibited.
* The update is also done by the developer.
* This plugin is a developer API plugin to make it easier to write code.
* When using this plug-in, be sure to specify it somewhere.
* Warning if violation is confirmed.
*
* Developer: metowa1227
*/

/*
    Plugin description

    - CONTENTS
        - Lightweight, fast and multifunctional economic system.

    - AUTHOR
        - metowa1227 (MoneySystemAPI)

    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - Altay 3.0.6+dev for Minecraft: PE v1.5.0 (protocol version 274)
        - PHP 7.2.1 64bit supported version
*/

namespace metowa1227\moneysystem\logger;

class OriginalLogger
{
    public function __construct()
    {
        $this->white = pack("c", 0x1B) . "[1;37m";
        $this->gray = pack("c", 0x1B) . "[0;37m";
        $this->darkgray = pack("c", 0x1B) . "[1;30m";
        $this->black = pack("c", 0x1B) . "[0;30m";
        $this->green = pack("c", 0x1B) . "[1;32m";
        $this->darkgreen = pack("c", 0x1B) . "[0;32m";
        $this->line = pack("c", 0x1B) . "[4m";
        $this->red = pack("c", 0x1B) . "[1;31m";
        $this->darkred = pack("c", 0x1B) . "[0;31m";
        $this->aqua = pack("c", 0x1B) . "[1;36m";
        $this->darkaqua = pack("c", 0x1B) . "[0;36m";
        $this->yellow = pack("c", 0x1B) . "[1;33m";
        $this->gold = pack("c", 0x1B) . "[0;33m";
        $this->blue = pack("c", 0x1B) . "[1;34m";
        $this->darkblue = pack("c", 0x1B) . "[0;34m";
        $this->purple = pack("c", 0x1B) . "[1;35m";
        $this->darkpurple = pack("c", 0x1B) . "[0;35m";
        $this->reset = pack("c", 0x1B) . "[1;0m";
        $this->obfuscated = pack("c", 0x1B) . "[1;7m";
        $this->bold = pack("c", 0x1B) . "[1m";
        $this->strikethrough = " ";
        $this->italic = "";
        $this->sender = [
            $this->aqua . "MoneySystemCore",
            $this->green . "MoneySystemAPI" . $this->reset . " ",
            $this->yellow . "MoneySystemLand",
            $this->purple . "MoneySystemJob" . $this->reset . " ",
            $this->blue . "MoneySystemShop",
            $this->blue . "MoneySystemSell"
        ];
        $this->prefix = $this->yellow . " [MoneySystem] " . $this->reset;
        $this->debug_prefix = $this->reset . " [MoneySystem] ";
    }

    public function info(string $text, int $sender = 0)
    {
        $text = $this->convertString($text);
        echo $this->aqua . "[" . date('H:i:s') . "]" . $this->prefix . $this->white . str_pad("[INFO]", 10) . $this->reset . ">>> " . $this->line . $this->sender[$sender] . $this->reset . " " . $this->reset . ">>> " . $text . PHP_EOL;
    }

    public function notice(string $text, int $sender = 0)
    {
        $text = $this->convertString($text);
        echo $this->aqua . "[" . date('H:i:s') . "]" . $this->prefix . $this->aqua . str_pad("[NOTICE]", 10) . $this->reset . ">>> " . $this->line . $this->sender[$sender] . $this->reset . " " . $this->reset . ">>> " . $this->aqua . $text . PHP_EOL;
    }

    public function error(string $text, int $sender = 0)
    {
        $text = $this->convertString($text);
        echo $this->aqua . "[" . date('H:i:s') . "]" . $this->prefix . $this->red . str_pad("[ERROR]", 10) . $this->reset . ">>> " . $this->line . $this->sender[$sender] . $this->reset . " " . $this->reset . ">>> " . $this->red . $text . PHP_EOL;
    }

    public function warning(string $text, int $sender = 0)
    {
        $text = $this->convertString($text);
        echo $this->aqua . "[" . date('H:i:s') . "]" . $this->prefix . $this->yellow . str_pad("[WARNING]", 10) . $this->reset . ">>> " . $this->line . $this->sender[$sender] . $this->reset . " " . $this->reset . ">>> " . $this->yellow . $text . PHP_EOL;
    }

    public function debug(string $text, int $sender = 0)
    {
        $text = $this->convertString($text);
        echo $this->darkred . "[" . date('H:i:s') . "]" . $this->debug_prefix . str_pad("[DEBUG]", 10) . ">>> " . $this->line . $this->sender[$sender] . $this->reset . " " . $this->reset . ">>> " . $text . PHP_EOL;
    }

    public function convertString(string $text) : string
    {
        $search = array("§0", "§1", "§2", "§3", "§4", "§5", "§6", "§7", "§8", "§9", "§a", "§b", "§c", "§d", "§e", "§f", "§k", "§l", "§m", "§n", "§o", "§r");
        $replace = array($this->black, $this->darkblue, $this->darkgreen, $this->darkaqua, $this->darkred, $this->darkpurple, $this->gold, $this->gray, $this->darkgray, $this->blue, $this->green, $this->aqua, $this->red, $this->purple, $this->yellow, $this->white, $this->obfuscated, $this->bold, $this->strikethrough, $this->line, $this->italic, $this->reset);
        return str_replace($search, $replace, $text);
    }
}
