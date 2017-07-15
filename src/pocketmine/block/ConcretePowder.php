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

use pocketmine\level\Level;
use pocketmine\level\Position;

class ConcretePowder extends Fallable{

	public function onUpdate(int $type){
		if($type === Level::BLOCK_UPDATE_NORMAL and ($block = $this->checkAdjacentWater($this)) !== null){
			$this->level->setBlock($this, $block);
			return $type;
		}

		return parent::onUpdate($type);
	}

	/**
	 * @param Position $fallingBlock
	 *
	 * @return Block|null
	 */
	public function tickFalling(Position $fallingBlock){
		return $this->checkAdjacentWater($fallingBlock->getLevel()->getBlock($fallingBlock));
	}

	/**
	 * @param Block $block
	 *
	 * @return Block|null
	 */
	private function checkAdjacentWater(Block $block){
		for($i = 1; $i < 6; ++$i){ //Do not check underneath
			if($block->getSide($i) instanceof FlowingWater){
				return Block::get(Block::CONCRETE, $this->meta);
			}
		}

		return null;
	}
}