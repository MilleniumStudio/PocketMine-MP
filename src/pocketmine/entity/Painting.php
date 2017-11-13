<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\nbt\tag\CompoundTag;

class Painting extends Entity{
    const NETWORK_ID = self::PAINTING;

    public static $motives = [
            // Motive Width Height
            ["Kebab", 1, 1],
            ["Aztec", 1, 1],
            ["Alban", 1, 1],
            ["Aztec2", 1, 1],
            ["Bomb", 1, 1],
            ["Plant", 1, 1],
            ["Wasteland", 1, 1],
            ["Wanderer", 1, 2],
            ["Graham", 1, 2],
            ["Pool", 2, 1],
            ["Courbet", 2, 1],
            ["Sunset", 2, 1],
            ["Sea", 2, 1],
            ["Creebet", 2, 1],
            ["Match", 2, 2],
            ["Bust", 2, 2],
            ["Stage", 2, 2],
            ["Void", 2, 2],
            ["SkullAndRoses", 2, 2],
            ["Wither", 2, 2],
            ["Fighters", 4, 2],
            ["Skeleton", 4, 3],
            ["DonkeyKong", 4, 3],
            ["Pointer", 4, 4],
            ["Pigscene", 4, 4],
            ["BurningSkull", 4, 4],
    ];

    public $motive = "Kebab";
    public $facing = 2;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
    }

    protected function initEntity(){
        parent::initEntity();

        $this->setMaxHealth(1);
        $this->setHealth(1);

        if (isset($this->namedtag->Motive))
        {
            $this->motive = $this->namedtag["Motive"];
        }
        if (isset($this->namedtag->Direction))
        {
            $this->facing = $this->namedtag["Direction"];
        }
    }

    public function onUpdate(int $currentTick): bool
    {
        return parent::onUpdate($currentTick);
    }

    public function canCollideWith(Entity $entity) : bool
    {
        return false;
    }

    public function getDrops(): array
    {
            $drops = [
                    ItemFactory::get(ItemIds::PAINTING, 0, 1)
            ];

            return $drops;
    }

    public function onInteract(Player $player, Item $item): bool
    {
        if ($player->getGamemode() == 0)
        {
            foreach ($this->getDrops() as $l_Item) {
                $this->level->dropItem($this->asVector3(), $l_Item);
            }
        }
        if ($player->getGamemode() == 2)
        {
            return false;
        }
        $this->getLevel()->addParticle(new ItemBreakParticle($this->asVector3(), ItemFactory::get(ItemIds::PAINTING)));
        $this->close();

        return true;
    }

    public function getTitle() : string
    {
        return $this->motive;
    }

    public function getFacing(): int
    {
        return $this->facing;
    }

    protected function sendSpawnPacket(Player $player) : void
    {
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