<?php

namespace App\CoreFacturalo\Interfaces;

/**
 * Interface ErrorCodeProviderInterface.
 */
interface ErrorCodeProviderInterface
{
    /**
     * @return array
     */
    public function getAll();

    /**
     * @param string $code
     *
     * @return string
     */
    public function getValue($code);
}
