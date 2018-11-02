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

namespace metowa1227\moneysystem\task\update;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat;

use metowa1227\moneysystem\logger\OriginalLogger;

class Update_Async extends AsyncTask
{
    public function __construct($path, $lang)
    {
        $this->path = $path;
        $this->lang = $lang;
        $this->logger = new OriginalLogger();
    }

    public function onRun()
    {
        $this->update();
    }

    private function update()
    {
        $newversion = @file_get_contents('http://metowa1227.s1001.xrea.com/MoneySystemNewVersion');
        if (!$newversion) {
            $this->logger->warning($this->getMessage("system.no-internet-connection"));
            $this->logger->warning($this->getMessage("system.no-internet-connection-2"));
        }
        if ($newversion > PLUGIN_VERSION) {
            $this->logger->notice($this->getMessage("system.update-required", array(substr($this->path, 0, -1))));
            $filename = $this->path . 'MoneySystem_version' . $newversion . '.phar';
            if (!file_exists($filename)) {
                $fullpath = 'http://metowa1227.s1001.xrea.com/MoneySystemDownload';
                @file_put_contents($filename, @file_get_contents($fullpath));
            }
            $this->update_description($newversion);
        } else {
            $this->logger->info($this->getMessage("system.latest-version"));
        }
    }

    private function update_description($version)
    {
        $description = @file_get_contents('http://metowa1227.s1001.xrea.com/MoneySystemNewVersionDescription');
        $this->logger->info($this->getMessage("system.update-info", array(PLUGIN_VERSION, $version)));
        $description = str_replace("???", "\n" . str_pad(" ", 66), $description);
        $this->logger->info($this->getMessage("system.update_description", array($description)));
    }

    public function getMessage(string $key, array $input = []) : string
    {
        $message = str_replace("[EOL]", "\n" . str_pad(" ", 66), $this->lang[$key]);
        $colortag = array("[COLOR: BLACK]", "[COLOR: DARK_BLUE]", "[COLOR: DARK_GREEN]", "[COLOR: DARK_AQUA]", "[COLOR: DARK_RED]", "[COLOR: DARK_PURPLE]", "[COLOR: GOLD]", "[COLOR: GRAY]", "[COLOR: DARK_GRAY]", "[COLOR: BLUE]", "[COLOR: GREEN]", "[COLOR: AQUA]", "[COLOR: RED]", "[COLOR: LIGHT_PURPLE]", "[COLOR: YELLOW]", "[COLOR: WHITE]", "[COLOR: OBFUSCATED]", "[COLOR: BOLD]", "[COLOR: STRIKETHROUGH]", "[COLOR: UNDERLINE]", "[COLOR: ITALIC]", "[COLOR: RESET]");
        $color = array(TextFormat::ESCAPE . "0", TextFormat::ESCAPE . "1", TextFormat::ESCAPE . "2", TextFormat::ESCAPE . "3", TextFormat::ESCAPE . "4", TextFormat::ESCAPE . "5", TextFormat::ESCAPE . "6", TextFormat::ESCAPE . "7", TextFormat::ESCAPE . "8", TextFormat::ESCAPE . "9", TextFormat::ESCAPE . "a", TextFormat::ESCAPE . "b", TextFormat::ESCAPE . "c", TextFormat::ESCAPE . "d", TextFormat::ESCAPE . "e", TextFormat::ESCAPE . "f", TextFormat::ESCAPE . "k", TextFormat::ESCAPE . "l", TextFormat::ESCAPE . "m", TextFormat::ESCAPE . "n", TextFormat::ESCAPE . "o", TextFormat::ESCAPE . "r");
        $message = str_replace($colortag, $color, $message);
        if (!empty($input)) {
            $count = (int) count($input);
            for ($i = 0; $i < $count; ++$i) {
                $search[] = '[TAG: NO.' . $i . ']';
                $replacement[] = $input[$i];
            }
            return str_replace($search, $replacement, $message);
        } else {
            return $message;
        }
    }
}
