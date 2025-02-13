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

namespace onebone\economyapi\currency;

use pocketmine\Player;

interface Currency {
	public function getName(): string;

	/**
	 * Returns if the currency is available to player. Availability
	 * may vary on the world where player is in or position or
	 * something else.
	 * @param Player $player
	 * @return bool
	 */
	public function isCurrencyAvailable(Player $player): bool;

	/**
	 * Returns default money which is given to player for the first
	 * time in forever. It may be overridden when server administrator
	 * sets the default value.
	 * @return float
	 */
	public function getDefaultMoney(): float;

	/**
	 * Returns the symbol of currency
	 * @return string
	 */
	public function getSymbol(): string;

	/**
	 * Formats money into string
	 * @param float $money
	 * @return string
	 */
	public function format(float $money): string;

	/**
	 * Stringify money into string
	 * @param float $money
	 * @return string
	 */
	public function stringify(float $money): string;
}
