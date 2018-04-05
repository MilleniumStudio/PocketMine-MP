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

namespace pocketmine\level\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;

abstract class Particle extends Vector3{

	public const TYPE_BUBBLE = 1;
	public const TYPE_CRITICAL = 2;
	public const TYPE_BLOCK_FORCE_FIELD = 3;
	public const TYPE_SMOKE = 4;
	public const TYPE_EXPLODE = 5;
	public const TYPE_EVAPORATION = 6;
	public const TYPE_FLAME = 7;
	public const TYPE_LAVA = 8;
	public const TYPE_LARGE_SMOKE = 9;
	public const TYPE_REDSTONE = 10;
	public const TYPE_RISING_RED_DUST = 11;
	public const TYPE_ITEM_BREAK = 12;
	public const TYPE_SNOWBALL_POOF = 13;
	public const TYPE_HUGE_EXPLODE = 14;
	public const TYPE_HUGE_EXPLODE_SEED = 15;
	public const TYPE_MOB_FLAME = 16;
	public const TYPE_HEART = 17;
	public const TYPE_TERRAIN = 18;
	public const TYPE_SUSPENDED_TOWN = 19, TYPE_TOWN_AURA = 19;
	public const TYPE_PORTAL = 20;
	public const TYPE_SPLASH = 21, TYPE_WATER_SPLASH = 21;
	public const TYPE_WATER_WAKE = 22;
	public const TYPE_DRIP_WATER = 23;
	public const TYPE_DRIP_LAVA = 24;
	public const TYPE_FALLING_DUST = 25, TYPE_DUST = 25;
	public const TYPE_MOB_SPELL = 26;
	public const TYPE_MOB_SPELL_AMBIENT = 27;
	public const TYPE_MOB_SPELL_INSTANTANEOUS = 28;
	public const TYPE_INK = 29;
	public const TYPE_SLIME = 30;
	public const TYPE_RAIN_SPLASH = 31;
	public const TYPE_VILLAGER_ANGRY = 32;
	public const TYPE_VILLAGER_HAPPY = 33;
	public const TYPE_ENCHANTMENT_TABLE = 34;
	public const TYPE_TRACKING_EMITTER = 35;
	public const TYPE_NOTE = 36;
	public const TYPE_WITCH_SPELL = 37;
	public const TYPE_CARROT = 38;
	//39 unknown
	public const TYPE_END_ROD = 40;
	public const TYPE_DRAGONS_BREATH = 41;
	public const TYPE_SPIT = 42;
	public const TYPE_TOTEM = 43;
	public const TYPE_FOOD = 44;

	/**
	 * @return DataPacket|DataPacket[]
	 */
	abstract public function encode();

}
