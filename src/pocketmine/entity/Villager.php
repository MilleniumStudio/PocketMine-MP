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

namespace pocketmine\entity;

use pocketmine\nbt\tag\IntTag;

class Villager extends Creature implements NPC, Ageable{
	const PROFESSION_FARMER = 0;
	const PROFESSION_LIBRARIAN = 1;
	const PROFESSION_PRIEST = 2;
	const PROFESSION_BLACKSMITH = 3;
	const PROFESSION_BUTCHER = 4;

	const NETWORK_ID = self::VILLAGER;

	public $width = 0.6;
	public $height = 1.8;

	public function getName() : string{
		return "Villager";
	}

	protected function initEntity(){
		parent::initEntity();

		/** @var int $profession */
		$profession = $this->namedtag["Profession"] ?? self::PROFESSION_FARMER;

		if($profession > 4 or $profession < 0){
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Profession = new IntTag("Profession", $this->getProfession());
	}

	/**
	 * Sets the villager profession
	 *
	 * @param int $profession
	 */
	public function setProfession(int $profession){
		$this->setDataProperty(self::DATA_VARIANT, self::DATA_TYPE_INT, $profession);
	}

	public function getProfession() : int{
		return $this->getDataProperty(self::DATA_VARIANT);
	}

	public function isBaby() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}
}
