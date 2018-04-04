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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Ladder extends Transparent{

	protected $id = self::LADDER;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Ladder";
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getHardness() : float{
		return 0.4;
	}

	public function canClimb() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		$entity->resetFallDistance();
		$entity->onGround = true;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$f = 0.1875;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		if($this->meta === 2){
			return new AxisAlignedBB(
				(float)$this->x,
				(float)$this->y,
				(float)$this->z + 1 - $f,
				(float)$this->x + 1,
				(float)$this->y + 1,
				(float)$this->z + 1
			);
		}elseif($this->meta === 3){
			return new AxisAlignedBB(
				(float)$this->x,
				(float)$this->y,
				(float)$this->z,
				(float)$this->x + 1,
				(float)$this->y + 1,
				(float)$this->z + $f
			);
		}elseif($this->meta === 4){
			return new AxisAlignedBB(
				(float)$this->x + 1 - $f,
				(float)$this->y,
				(float)$this->z,
				(float)$this->x + 1,
				(float)$this->y + 1,
				(float)$this->z + 1
			);
		}elseif($this->meta === 5){
			return new AxisAlignedBB(
				(float)$this->x,
				(float)$this->y,
				(float)$this->z,
				(float)$this->x + $f,
				(float)$this->y + 1,
				(float)$this->z + 1
			);
		}

		return new AxisAlignedBB(
			$this->x + $minX,
			$this->y,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + 1,
			$this->z + $maxZ
		);
	}


	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if(!$blockClicked->isTransparent()){
			$faces = [
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5
			];
			if(isset($faces[$face])){
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($blockReplace, $this, true, true);

				return true;
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide($this->meta ^ 0x01)->isSolid()){ //Replace with common break method
			$this->level->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int{
		return 0;
	}
}
