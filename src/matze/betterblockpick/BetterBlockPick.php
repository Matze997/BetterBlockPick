<?php

namespace matze\betterblockpick;

use pocketmine\block\Block;
use pocketmine\block\UnknownBlock;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBlockPickEvent as PMPlayerBlockPickEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class BetterBlockPick extends PluginBase implements Listener {

    /** @var null  */
    private static $instance = null;

    public function onEnable() : void {
        self::$instance = $this;

        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @return BetterBlockPick|null
     */

    public static function getInstance() : ?self {
        return self::$instance;
    }

    /**
     * @param Player $player
     * @param Block $block
     * @return bool
     */

    public function handleBlockPick(Player $player, Block $block) : bool {
        if($block instanceof UnknownBlock){
            return false;
        }
        if($player->isSpectator() || $player->isAdventure(true)) {
            return false;
        }

        $inventory = $player->getInventory();
        $item = $block->getPickedItem();
        $event = new PlayerBlockPickEvent($player, $block, $item);
        $event->call();

        if($event->isCancelled()) return false;

        $item = $event->getResultItem();

        switch ($player->getGamemode()) {
            case Player::CREATIVE: {
                if(!$inventory->contains($item)){
                    $oldItem = $inventory->getItemInHand();
                    $newSlot = $inventory->firstEmpty();
                    if($oldItem->getId() === Item::AIR) {
                        $newSlot = $inventory->getHeldItemIndex();
                    }

                    if($newSlot <= 8 && $newSlot >= 0) {
                        $inventory->setHeldItemIndex($newSlot);
                        $inventory->setItem($newSlot, $item);
                        return true;
                    }
                    if($newSlot !== -1) {
                        $inventory->setItem($newSlot, $oldItem);
                    }
                    $inventory->setItemInHand($item);
                    return true;
                } else {
                    $oldItem = $inventory->getItemInHand();
                    $newSlot = $inventory->first($item, false);

                    if($newSlot <= 8 && $newSlot >= 0) {
                        $inventory->setHeldItemIndex($newSlot);
                        return true;
                    }

                    $item = $inventory->getItem($newSlot);
                    if($newSlot !== -1) {
                        $inventory->setItem($newSlot, $oldItem);
                    }
                    $inventory->setItemInHand($item);
                    $inventory->setItem($newSlot, $oldItem);
                    return true;
                }
            }
            case Player::SURVIVAL: {
                if(!$inventory->contains($item)) return false;
                $itemSlot = $inventory->first($item, false);
                $newSlot = $inventory->firstEmpty();
                if($inventory->isSlotEmpty($inventory->getHeldItemIndex())){
                    $newSlot = $inventory->getHeldItemIndex();
                }
                $oldItem = $inventory->getItem($itemSlot);
                $item = $inventory->getItem($itemSlot);

                if($itemSlot <= 8 && $itemSlot >= 0) {
                    $inventory->setHeldItemIndex($itemSlot);
                    return true;
                }
                $inventory->setItem($itemSlot, $oldItem);
                $inventory->setItem($newSlot, $item);
                $inventory->setHeldItemIndex($newSlot);
                return true;
            }
        }
        return false;
    }

    /**
     * @param PMPlayerBlockPickEvent $event
     */

    public function onBlockPick(PMPlayerBlockPickEvent $event) : void {
        $event->setCancelled();
    }

    /*public function onRealBlockPick(PlayerBlockPickEvent $event) : void { // For tests
        $event->setCancelled();
    }*/

    /**
     * @param DataPacketReceiveEvent $event
     */

    public function dataPacket(DataPacketReceiveEvent $event) : void {
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        if($packet instanceof BlockPickRequestPacket) {
            $block = $player->getLevel()->getBlockAt($packet->blockX, $packet->blockY, $packet->blockZ);
            $this->handleBlockPick($player, $block);
        }
    }
}
