<?php

declare(strict_types=1);

namespace pocketmine\item;

use fatcraft\loadbalancer\LoadBalancer;
use fatcraft\lobby\Lobby;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;
use fatutils\shop\ShopManager;
use fatutils\ui\impl\PaintballWindow;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Paintball;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\Task;

class PaintballGun extends Egg
{
    public function __construct(int $meta = 0){
        parent::__construct(self::EGG, $meta, "PaintballGun");
    }

    public function getMaxStackSize() : int{
        return 16;
    }

    public function getProjectileEntityType() : string{
        return "Paintball";
    }

    public function getThrowForce() : float{
        return 5;
    }

    public function onClickAir(Player $player, Vector3 $directionVector) : bool
    {
        echo ("click air \n");

        if ($this->getCount() > 1)
        {
            if (LoadBalancer::getInstance()->getServerType() == LoadBalancer::TEMPLATE_TYPE_LOBBY && LoadBalancer::getInstance()->getServerId() != 2)
                LoadBalancer::getInstance()->balancePlayer($player, LoadBalancer::TEMPLATE_TYPE_LOBBY, 2);
            $l_fatPlayer = PlayersManager::getInstance()->getFatPlayer($player);
            $paint = $l_fatPlayer->getSlot(ShopItem::SLOT_PAINTBALL);

            $nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight() + 0.1, 0), $directionVector, $player->yaw, $player->pitch);
            $projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);
            if($projectile !== null)
            {
                if ($projectile instanceof Paintball)
                    $projectile->setPaintKey($paint->getKey());
                $projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
            }


            foreach ($l_fatPlayer->getBoughtItems() as $l_key => $l_BoughtShopItem)
            {
                $l_BoughtShopItem = explode(" ", $l_BoughtShopItem);
                if (strcmp($l_BoughtShopItem[0], $paint->getKey()) == 0)
                {
                    $number = intval($l_BoughtShopItem[1]) - 1;

                    $l_fatPlayer->changeBoughtItemAmmount($paint->getKey(), $number);

                    if ($number > 100)
                        $number = 100;
                    $this->setCount($number);
                }
            }
        }
        else
        {
            echo("window should open\n");
            Lobby::getInstance()->getPaintballMenu($player)->open();
        }
        return true;
    }
}
