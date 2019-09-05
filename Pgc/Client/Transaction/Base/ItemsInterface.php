<?php

namespace Pgc\Client\Transaction\Base;
use Pgc\Client\Data\Item;

/**
 * Interface ItemsInterface
 *
 * @package Pgc\Client\Transaction\Base
 */
interface ItemsInterface {

    /**
     * @param Item[] $items
     * @return void
     */
    public function setItems($items);

    /**
     * @return Item[]
     */
    public function getItems();

    /**
     * @param Item $item
     * @return void
     */
    public function addItem($item);

}
