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

use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\event\entity\EntityVehicleEnterEvent;
use pocketmine\event\entity\EntityVehicleExitEvent;

abstract class Vehicle extends Interactable implements Rideable{

    protected $rollingDirection = true;

    public function getRollingAmplitude() {
        return $this->getDataProperty(Entity::DATA_HURT_TIME);
    }

    public function setRollingAmplitude(int $time) {
        $this->setDataProperty(Entity::DATA_HURT_TIME, Entity::DATA_TYPE_INT, $time);
    }

    public function getRollingDirection() {
        return $this->getDataProperty(Entity::DATA_HURT_DIRECTION);
    }

    public function setRollingDirection(int $direction) {
        $this->setDataProperty(Entity::DATA_HURT_DIRECTION, Entity::DATA_TYPE_INT, $direction);
    }

    public function getDamage() {
        return $this->getDataProperty(Entity::DATA_HEALTH); // false data name (should be DATA_DAMAGE_TAKEN)
    }

    public function setDamage(int $damage) {
        $this->setDataProperty(Entity::DATA_HEALTH, Entity::DATA_TYPE_INT, $damage);
    }

    public function getInteractButtonText():string {
        return "Mount";
    }

    public function canDoInteraction():bool {
        return $this->linkedEntity == null;
    }

    /**
     * Mount or Dismounts an Entity from a vehicle
     *
     * @param entity The target Entity
     * @return {@code true} if the mounting successful
     */
    public function mountEntity(Entity $p_Rider) {
        $this->PitchDelta = 0.0;
        $this->YawDelta = 0.0;
        if ($p_Rider->vehicle != null) { //dismount action
            $ev = new EntityVehicleExitEvent($p_Rider, $this);
            $this->server->getPluginManager()->callEvent($ev);
            if ($ev->isCancelled()) {
                return false;
            }

            $pk = new SetEntityLinkPacket();
            $pk->rider = $p_Rider->getId();
            $pk->riding = $p_Rider->vehicle->getId();
            $pk->type = SetEntityLinkPacket::TYPE_REMOVE;
            $this->server->broadcastPacket($p_Rider->getViewers(), $pk);

            if ($p_Rider instanceof \pocketmine\Player) {
                $p_Rider->dataPacket($pk);
            }

            $this->server->getLogger()->info("Entity " . $p_Rider->getId() . " dismount " . $p_Rider->vehicle->getId());
            $p_Rider->vehicle->passenger = null;
            $p_Rider->vehicle = null;
            $p_Rider->setDataProperty(Entity::DATA_FLAG_RIDING, Entity::DATA_TYPE_BYTE, 0);
            return true;
        }
        $ev = new EntityVehicleEnterEvent($p_Rider, $this);
        $this->server->getPluginManager()->callEvent($ev);
        if ($ev->isCancelled()) {
            return false;
        }

        // mount Action
        $pk = new SetEntityLinkPacket();
        $pk->rider = $p_Rider->getId();
        $pk->riding = $this->getId();
        $pk->type = SetEntityLinkPacket::TYPE_RIDE;
        $this->server->broadcastPacket($p_Rider->getViewers(), $pk);

        if (!$p_Rider instanceof Player) {
            $p_Rider->dataPacket($pk);
        }

        $p_Rider->vehicle = $this;
        $this->passenger = $p_Rider;

        $p_Rider->setDataProperty(Entity::DATA_FLAG_RIDING, Entity::DATA_TYPE_BYTE, 1);
        $this->updateRiderPosition($this->getMountedYOffset());
        $this->server->getLogger()->info("Entity " . $p_Rider->getId() . " mount " . $this->getId());
        return true;
    }

    public function onUpdate(int $currentTick):bool {
        // The rolling amplitude
        if ($this->getRollingAmplitude() > 0) {
            $this->setRollingAmplitude($this->getRollingAmplitude() - 1);
        }
        // The damage token
        if ($this->getDamage() > 0) {
            $this->setDamage($this->getDamage() - 1);
        }
        // A killer task
        if ($this->y < -16) {
            $this->kill();
        }
        // Movement code
        $this->updateMovement();
        return true;
    }

    protected function performHurtAnimation(int $damage) {
        if ($damage >= $this->getHealth()) {
            return false;
        }

        // Vehicle does not respond hurt animation on packets
        // It only respond on vehicle data flags. Such as these
        $this->setRollingAmplitude(10);
        $this->setRollingDirection($this->rollingDirection ? 1 : -1);
        $this->rollingDirection = !$this->rollingDirection;
        $this->setDamage($this->getDamage() + $damage);
        $this->server->getLogger()->info("Entity " . $this->getId() . " damage " . $this->getDamage());
        return true;
    }
}