<?php

namespace onebone\economyapi\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MyMoneyCommand extends PluginCommand {
	public function __construct(EconomyAPI $plugin) {
		$desc = $plugin->getCommandMessage("mymoney");
		parent::__construct("mymoney", $plugin);
		$this->setDescription($desc["description"]);
		$this->setUsage($desc["usage"]);

		$this->setPermission("economyapi.command.mymoney");
	}

	public function execute(CommandSender $sender, string $label, array $params): bool {
		if(!$this->testPermission($sender)) {
			return false;
		}

		if($sender instanceof Player) {
			/** @var EconomyAPI $plugin */
			$plugin = $this->getPlugin();

			$money = $plugin->myMoney($sender);
			$sender->sendMessage($plugin->getMessage("mymoney-mymoney", [$money]));
			return true;
		}
		$sender->sendMessage(TextFormat::RED . "Please run this command in-game.");
		return true;
	}
}

