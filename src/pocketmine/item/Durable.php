<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;

abstract class Durable extends Item{

	/**
	 * Returns whether this item will take damage when used.
	 * @return bool
	 */
	public function isUnbreakable() : bool{
		$tag = $this->getNamedTagEntry("Unbreakable");
		return $tag !== null and $tag->getValue() !== 0;
	}

	/**
	 * Sets whether the item will take damage when used.
	 * @param bool $value
	 */
	public function setUnbreakable(bool $value = true){
		$this->setNamedTagEntry(new ByteTag("Unbreakable", $value ? 1 : 0));
	}

	/**
	 * Applies damage to the item.
	 * @param int $amount
	 *
	 * @return bool if any damage was applied to the item
	 */
	public function applyDamage(int $amount) : bool{
		if($this->isUnbreakable() or $this->isBroken()){
			return false;
		}

		//TODO: Unbreaking enchantment

		$this->meta += $amount;
		if($this->isBroken()){
			$this->pop();
		}

		return true;
	}

	/**
	 * Returns whether the item is broken.
	 * @return bool
	 */
	public function isBroken() : bool{
		return $this->meta >= $this->getMaxDurability();
	}
}