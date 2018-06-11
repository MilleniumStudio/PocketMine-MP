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

namespace pocketmine\entity\projectile;

use fatcraft\loadbalancer\LoadBalancer;
use fatcraft\lobby\Lobby;
use fatutils\FatUtils;
use fatutils\ui\impl\LobbiesWindow;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\level\Position;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;

class Paintball extends Egg{
    public const NETWORK_ID = self::EGG;

    protected $gravity = 0.01;
    protected $drag = 0.01;

    public $width = 0.5;
    public $height = 0.5;

    public $scale = 0.5;

    protected $damage = 0;

    private $key = "";

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false){
        parent::__construct($level, $nbt, $shootingEntity);
    }

    public function getResultDamage() : int{
        return 0;
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->closed){
            return false;
        }

        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->age > 50){
            $this->flagForDespawn();
            $hasUpdate = true;
        }

        return $hasUpdate;
    }

    protected function onHit(ProjectileHitEvent $event) : void{
        for($i = 0; $i < 6; ++$i){
            $this->level->addParticle(new ItemBreakParticle($this, ItemFactory::get(Item::MAGMA_CREAM)));
        }
        if($event instanceof ProjectileHitBlockEvent)
        {
            /*switch ($l_fatPlayer->getEquipedPaintballAmmo()->getId())
            {
                case BlockIds::CONCRETE:

                    break;
            }*/
            if ($event->getBlockHit()->getId() == BlockIds::BEACON ||
                $event->getBlockHit()->getId() == BlockIds::SEA_LANTERN )
                return;
            /*LoadBalancer::getInstance()->getServer()->getScheduler()->scheduleDelayedTask(new class(FatUtils::getInstance(), $event->getBlockHit(), $event->getBlockHit()->asPosition()) extends PluginTask
            {
                private $m_originalBlock;
                private $m_pos;
                public function __construct(PluginBase $p_Plugin, Block $p_originBlock, Position $p_pos)
                {
                    parent::__construct($p_Plugin);
                    $this->m_originalBlock = $p_originBlock;
                    $this->m_pos = $p_pos;
                }

                public function onRun(int $currentTick)
                {
                    LoadBalancer::getInstance()->getServer()->getLevel(1)->setBlock($this->m_pos->asVector3(), $this->m_originalBlock);
                }
            }, 20 * 60);*/

            $l_blockID = BlockIds::CONCRETE;
            $l_blockMeta = 4;

            switch ($this->key)
            {
                case "paintball.white":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 0;
                    break;
                case "paintball.orange":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 1;
                    break;
                case "paintball.magenta":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 2;
                    break;
                case "paintball.lightBlue":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 3;
                    break;
                case "paintball.yellow":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 4;
                    break;
                case "paintball.lime":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 5;
                    break;
                case "paintball.pink":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 6;
                    break;
                case "paintball.grey":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 7;
                    break;
                case "paintball.lightGrey":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 8;
                    break;
                case "paintball.cyan":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 9;
                    break;
                case "paintball.purple":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 10;
                    break;
                case "paintball.blue":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 11;
                    break;
                case "paintball.brown":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 12;
                    break;
                case "paintball.green":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 13;
                    break;
                case "paintball.red":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 14;
                    break;
                case "paintball.black":
                    $l_blockID = BlockIds::CONCRETE;
                    $l_blockMeta = 15;
                    break;
            }
            $l_block = BlockFactory::get($l_blockID, $l_blockMeta);
            LoadBalancer::getInstance()->getServer()->getLevel(1)->setBlock($event->getBlockHit(),$l_block);
        }
    }

    public function setPaintKey(String $p_key)
    {
        $this->key = $p_key;
        echo ($this->key . "\n");
    }
}
