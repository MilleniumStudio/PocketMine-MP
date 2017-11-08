<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\Player;

class Painting extends Entity{
    const NETWORK_ID = self::PAINTING;

    /** @var string */
    protected $title = "";

    public $motive = 0;

    public $face;
    public $width = 1;
    public $height = 1;

    protected $baseOffset = 0.125;

    protected function initEntity(){
            parent::initEntity();

            if(isset($this->namedtag->Title)){
                    $this->title = $this->namedtag["Title"];
            }
            if(isset($this->namedtag->Title)){
                    $this->title = $this->namedtag["Title"];
            }
            if(isset($this->namedtag->Title)){
                    $this->title = $this->namedtag["Title"];
            }

            $this->server->getPluginManager()->callEvent(new ItemSpawnEvent($this));
    }

    public function canCollideWith(Entity $entity) : bool{
            return false;
    }

    /**
     * @return string
     */
    public function getTitle() : string{
            return $this->title;
    }

    public function getFace(): int
    {
        return $this->face;
    }

    protected function sendSpawnPacket(Player $player) : void{
            $pk = new AddPaintingPacket();
            $pk->entityUniqueId = null;
            $pk->entityRuntimeId = $this->getId();
            $pk->position = $this->asVector3();
            $pk->direction = $this->getFace();
            $pk->title = $this->getTitle();

            $player->dataPacket($pk);
    }
}