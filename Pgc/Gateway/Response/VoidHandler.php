<?php

namespace Pgc\Pgc\Gateway\Response;

/**
 * Class VoidHandler
 * @package Pgc\Pgc\Gateway\Response
 */
class VoidHandler extends TxnIdHandler
{
    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction(): bool
    {
        return true;
    }
}
