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

use pocketmine\event\Timings;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class Boat extends Vehicle
{

	const NETWORK_ID = 90;
	const DATA_WOOD_ID = 20;
	public $width = 1.6;
	public $height = 0.75;
	public $gravity = 0.1;
	public $drag = 0.01;
	public $seatOffset = array(0, 0.9, 0);

	public function __construct(Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
	}

	public function getName(): string
	{
		return "Boat";
	}

	protected function sendSpawnPacket(Player $player): void
	{
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Boat::NETWORK_ID;
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);
	}

	public function getDrops(): array
	{
		$drops = [
			ItemFactory::get(ItemItem::BOAT, 0, 1)
		];

		return $drops;
	}

	public function attack(EntityDamageEvent $source)
	{
		$damage = (int)floor($source->getFinalDamage());

		$this->performHurtAnimation();
		$this->setDamage($this->getDamage() + $damage);
		$this->setHealth($this->getHealth() - $damage);
		echo "Entity " . $this->getId() . " damage " . $this->getDamage() . "/" . $this->getMaxHealth() . "\n";

		$instantKill = false;
		if ($source instanceof EntityDamageByEntityEvent && $source->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
			$instantKill = $source->getDamager() instanceof Player && $source->getDamager()->isCreative();
		}
		if ($instantKill)
				$this->kill();
		if ($this->getDamage() >= $this->getMaxHealth()){
			foreach ($this->getDrops() as $l_Item) {
				$this->level->dropItem($this, $l_Item);
			}
			$this->close();
		}
		return true;
	}

	public function close()
	{
		if (!$this->closed) {
			if ($this->passenger instanceof Player) {
				$this->passenger->vehicle = null;
			}

			$particle = new SmokeParticle($this);
			$this->level->addParticle($particle);
			parent::close();
		}
	}

	public function onUpdate(int $currentTick): bool
	{
		$b = parent::onUpdate($currentTick);
		if ($this->level != null) {
			$blockId = $this->level->getBlockAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ())->getId();
			$this->motionY = ($blockId == ItemIds::WATER || $blockId == ItemIds::FLOWING_WATER) ? 0.1 : $this->motionY;
			$this->setMotion(new Vector3($this->motionX, $this->motionY, $this->motionZ));
		}

		return $b;
	}


	public function onInteract(Player $player, ItemItem $item): bool
	{
		if ($this->passenger != null) {
			return false;
		}

		parent::mountEntity($player);
		return true;
	}

	public function move(float $dx, float $dy, float $dz): bool
	{
		$this->blocksAround = null;

		if ($dx == 0 and $dz == 0 and $dy == 0) {
			return true;
		}

		if ($this->keepMovement) {
			$this->boundingBox->offset($dx, $dy, $dz);
			$this->setPosition($this->temporalVector->setComponents(($this->boundingBox->minX + $this->boundingBox->maxX) / 2, $this->boundingBox->minY, ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2));
			$this->onGround = $this->isPlayer ? true : false;
			return true;
		} else {

			Timings::$entityMoveTimer->startTiming();

			$this->ySize *= 0.4;

			$movX = $dx;
			$movY = $dy;
			$movZ = $dz;

			$axisalignedbb = clone $this->boundingBox;

			assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");

			$list = $this->level->getCollisionCubes($this, $this->level->getTickRate() > 1 ? $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz) : $this->boundingBox->addCoord($dx, $dy, $dz), false);

			foreach ($list as $bb) {
				$dy = $bb->calculateYOffset($this->boundingBox, $dy);
			}
			$this->boundingBox->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));

			foreach ($list as $bb) {
				$dx = $bb->calculateXOffset($this->boundingBox, $dx);
			}

			$this->boundingBox->offset($dx, 0, 0);

			foreach ($list as $bb) {
				$dz = $bb->calculateZOffset($this->boundingBox, $dz);
			}

			$this->boundingBox->offset(0, 0, $dz);

			if ($this->stepHeight > 0 and $fallingFlag and $this->ySize < 0.05 and ($movX != $dx or $movZ != $dz)) {
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;

				$axisalignedbb1 = clone $this->boundingBox;

				$this->boundingBox->setBB($axisalignedbb);

				$list = $this->level->getCollisionCubes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);

				foreach ($list as $bb) {
					$dy = $bb->calculateYOffset($this->boundingBox, $dy);
				}

				$this->boundingBox->offset(0, $dy, 0);

				foreach ($list as $bb) {
					$dx = $bb->calculateXOffset($this->boundingBox, $dx);
				}

				$this->boundingBox->offset($dx, 0, 0);

				foreach ($list as $bb) {
					$dz = $bb->calculateZOffset($this->boundingBox, $dz);
				}

				$this->boundingBox->offset(0, 0, $dz);

				if (($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)) {
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
					$this->boundingBox->setBB($axisalignedbb1);
				} else {
					$this->ySize += 0.5; //FIXME: this should be the height of the block it walked up, not fixed 0.5
//					$blockBB = $this->level->getBlock($this)->getBoundingBox();
//					echo "ySize block, etc.. ".$blockBB->maxY - $blockBB->minY."\n";
//					$this->ySize += $blockBB->maxY - $blockBB->minY;
				}

			}

			$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
//			$this->y = $this->boundingBox->minY - $this->ySize;
			$this->y = ($this->boundingBox->minY + $this->boundingBox->maxY) / 2;
			$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

			$this->checkChunks();
			$this->checkBlockCollision();
			$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
			$this->updateFallState($dy, $this->onGround);

			if ($movX != $dx) {
				$this->motionX = 0;
			}

			if ($movY != $dy) {
				$this->motionY = 0;
			}

			if ($movZ != $dz) {
				$this->motionZ = 0;
			}

			Timings::$entityMoveTimer->stopTiming();

			return true;
		}
	}
}
