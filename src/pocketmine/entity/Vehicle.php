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
use pocketmine\Player;

abstract class Vehicle extends Interactable implements Rideable{

    protected $rollingDirection = true;
    public $seatOffset = array(0, 0, 0);

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
//        $this->PitchDelta = 0.0;
//        $this->YawDelta = 0.0;

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
        $p_Rider->pitch = $this->pitch;

        $p_Rider->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, $this->seatOffset);
        $p_Rider->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
        $p_Rider->setDataProperty(self::DATA_RIDER_ROTATION_LOCKED, self::DATA_TYPE_BYTE, 1);
        $p_Rider->setDataProperty(self::DATA_RIDER_MIN_ROTATION, self::DATA_TYPE_FLOAT, -90);
        $p_Rider->setDataProperty(self::DATA_RIDER_MAX_ROTATION, self::DATA_TYPE_FLOAT, 90);
//        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SADDLED, true);

        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $this->getId();
        $link->type = self::STATE_SITTING;
        $link->toEntityUniqueId = $p_Rider->getId();
        $link->bool1 = TRUE;

        $pk->link = $link;
        $this->server->broadcastPacket($this->server->getOnlinePlayers(), $pk);

        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $this->getId();
        $link->type = self::STATE_SITTING;
        $link->toEntityUniqueId = 0;
        $link->bool1 = TRUE;

        $pk->link = $link;
        $p_Rider->dataPacket($pk);
//        $this->server->getLogger()->debug("Entity " . $p_Rider->getId() . " mount " . $this->getId());
        return true;
    }

    public function dismount(Entity $p_Rider): bool
    {
        $ev = new EntityVehicleExitEvent($p_Rider, $p_Rider->vehicle);
        $this->server->getPluginManager()->callEvent($ev);
        if ($ev->isCancelled()) {
            return false;
        }
//        $this->server->getLogger()->debug("Entity " . $p_Rider->getId() . " dismount " . $p_Rider->vehicle->getId());

		if($p_Rider instanceof Living)
			$p_Rider->headYaw = null;
        $pk = new SetEntityLinkPacket();
        $link = new EntityLink();
        $link->fromEntityUniqueId = $p_Rider->vehicle->getId();
        $link->type = self::STATE_STANDING;
        $link->toEntityUniqueId = $p_Rider->getId();
        $link->bool1 = TRUE;

        $pk->link = $link;
        $this->server->broadcastPacket($this->server->getOnlinePlayers(), $pk);

        $pk = new SetEntityLinkPacket();

        $link = new EntityLink();
        $link->fromEntityUniqueId = $p_Rider->getId();
        $link->type = self::STATE_STANDING;
        $link->toEntityUniqueId = 0;
        $link->bool1 = TRUE;

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

    public function doRidingMovement(float $motionX, float $motionY, float $motionZ, float $yaw, float $pitch): bool
    {
//    	echo "Vehicle::doRidingMovement()\n";
        $this->yaw = $yaw;
        $this->pitch = $pitch;

        //todo understand
//        $x = $this->getDirectionVector()->x / 2 * $this->getSpeedVector()->length();
//        $z = $this->getDirectionVector()->z / 2 * $this->getSpeedVector()->length();

        if(!$this->isOnGround()) {
            if($this->motionY > -$this->gravity * 2) {
                $this->motionY = -$this->gravity * 2;
            } else {
                $this->motionY -= $this->gravity;
            }
        }
//        else {
//            $this->motionY -= $this->gravity;
//        }

//        $finalMotion = [0, 0];
//        switch($motionZ) {
//            case 1:
//                $finalMotion = [$x, $z];
//                break;
//            case 0:
//                break;
//            case -1:
//                $finalMotion = [-$x, -$z];
//                break;
//            default:
//                $average = $x + $z / 2;
//                $finalMotion = [$average / 1.414 * $motionZ, $average / 1.414 * $motionX];
//                break;
//        }
//        switch($motionX) {
//            case 1:
//                $finalMotion = [$z, -$x];
//                break;
//            case 0:
//                break;
//            case -1:
//                $finalMotion = [-$z, $x];
//                break;
//        }

//        $this->move($finalMotion[0], $this->motionY, $finalMotion[1]);
//		echo $motionX . ", " . $motionY . ", " . $motionZ . "\n";
        $dx = $motionX - $this->lastX;
        $dy = $motionY - $this->lastY;
        $dz = $motionZ - $this->lastZ;
		$this->move($dx, $dy, $dz);
        return $this->isAlive();
    }

//    public function onUpdate(int $currentTick):bool {
//        if (parent::onUpdate($currentTick))
//        {
//            return false;
//        }
//        // The rolling amplitude
//        if ($this->getRollingAmplitude() > 0) {
//            $this->setRollingAmplitude($this->getRollingAmplitude() - 1);
//        }
//        // The damage token
//        if ($this->getDamage() > 0) {
//            $this->setDamage($this->getDamage() - 1);
//        }
//        // A killer task
//        if ($this->y < -16) {
//            $this->kill();
//        }
//        // Movement code
//        $this->updateMovement();
////        if ($this->passenger !== null)
////        {
////            $title = "Position : " . $this->passenger->x . " / " . $this->passenger->y . " / " . $this->passenger->z . "\n";
////            $this->sendPopup($title . "Vehicle : " . $this->x . " / " . $this->y . " / " . $this->z . "", "");
////        }
//        return true;
//    }

    protected function performHurtAnimation() {
        // Vehicle does not respond hurt animation on packets
        // It only respond on vehicle data flags. Such as these
        $this->setRollingAmplitude(10);
        $this->setRollingDirection($this->rollingDirection ? 1 : -1);
        $this->rollingDirection = !$this->rollingDirection;
        return true;
    }
}