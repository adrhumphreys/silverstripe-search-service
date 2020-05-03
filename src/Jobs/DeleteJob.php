<?php

namespace SilverStripe\SearchService\Jobs;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;
use SilverStripe\SearchService\Service\Indexer;

/**
 * Remove an item from search async. This method works well
 * for performance and batching large operations
 */
class DeleteJob extends AbstractQueuedJob implements QueuedJob
{
    /**
     * @param string $itemClass
     * @param int $itemId
     */
    public function __construct($itemClass = null, $itemId = null)
    {
        if ($itemClass) {
            $this->itemClass = $itemClass;
        }
        if ($itemId) {
            $this->itemID = $itemId;
        }
    }


    /**
     * Defines the title of the job.
     *
     * @return string
     */
    public function getTitle()
    {
        return sprintf(
            'Search service remove %s',
            $this->itemID
        );
    }

    /**
     * @return int
     */
    public function getJobType()
    {
        $this->totalSteps = 1;

        return QueuedJob::IMMEDIATE;
    }

    /**
     * This is called immediately before a job begins - it gives you a chance
     * to initialise job data and make sure everything's good to go
     *
     * What we're doing in our case is to queue up the list of items we know we need to
     * process still (it's not everything - just the ones we know at the moment)
     *
     * When we go through, we'll constantly add and remove from this queue, meaning
     * we never overload it with content
     */
    public function setup()
    {
    }

    /**
     * Lets process a single node
     */
    public function process()
    {
        try {
            $indexer = Injector::inst()->create(Indexer::class);
            $indexer->deleteItem($this->itemClass, $this->itemID);
        } catch (Exception $e) {
            Injector::inst()->create(LoggerInterface::class)->error($e);
        }

        $this->isComplete = true;

        return;
    }
}
