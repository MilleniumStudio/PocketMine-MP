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

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use \pocketmine\nbt\tag\IntTag;
use \pocketmine\nbt\tag\ByteTag;
use \pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Painting as EntityPainting;

class Painting extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::PAINTING, $meta, "Painting");
	}

	public function onActivate(Level $level, Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if(!$blockClicked->isTransparent() and $face > 1 and !$blockReplace->isSolid()){
			$faces = [
				2 => 2, // SIDE_NORTH
				3 => 0, // SIDE_SOUTH
				4 => 1, // SIDE_WEST
				5 => 3, // SIDE_EAST
			];
			$motives = [
				// Motive Width Height
				["Kebab", 1, 1],
				["Aztec", 1, 1],
				["Alban", 1, 1],
				["Aztec2", 1, 1],
				["Bomb", 1, 1],
				["Plant", 1, 1],
				["Wasteland", 1, 1],
				["Wanderer", 1, 2],
				["Graham", 1, 2],
				["Pool", 2, 1],
				["Courbet", 2, 1],
				["Sunset", 2, 1],
				["Sea", 2, 1],
				["Creebet", 2, 1],
				["Match", 2, 2],
				["Bust", 2, 2],
				["Stage", 2, 2],
				["Void", 2, 2],
				["SkullAndRoses", 2, 2],
                                ["Wither", 2, 2],
				["Fighters", 4, 2],
				["Skeleton", 4, 3],
				["DonkeyKong", 4, 3],
				["Pointer", 4, 4],
				["Pigscene", 4, 4],
				["Flaming Skull", 4, 4],
			];
			$motive = $motives[mt_rand(0, count($motives) - 1)];

                        // TODO calculate space

			$data = [
				"facing" => $faces[$face],
				"motive" => $motive
			];
                        $nbt = new CompoundTag("", [
                            new ByteTag("Direction", $faces[$face]),
                            new StringTag("Motive", $motive[0]),
                            new ListTag("Pos", [
                                new DoubleTag("", $blockClicked->getX()),
                                new DoubleTag("", $blockClicked->getY()),
                                new DoubleTag("", $blockClicked->getZ())
                                    ]),
                            new ListTag("Motion", [
                                new DoubleTag("", 0),
                                new DoubleTag("", 0),
                                new DoubleTag("", 0)
                                    ]),
                            new ListTag("Rotation", [
                                new FloatTag("", $faces[$face] * 90),
                                new FloatTag("", 0)
                                    ]),
                            new IntTag("TileX", $blockClicked->getX()),
                            new IntTag("TileY", $blockClicked->getY()),
                            new IntTag("TileZ", $blockClicked->getZ())
                        ]);

                    $entity = Entity::createEntity(EntityPainting::NETWORK_ID, $level, $nbt, $data);
                    $entity->setCanSaveWithChunk(false);
                    $entity->spawnToAll();
                    return true;
		}

		return false;
	}

}