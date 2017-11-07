<?php

/**
 * This file is part of the MharySettingsBundle package.
 *
 * (c) 2013 Dmitriy Scherbina <http://mhary.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mhary\SettingsBundle\Tests\Serializer;

use Mhary\SettingsBundle\Serializer\SerializerInterface;

class CustomSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return serialize(json_encode($data));
    }

    public function unserialize($serialized)
    {
        return json_decode(unserialize($serialized), true);
    }
}
