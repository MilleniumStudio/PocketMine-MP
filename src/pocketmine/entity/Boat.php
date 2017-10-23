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

declare(strict_types = 1);

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\item\Boat as ItemBoat;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\VehicleUpdateEvent;
use pocketmine\event\entity\VehicleMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\level\Location;

class Boat extends Vehicle
{

    const NETWORK_ID = 90;
    const DATA_WOOD_ID = 20;

    public $width = 1.6;
    public $height = 0.7;
    public $drag = 0.1;
    public $gravity = 0.1;
    public $baseOffset = 0.35;

    public function getName(): string
    {
        return "Boat";
    }

    public function spawnTo(Player $player)
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

        parent::spawnTo($player);
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
        $this->performHurtAnimation((int) floor($source->getFinalDamage()));
        $instantKill = false;
        if ($source instanceof EntityDamageByEntityEvent && $source->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK)
        {
            $instantKill = $source->getDamager() instanceof Player && $source->getDamager()->isCreative();
        }
        if ($instantKill || $this->getDamage() > 40)
        {
            if ($this->passenger != null)
            {
                $this->mountEntity($this->passenger);
            }

            if ($instantKill) {
                $this->kill();
            } else {
//                if ($this->level->getGameRules()->getBoolean("doEntityDrops")) { // not implemented
                    $this->level->dropItem($this, new ItemBoat());
//                }
                $this->close();
            }
        }
        return true;
    }

    public function close()
    {
        if (!$this->closed){
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
        if ($this->closed){
            return false;
        }

        $tickDiff = $currentTick - $this->lastUpdate;
//        if ($tickDiff <= 0){
//            $this->server->getLogger()->debug("Expected tick difference of at least 1, got $tickDiff for " . get_class($this));
//            return false;
//        }

        if (!$this->isAlive()){
            $this->deadTicks += $tickDiff;
            if ($this->deadTicks >= 10){
                $this->despawnFromAll();
                if (!$this->isPlayer){
                    $this->close();
                }
            }
            return $this->deadTicks < 10;
        }

        parent::onUpdate($currentTick);

        $hasUpdate = $this->entityBaseTick($tickDiff);

        if ($this->level == null)
        {
            return false;
        }

        $this->motionY = ($this->level->getBlock(new Vector3($this->x, $this->y, $this->z))->getBoundingBox() != null || $this->isInsideOfWater()) ? $this->gravity : -0.08;

        if ($this->checkObstruction($this->x, $this->y, $this->z)) {
            $hasUpdate = true;
        }

        $this->move($this->motionX, $this->motionY, $this->motionZ);

        $friction = 1 - $this->drag;

        if ($this->onGround && (abs($this->motionX) > 0.00001 || abs($this->motionZ) > 0.00001)) {
            $friction *= $this->getLevel()->getBlock($this->temporalVector->setComponents((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z) - 1)).getFrictionFactor();
        }

        $this->motionX *= $friction;
        $this->motionY *= 1 - $this->drag;
        $this->motionZ *= $friction;

        if ($this->onGround) {
            $this->motionY *= -0.5;
        }

        $from = new Location($this->lastX, $this->lastY, $this->lastZ, $this->lastYaw, $this->lastPitch, $this->level);
        $to = new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->level);

        $this->server->getPluginManager()->callEvent(new VehicleUpdateEvent($this));

        if (!$from->equals($to)) {
            $this->server->getPluginManager()->callEvent(new VehicleMoveEvent($this, $from, $to));
        }

        $this->updateMovement();
        return true;
    }

    public function onInteract(Player $player, ItemItem $item):bool
    {
        if ($this->passenger != null)
        {
            return false;
        }

        parent::mountEntity($player);
        return true;
    }

}
