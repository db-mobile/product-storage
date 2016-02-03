<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\SequenceNumber\Business;

use Generated\Shared\Transfer\SequenceNumberSettingsTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\SequenceNumber\Business\SequenceNumberBusinessFactory getFactory()
 */
class SequenceNumberFacade extends AbstractFacade
{

    /**
     * @param \Generated\Shared\Transfer\SequenceNumberSettingsTransfer $sequenceNumberSettings
     *
     * @return string
     */
    public function generate(SequenceNumberSettingsTransfer $sequenceNumberSettings)
    {
        $sequenceNumber = $this->getFactory()
            ->createSequenceNumber($sequenceNumberSettings);

        return $sequenceNumber->generate();
    }

}
