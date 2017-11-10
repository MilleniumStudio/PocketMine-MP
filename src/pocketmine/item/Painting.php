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
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\StringTag;
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

                        $validMotives = array();
                        foreach (EntityPainting::$motives as $motive)
                        {
                            $isValid = true;
                            for ($x = 0; $x < $motive[1]; $x++)
                            {
                                for ($y = 0; $y < $motive[2]; $y++)
                                {
                                    if ($blockClicked->getSide($face, $x)->isTransparent() or $blockClicked->getSide(Vector3::SIDE_UP, $y)->isTransparent())
                                    {
                                        if ($blockReplace->getSide($face, $x)->isSolid() or $blockReplace->getSide(Vector3::SIDE_UP, $y)->isSolid())
                                        {
                                            $isValid = false;
                                        }
                                    }
                                }
                            }
                            if ($isValid)
                            {
                                $validMotives[] = $motive;
                            }
                        }

                        if (count($validMotives) > 0)
                        {
                            $motive = $validMotives[mt_rand(0, count($validMotives) - 1)];

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
                                new IntTag("TileX", $blockClicked->getFloorX()),
                                new IntTag("TileY", $blockClicked->getFloorY()),
                                new IntTag("TileZ", $blockClicked->getFloorZ())
                            ]);

                            $entity = Entity::createEntity(EntityPainting::NETWORK_ID, $level, $nbt);
                            $entity->spawnToAll();
                            return true;
                        }
		}

		return false;
	}

}