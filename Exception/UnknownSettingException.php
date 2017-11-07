<?php

/**
 * This file is part of the MharySettingsBundle package.
 *
 * (c) 2013 Dmitriy Scherbina <http://mhary.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mhary\SettingsBundle\Exception;

class UnknownSettingException extends SettingsException
{
    public function __construct($settingName)
    {
        parent::__construct(sprintf('Unknown setting "%s"', $settingName));
    }
}
