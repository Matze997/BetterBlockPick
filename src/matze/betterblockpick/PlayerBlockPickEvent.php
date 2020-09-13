<?php

namespace matze\betterblockpick;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerBlockPickEvent extends PlayerEvent implements Cancellable {

    /** @var Block */
    private $clickedBlock;
    /** @var Item  */
    private $resultItem;

    /**
     * PlayerBlockPickEvent constructor.
     * @param Player $player
     * @param Block $clickedBlock
     * @param Item $resultItem
     */

    public function __construct(Player $player, Block $clickedBlock, Item $resultItem) {
        $this->player = $player;
        $this->clickedBlock = $clickedBlock;
        $this->resultItem = $resultItem;
    }

    /**
     * Returns the result item of the clicked block
     *
     * @return Item
     */

    public function getResultItem() : Item {
        return $this->resultItem;
    }

    /**
     * Edit the result item
     *
     * @param Item $item
     */

    public function setResultItem(Item $item) : void {
        $this->resultItem = $item;
    }

    /**
     * Returns the block clicked
     *
     * @return Block
     */

    public function getBlock() : Block {
        return $this->clickedBlock;
    }
}