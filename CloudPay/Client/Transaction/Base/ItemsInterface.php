<?php

namespace CloudPay\Client\Transaction\Base;
use CloudPay\Client\Data\Item;

/**
 * Interface ItemsInterface
 *
 * @package CloudPay\Client\Transaction\Base
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
