<?php

namespace Pgc\Pgc\Gateway\Response;

/**
 * Class CaptureHandler
 * @package Pgc\Pgc\Gateway\Response
 */
class CaptureHandler extends TxnIdHandler
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
