<?php

/*
 * This file is part of the slince/shopify-api-php
 *
 * (c) Slince <taosikai@yeah.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Slince\Shopify\Service;

use Slince\Shopify\Client;
use Slince\Shopify\Service\Contracts\ManagerInterface;

abstract class AbstractManager implements ManagerInterface
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Gets the resource name.
     *
     * @return string
     */
    abstract public function getResourceName();

    /**
     * Gets the model class.
     *
     * @return string
     */
    abstract public function getModelClass();

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function createMany(array $data, $modelClass = null)
    {
        $models = [];
        foreach ($data as $item) {
            $models[] = $this->fromArray($item, $modelClass);
        }

        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function fromArray(array $data, $modelClass = null)
    {
        if (null === $modelClass) {
            $modelClass = $this->getModelClass();
        }

        return $this->client->getHydrator()->hydrate($modelClass, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($model)
    {
        return $this->client->getHydrator()->extract($model);
    }
}