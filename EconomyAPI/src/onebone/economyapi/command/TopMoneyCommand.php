<?php

/*
 * EconomyS, the massive economy plugin with many features for PocketMine-MP
 * Copyright (C) 2013-2017  onebone <jyc00410@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace onebone\economyapi\command;

use onebone\economyapi\EconomyAPI;
use onebone\economyapi\task\SortTask;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class TopMoneyCommand extends PluginCommand {
	public function __construct(EconomyAPI $plugin) {
		$desc = $plugin->getCommandMessage("topmoney");
		parent::__construct("topmoney", $plugin);
		$this->setDescription($desc["description"]);
		$this->setUsage($desc["usage"]);

		$this->setPermission("economyapi.command.topmoney");
	}

	public function execute(CommandSender $sender, string $label, array $params): bool {
		if(!$this->testPermission($sender)) return false;

		$page = (int) array_shift($params);

		/** @var EconomyAPI $plugin */
		$plugin = $this->getPlugin();
		$server = $plugin->getServer();

		$banned = [];
		foreach($server->getNameBans()->getEntries() as $entry) {
			if($plugin->accountExists($entry->getName())) {
				$banned[] = $entry->getName();
			}
		}
		$ops = [];
		foreach($server->getOps()->getAll() as $op) {
			if($plugin->accountExists($op)) {
				$ops[] = $op;
			}
		}

		$task = new SortTask($sender->getName(), $plugin->getAllMoney(), $plugin->getPluginConfig()->getAddOpAtRank(), $page, $ops, $banned);
		$server->getAsyncPool()->submitTask($task);

		return true;
	}
}
