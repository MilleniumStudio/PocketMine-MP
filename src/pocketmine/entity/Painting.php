<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class Painting extends Entity{
    const NETWORK_ID = self::PAINTING;

    public $motive = ["Kebab", 1, 1];
    public $facing = 2;

    public function __construct(Level $level, CompoundTag $nbt, $data = array())
    {
        $this->gravity = 0;
        if (isset($data["motive"]))
        {
            $this->motive = $data["motive"];
            $this->widht = $data["motive"][1];
            $this->height = $data["motive"][2];
        }
        if (isset($data["facing"]))
        {
            $this->facing = $data["facing"];
        }
        if (isset($data["x"]))
        {
            $this->x = $data["x"];
        }
        if (isset($data["y"]))
        {
            $this->y = $data["y"];
        }
        if (isset($data["z"]))
        {
            $this->z = $data["z"];
        }
        $this->setCanSaveWithChunk(false);
        parent::__construct($level, $nbt);
    }

    protected function initEntity(){
        parent::initEntity();
    }

    public function saveNBT()
    {
        //
    }

    public function onUpdate(int $currentTick): bool
    {
        return parent::onUpdate($currentTick);
    }

    public function canCollideWith(Entity $entity) : bool
    {
        return false;
    }

    public function onInteract(Player $player, Item $item): bool
    {
        if ($item == null)
        {
            $this->close();
        }
        // TODO roll painting
        return true;
    }

    public function getTitle() : string
    {
        return $this->motive[0];
    }

    public function getFacing(): int
    {
        return $this->facing;
    }

    protected function sendSpawnPacket(Player $player) : void{
        $pk = new AddPaintingPacket();
        $pk->entityUniqueId = $this->getId();
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->direction = $this->getFacing();
        $pk->title = $this->getTitle();
        $player->dataPacket($pk);
        parent::spawnTo($player);
    }
}