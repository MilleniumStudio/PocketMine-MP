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

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\players\PlayersManager;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Grenada;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\level\Position;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

abstract class ProjectileItem extends Item{

	abstract public function getProjectileEntityType() : string;

	abstract public function getThrowForce() : float;

    public function relocationByAngle(Entity $p_Origin, float $p_Angle, float $p_Dist, bool $p_Relative, bool $p_UseMinecraftAngle) : Position
    {
        if ($p_UseMinecraftAngle)
        {
            echo("angle " . $p_Angle . "\n");
            $p_Angle = -deg2rad($p_Angle);
            echo("angler " . $p_Angle . "\n");
        }

        if ($p_Relative)
        {
            $z = ($p_Dist * cos($p_Angle));
            $x = ($p_Dist * sin($p_Angle));
        }
        else
        {
            $z = $p_Origin->z + ($p_Dist * cos($p_Angle));
            $x = $p_Origin->x + ($p_Dist * sin($p_Angle));
        }

        return new Position($x, $p_Origin->y, $z);
    }

    public function onClickAir(Player $player, Vector3 $directionVector) : bool
    {
        echo("yo " . $player->yaw.  "! hey yo " . $player->headYaw . "\n");
        $nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight() + 0.1, 0), $directionVector, $player->yaw, $player->pitch);
        //$relocation = $this->relocationByAngle($player, $player->yaw, 0.3, true, true);
		//$nbt = Entity::createBaseNBT($player->add($relocation->x, $player->getEyeHeight() - 0.1, $relocation->z), $directionVector, $player->yaw, $player->pitch);

		if ($this->getProjectileEntityType() == "MachineGunAmmo") //
        {
            if ($this->PUBGBulletAmmoBehavior($player, $directionVector, $nbt, ItemIds::CHORUS_FRUIT_POPPED))
                LoadBalancer::getInstance()->getServer()->getLevel(1)->broadcastLevelSoundEvent($player->getPosition(), LevelSoundEventPacket::SOUND_BURP, 1, 0x10000000);
            return true;
        }
        if ($this->getProjectileEntityType() == "ShotgunAmmo")
        {
            if ($this->PUBGBulletAmmoBehavior($player, $directionVector, $nbt, ItemIds::GUNPOWDER))
                LoadBalancer::getInstance()->getServer()->getLevel(1)->broadcastLevelSoundEvent($player->getPosition(), LevelSoundEventPacket::SOUND_SHULKERBOX_OPEN, 1, 0x10000000);
            return true;
        }

        $projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);

		if($projectile !== null){
			$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
		}
        echo("yo proj item : " . $this->getProjectileEntityType() . "\n");
		$this->count--;

        if ($projectile instanceof SplashPotion)
        {
            echo ("it's a SplashPotion !\n");
            $projectile->setOwningEntity($player);
            $projectile->setOriginLaunchPoint($player->getPosition());
            if ($this instanceof \pocketmine\item\SplashPotion)
                $projectile->metaData = $this->metaData;
        }

        if ($projectile instanceof Grenada) //  item/MachineGunAmmo
        {
            echo ("it's a grenada !\n");
            if (isset($projectile->scale))
                $projectile->setScale($projectile->scale);

        }
            if($projectile instanceof Projectile){
			$player->getServer()->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($projectile));
			if($projectileEv->isCancelled()){
				$projectile->flagForDespawn();
			}else{
                if (isset($projectile->scale))
                    $projectile->setScale($projectile->scale);
				$projectile->spawnToAll();
				$player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());
			}
		}else{
			$projectile->spawnToAll();
		}

		return true;
	}

	private function getAmmoLeft(Player $player, int $id)
    {
        $i = 1;
        while ($player->getInventory()->contains(ItemFactory::get($id, 0, $i++)));
        return $i;
    }

    private function getNewBulletVector(Vector3 $originVector, int $dispersionAngle ) : Vector3
    {
        $x = (rand(($originVector->x * 100 % 100) - $dispersionAngle, ($originVector->x * 100 % 100) + $dispersionAngle)) / 100;
        $y = (rand(($originVector->y * 100 % 100) - $dispersionAngle, ($originVector->y * 100 % 100) + $dispersionAngle)) / 100;
        $z = (rand(($originVector->z * 100 % 100) - $dispersionAngle, ($originVector->z * 100 % 100) + $dispersionAngle)) / 100;

        if ($x > 1)
            $x = 1 - ($x - 1);
        if ($y > 1)
            $y = 1 - ($y - 1);
        if ($z > 1)
            $z = 1 - ($z - 1);

        if ($x < -1)
            $x = -1 - ($x + 1);
        if ($y < -1)
            $y = -1 - ($y + 1);
        if ($z < -1)
            $z = -1 - ($z + 1);

        return new Vector3($x, $y, $z);
    }

	private function PUBGBulletAmmoBehavior(Player $player, Vector3 $directionVector, CompoundTag $nbt, int $ItemId) : bool
    {
        if (!$player->getInventory()->contains(ItemFactory::get($ItemId, 0, 1)))
        {
            $this->count = 1;
            return false;
        }

        $isShotgun = false;
        $nbBullets = 1;
        if ($this->getProjectileEntityType() == "ShotgunAmmo")
        {
            if (microtime(true) < $player->getShotgunCooldown())
                return false;
            $player->setShotgunCooldown(microtime(true) + 1,1);
            $nbBullets = 15;
            $isShotgun = true;
        }

        while ($nbBullets > 0)
        {
            $projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);
            $bulletDirection = $projectile->getMotion();

            if ($isShotgun)
                $bulletDirection = $this->getNewBulletVector($bulletDirection, 20);

            if ($projectile !== null) {
                $projectile->setMotion($bulletDirection->multiply($this->getThrowForce()));
            }

            $ammoLeft = $this->getAmmoLeft($player, $ItemId);
            if (($ammoLeft - 3) <= 1) {
                $this->count = 1;
            } else
                $this->count = $ammoLeft - 3;

            if ($projectile instanceof Projectile) {
                $player->getServer()->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($projectile));
                if ($projectileEv->isCancelled()) {
                    $projectile->flagForDespawn();
                } else {
                    if (isset($projectile->scale))
                        $projectile->setScale($projectile->scale);
                    $projectile->spawnToAll();
                    $player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());

                }
            } else {
                $projectile->spawnToAll();
            }
            $nbBullets--;
        }
        if ($player->isSurvival())
                $player->getInventory()->removeItem(ItemFactory::get($ItemId, 0, 1));
        return true;
    }

    private function SplashWall()
    {

    }

}
