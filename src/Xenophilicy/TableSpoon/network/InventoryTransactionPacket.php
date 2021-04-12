<?php
declare(strict_types=1);

namespace Xenophilicy\TableSpoon\network;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket as PMInventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\TransactionData;
use ReflectionProperty;
use Xenophilicy\TableSpoon\network\types\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction as PMNetworkInventoryAction;

/**
 * Class InventoryTransactionPacket
 * @package Xenophilicy\TableSpoon\network
 */
class InventoryTransactionPacket extends PMInventoryTransactionPacket {
    
    /** @var bool */
    public $isCraftingPart;
    
    /** @var bool */
    public $isFinalCraftingPart;
    
    protected function decodePayload(): void{
        parent::decodePayload();
        $hook = new ReflectionProperty(TransactionData::class, "actions");
        $hook->setAccessible(true);
        $actions = $hook->getValue($this->trData);
        foreach($this->trData->getActions() as $index => $action){
            $actions[$index] = NetworkInventoryAction::cast($action);
            if($action->sourceType === NetworkInventoryAction::SOURCE_CONTAINER and $action->windowId === ContainerIds::UI and $action->inventorySlot === 50 and !$action->oldItem->equalsExact($action->newItem)){
                $this->isCraftingPart = true;
                if(!$action->oldItem->getItemStack()->isNull() and $action->newItem->getItemStack()->isNull()){
                    $this->isFinalCraftingPart = true;
                }
            }elseif($action->sourceType === NetworkInventoryAction::SOURCE_TODO and ($action->windowId === NetworkInventoryAction::SOURCE_TYPE_CRAFTING_RESULT or $action->windowId === NetworkInventoryAction::SOURCE_TYPE_CRAFTING_USE_INGREDIENT)){
                $this->isCraftingPart = true;
            }
        }
        $hook->setValue($actions);
    }
}