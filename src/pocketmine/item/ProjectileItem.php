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

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Grenada;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

abstract class ProjectileItem extends Item{

	abstract public function getProjectileEntityType() : string;

	abstract public function getThrowForce() : float;

    public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $directionVector, $player->yaw, $player->pitch);

		if ($this->getProjectileEntityType() == "MachineGunAmmo") //
        {
            return $this->PUBGBulletAmmoBehavior($player, $directionVector, $nbt, ItemIds::CHORUS_FRUIT_POPPED);
        }
        if ($this->getProjectileEntityType() == "SniperAmmo")
        {
            return $this->PUBGBulletAmmoBehavior($player, $directionVector, $nbt, ItemIds::ARROW);
        }

        $projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);

		if($projectile !== null){
			$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
		}
        echo ("proj item : " . $this->getProjectileEntityType() . "\n");
		$this->count--;

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

	private function PUBGBulletAmmoBehavior(Player $player, Vector3 $directionVector, CompoundTag $nbt, int $ItemId) : bool
    {
        if (!$player->getInventory()->contains(ItemFactory::get($ItemId, 0, 1)))
        {
            //echo("set count 1\n");
            $this->count = 1;
            return true;
        }
        $projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);

        if($projectile !== null){
            $projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
        }
        //echo ("Machine gun ammo behavior : " . $this->getProjectileEntityType() . "\n");

        $ammoLeft = $this->getAmmoLeft($player, $ItemId);
        if (($ammoLeft - 3) <= 1)
        {
            $this->count = 1;
        }
        else
            $this->count = $ammoLeft - 3;

        if($projectile instanceof Projectile){
            $player->getServer()->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($projectile));
            if($projectileEv->isCancelled()){
                $projectile->flagForDespawn();
            }else {
                if (isset($projectile->scale))
                    $projectile->setScale($projectile->scale);
                $projectile->spawnToAll();
                $player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());
                if ($player->isSurvival()) {
                    $player->getInventory()->removeItem(ItemFactory::get($ItemId, 0, 1));

                }
            }
        }else{
            $projectile->spawnToAll();
        }
        return true;
    }

}
