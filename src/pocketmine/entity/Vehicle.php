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

use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\event\entity\EntityVehicleEnterEvent;
use pocketmine\event\entity\EntityVehicleExitEvent;

abstract class Vehicle extends Interactable implements Rideable{

    protected $rollingDirection = true;
    /** @var int */
    protected $jumpTicks = 0;
    /** @var float */
    protected $jumpHeight = 0.08;
    protected $seatOffset = array(0, 0, 0);

    const STATE_SITTING = 1;
    const STATE_STANDING = 0;

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
        return $this->passenger == null;
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

        //dismount action
        if ($p_Rider->vehicle != null) {
            return $this->dismount($p_Rider);
        }
        $ev = new EntityVehicleEnterEvent($p_Rider, $this);
        $this->server->getPluginManager()->callEvent($ev);
        if ($ev->isCancelled()) {
            return false;
        }

        // mount Action
        if($p_Rider->vehicle !== null) {
            return false;
        }
        if($p_Rider instanceof Player && $p_Rider->isSurvival(true)) {
            $p_Rider->setAllowFlight(true); // Set allow flight to true to prevent any 'kicked for flying' issues.
        }
        $this->passenger = $p_Rider;
        $p_Rider->vehicle = $this;
        $p_Rider->canCollide = false;

        $p_Rider->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, $this->seatOffset);
        $p_Rider->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
//        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SADDLED, true);

        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $this->getId();
        $link->type = self::STATE_SITTING;
        $link->toEntityUniqueId = $p_Rider->getId();
        $link->byte2 = 1;

        $pk->link = $link;
        $this->server->broadcastPacket($this->server->getOnlinePlayers(), $pk);

        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $this->getId();
        $link->type = self::STATE_SITTING;
        $link->toEntityUniqueId = 0;
        $link->byte2 = 1;

        $pk->link = $link;
        $p_Rider->dataPacket($pk);
        $this->server->getLogger()->info("Entity " . $p_Rider->getId() . " mount " . $this->getId());
        return true;
    }

    public function dismount(Entity $p_Rider): bool
    {
        $ev = new EntityVehicleExitEvent($p_Rider, $p_Rider->vehicle);
        $this->server->getPluginManager()->callEvent($ev);
        if ($ev->isCancelled()) {
            return false;
        }
        $this->server->getLogger()->info("Entity " . $p_Rider->getId() . " dismount " . $p_Rider->vehicle->getId());

        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $p_Rider->vehicle->getId();
        $link->type = self::STATE_STANDING;
        $link->toEntityUniqueId = $p_Rider->getId();
        $link->byte2 = 1;

        $pk->link = $link;
        $this->server->broadcastPacket($this->level->getPlayers(), $pk);

        $pk = new SetEntityLinkPacket();

        $link = new EntityLink();
        $link->fromEntityUniqueId = $p_Rider->getId();
        $link->type = self::STATE_STANDING;
        $link->toEntityUniqueId = 0;
        $link->byte2 = 1;

        $pk->link = $link;
        $p_Rider->dataPacket($pk);

        if($p_Rider !== null) {
            $p_Rider->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, false);
            if($p_Rider instanceof Player && $p_Rider->isSurvival(true))
            {
                $p_Rider->setAllowFlight(false);
            }
        }

        $p_Rider->canCollide = true;
        $p_Rider->vehicle->passenger = null;
        $p_Rider->vehicle = null;
        $p_Rider->onGround = true;
        return true;
    }

    public function doRidingMovement(float $motionX, float $motionZ): bool
    {
        $this->pitch = $this->passenger->pitch;
        $this->yaw = $this->passenger->yaw;

        $x = $this->getDirectionVector()->x / 2 * $this->getSpeed();
        $z = $this->getDirectionVector()->z / 2 * $this->getSpeed();

        if($this->jumpTicks > 0) {
            $this->jumpTicks--;
        }

        if(!$this->isOnGround()) {
            if($this->motionY > -$this->gravity * 2) {
                $this->motionY = -$this->gravity * 2;
            } else {
                $this->motionY -= $this->gravity;
            }
        } else {
            $this->motionY -= $this->gravity;
        }

        $finalMotion = [0, 0];
        switch($motionZ) {
            case 1:
                $finalMotion = [$x, $z];
                if($this->isOnGround()) {
                    $this->jump();
                }
                break;
            case 0:
                if($this->isOnGround()) {
                    $this->jump();
                }
                break;
            case -1:
                $finalMotion = [-$x, -$z];
                if($this->isOnGround()) {
                    $this->jump();
                }
                break;
            default:
                $average = $x + $z / 2;
                $finalMotion = [$average / 1.414 * $motionZ, $average / 1.414 * $motionX];
                if($this->isOnGround()) {
                    $this->jump();
                }
                break;
        }
        switch($motionX) {
            case 1:
                $finalMotion = [$z, -$x];
                if($this->isOnGround()) {
                        $this->jump();
                }
                break;
            case 0:
                if($this->isOnGround()) {
                        $this->jump();
                }
                break;
            case -1:
                $finalMotion = [-$z, $x];
                if($this->isOnGround()) {
                        $this->jump();
                }
                break;
        }

        $this->move($finalMotion[0], $this->motionY, $finalMotion[1]);
        $this->updateMovement();
        return $this->isAlive();
    }

    public function jump(): void {
        $this->motionY = $this->jumpHeight * 12 * $this->getScale();
        $this->move($this->motionX, $this->motionY, $this->motionZ);
        $this->jumpTicks = 10;
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