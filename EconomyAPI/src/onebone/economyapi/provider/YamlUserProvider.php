<?php

/*
 * EconomyS, the massive economy plugin with many features for PocketMine-MP
 * Copyright (C) 2013-2019  onebone <jyc00410@gmail.com>
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

namespace onebone\economyapi\provider;

use onebone\economyapi\EconomyAPI;
use onebone\economyapi\UserInfo;
use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class YamlUserProvider implements UserProvider, Listener {
	/** @var $api EconomyAPI */
	private $api;
	private $data = [];

	private $root;

	private $defaultSchema;

	public function __construct(EconomyAPI $api) {
		$this->api = $api;

		$this->root = $this->api->getDataFolder() . 'users' . DIRECTORY_SEPARATOR;
		if(!file_exists($this->root)) {
			mkdir($this->root);
		}

		$this->defaultSchema = [
			'language' => $this->api->getPluginConfig()->getDefaultLanguage()
		];

		$api->getServer()->getPluginManager()->registerEvents($this, $api);
	}

	public function getName(): string {
		return 'Yaml';
	}

	public function create(string $username): bool {
		$username = strtolower($username);
		$base = $this->root . $username[0] . DIRECTORY_SEPARATOR;
		if(!file_exists($base)) {
			mkdir($base);
		}

		$path = $base . $username . '.yml';
		if(!is_file($path)) {
			yaml_emit_file($path, $this->defaultSchema);
			return true;
		}

		return false;
	}

	public function delete(string $username): bool {
		$username = strtolower($username);
		$base = $this->root . $username[0] . DIRECTORY_SEPARATOR;
		if(!file_exists($base)) {
			return false;
		}

		$path = $base . $username . '.yml';
		if(is_file($path)) {
			unlink($path);
			return true;
		}

		return false;
	}

	public function exists(string $username): bool {
		$username = strtolower($username);
		$path = $this->root . $username[0] . DIRECTORY_SEPARATOR . $username . '.yml';
		return is_file($path);
	}

	public function setLanguage(string $username, string $lang): bool {
		$username = strtolower($username);
		if(!$this->api->hasLanguage($lang)) return false;

		$this->setProperty($username, 'language', $lang);
		return true;
	}

	public function setProperty(string $username, string $key, $val) {
		$username = strtolower($username);

		if(isset($this->data[$username])) {
			$this->data[$username][$key] = $val;

			$this->savePlayer($username);
		}else{
			$data = $this->readPlayer($username);
			$data[$key] = $val;

			$this->savePlayer($username, $data);
		}
	}

	private function loadPlayer(string $username) {
		$this->data[$username] = $this->readPlayer($username);
	}

	private function validate(&$data): bool {
		if(!isset($data['language'])) return false;

		if(!is_string($data['language'])) return false;

		if(!$this->api->hasLanguage($data['language'])) {
			$data['language'] = $this->api->getPluginConfig()->getDefaultLanguage();
		}

		return true;
	}

	private function unloadPlayer(string $username) {
		if(isset($this->data[$username])) {
			unset($this->data[$username]);
		}
	}

	private function readPlayer(string $username): array {
		$username = strtolower($username);

		$base = $this->root . $username[0] . DIRECTORY_SEPARATOR;
		if(!file_exists($base)) {
			mkdir($base);
		}

		$path = $base . $username . '.yml';
		if(!is_file($path)) {
			yaml_emit_file($path, ['language' => $this->api->getPluginConfig()->getDefaultLanguage()]);
		}

		$data = yaml_parse_file($path);
		$this->validate($data);

		return $data;
	}

	private function savePlayer(string $username, $data = null) {
		$username = strtolower($username);

		if($data === null) {
			if(isset($this->data[$username])) {
				$data = $this->data[$username];
			}else{
				return;
			}
		}

		$base = $this->root . $username[0] . DIRECTORY_SEPARATOR;
		if(!file_exists($base)) {
			mkdir($base);
		}

		$path = $base . $username . '.yml';
		yaml_emit_file($path, $data);
	}

	public function getLanguage(string $username): string {
		$info = $this->getUserInfo($username);

		return $info->language;
	}

	public function getUserInfo(string $username): UserInfo {
		$username = strtolower($username);

		if(isset($this->data[$username])) {
			$data = $this->data[$username];
		}elseif($this->exists($username)) {
			$data = $this->readPlayer($username);
		}else{
			$data = $this->defaultSchema;
		}

		return new UserInfo($username, $data['language']);
	}

	public function save() {
	}

	public function close() {
		HandlerList::unregisterAll($this);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) {
		$this->loadPlayer(strtolower($event->getPlayer()->getName()));
	}

	public function onPlayerQuit(PlayerQuitEvent $event) {
		$this->unloadPlayer(strtolower($event->getPlayer()->getName()));
	}
}
