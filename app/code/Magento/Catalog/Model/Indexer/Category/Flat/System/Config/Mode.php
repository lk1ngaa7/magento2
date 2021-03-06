<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\System\Config;

/**
 * Flat category on/off backend
 */
class Mode extends \Magento\Framework\App\Config\Value
{
    /** @var \Magento\Indexer\Model\Indexer\State */
    protected $indexerState;

    /** @var \Magento\Indexer\Model\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Indexer\Model\IndexerRegistry $indexerRegistry
     * @param \Magento\Indexer\Model\Indexer\State $indexerState
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Indexer\Model\IndexerRegistry $indexerRegistry,
        \Magento\Indexer\Model\Indexer\State $indexerState,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerState = $indexerState;
    }

    /**
     * Set after commit callback
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->_getResource()->addCommitCallback([$this, 'processValue']);
        return $this;
    }

    /**
     * Process flat enabled mode change
     *
     * @return void
     */
    public function processValue()
    {
        if ((bool)$this->getValue() != (bool)$this->getOldValue()) {
            if ((bool)$this->getValue()) {
                $this->indexerState->loadByIndexer(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID);
                $this->indexerState->setStatus(\Magento\Indexer\Model\Indexer\State::STATUS_INVALID);
                $this->indexerState->save();
            } else {
                $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID)
                    ->setScheduled(false);
            }
        }
    }
}
