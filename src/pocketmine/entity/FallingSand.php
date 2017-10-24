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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Fallable;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;

class FallingSand extends Entity{
	const NETWORK_ID = self::FALLING_BLOCK;

	public $width = 0.98;
	public $height = 0.98;

	protected $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	/** @var Block */
	protected $block;

	public $canCollide = false;

	protected function initEntity(){
		parent::initEntity();

		$blockId = 0;
		$damage = 0;

		if(isset($this->namedtag->TileID)){
			$blockId = (int) $this->namedtag["TileID"];
		}elseif(isset($this->namedtag->Tile)){
			$blockId = (int) $this->namedtag["Tile"];
			$this->namedtag["TileID"] = new IntTag("TileID", $blockId);
		}

		if(isset($this->namedtag->Data)){
			$damage = (int) $this->namedtag["Data"];
		}

		if($blockId === 0){
			$this->close();
			return;
		}

		$this->block = BlockFactory::get($blockId, $damage);

		$this->setDataProperty(self::DATA_VARIANT, self::DATA_TYPE_INT, $this->block->getId() | ($this->block->getDamage() << 8));
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function attack(EntityDamageEvent $source){
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){
			$pos = Position::fromObject($this->add(-$this->width / 2, $this->height, -$this->width / 2)->floor(), $this->getLevel());

			$this->block->position($pos);

			$blockTarget = null;
			if($this->block instanceof Fallable){
				$blockTarget = $this->block->tickFalling();
			}

			if($this->onGround or $blockTarget !== null){
				$this->kill();

				$block = $this->level->getBlock($pos);
				if($block->getId() > 0 and $block->isTransparent() and !$block->canBeReplaced()){
					//FIXME: anvils are supposed to destroy torches
					$this->getLevel()->dropItem($this, ItemFactory::get($this->getBlock(), $this->getDamage()));
				}else{
					$this->server->getPluginManager()->callEvent($ev = new EntityBlockChangeEvent($this, $block, $blockTarget ?? $this->block));
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($pos, $ev->getTo(), true);
					}
				}
				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

	public function getBlock(){
		return $this->block->getId();
	}

	public function getDamage(){
		return $this->block->getDamage();
	}

	public function saveNBT(){
		$this->namedtag->TileID = new IntTag("TileID", $this->block->getId());
		$this->namedtag->Data = new ByteTag("Data", $this->block->getDamage());
	}
}
