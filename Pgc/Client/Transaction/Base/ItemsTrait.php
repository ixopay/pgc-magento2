<?php

namespace Pgc\Client\Transaction\Base;

use Pgc\Client\Data\Item;

/**
 * Class ItemsTrait
 *
 * @package Pgc\Client\Transaction\Base
 */
trait ItemsTrait {

    /** @var Item[]  */
    protected $items = array();

    /**
     * @param Item[] $items
     * @return $this
     */
    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    /**
     * @return Item[]
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @param Item $item
     *
     * @return $this
     */
    public function addItem($item) {
        $this->items[] = $item;
        return $this;
    }
}
