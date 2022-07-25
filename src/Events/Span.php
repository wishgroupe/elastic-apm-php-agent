<?php

namespace PhilKra\Events;

use PhilKra\Helper\Encoding;
use PhilKra\Helper\Timer;
use PhilKra\Traits\Events\Stacktrace;

/**
 *
 * Spans
 *
 * @link https://www.elastic.co/guide/en/apm/server/master/span-api.html
 *
 */
class Span extends TraceableEvent implements \JsonSerializable
{
    use Stacktrace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \PhilKra\Helper\Timer
     */
    private $timer;

    /**
     * @var int
     */
    private $duration = 0;

    /**
     * @var string
     */
    private $action = null;

    /**
     * @var string
     */
    private $type = 'request';

    /**
     * @var string
     */
    private $subtype = '';

    /**
     * @var string
     */
    private $outcome;

    /**
     * @var mixed array|null
     */
    private $stacktrace = [];

    /**
     * @var bool
     */
    private $sync = true;

    /**
     * @param string $name
     * @param EventBean $parent
     */
    public function __construct(string $name, EventBean $parent, $start = null)
    {
        parent::__construct([]);
        $this->name  = trim($name);
        $this->timer = new Timer($start);
        if($start) {
            $this->setTimestamp(round($start * 1000000));
        }
        $this->setParent($parent);
    }

    /**
     * Start the Timer
     *
     * @return void
     */
    public function start()
    {
        $this->timer->start();
    }

    /**
     * Stop the Timer
     *
     * @param integer|null $duration
     *
     * @return void
     */
    public function stop(int $duration = null)
    {
        $this->timer->stop();
        $this->duration = $duration ?? round($this->timer->getDurationInMilliseconds(), 3);
    }

    /**
    * Get the Event Name
    *
    * @return string
    */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the Span's Type
     *
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = trim($action);
    }

    /**
     * Set the Spans' Action
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = trim($type);
    }

    /**
     * Set the Spans' Sub type
     * @param string $subtype
     */
    public function setSubType(string $subtype) {
        $this->subtype = trim($subtype);
    }

    /**
     * Set a complimentary Stacktrace for the Span
     *
     * @link https://www.elastic.co/guide/en/apm/server/master/span-api.html
     *
     * @param array $stacktrace
     */
    public function setStacktrace(array $stacktrace)
    {
        $this->stacktrace = $stacktrace;
    }

    /**
     * Specify if the span is sync or async
     * @param bool $sync
     * @return void
     */
    public function setSync(bool $sync)
    {
        $this->sync = $sync;
    }

    /**
     * set Span's outcome
     * @param string|null $outcome
     * @return void
     */
    public function setOutcome(?string $outcome)
    {
        $this->outcome = trim($outcome);
    }

    /**
     * get span's sync
     * @return bool
     */
    public function getSync() : bool
    {
        return $this->sync;
    }

    /**
     * get span's outcome (success, failure)
     * @return string
     */
    public function getOutcome() : ?string
    {
        return $this->outcome;
    }

    /**
     * Serialize Span Event
     *
     * @link https://www.elastic.co/guide/en/apm/server/master/span-api.html
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'span' => [
                'id'             => $this->getId(),
                'transaction_id' => $this->getParentId(),
                'trace_id'       => $this->getTraceId(),
                'parent_id'      => $this->getParentId(),
                'type'           => Encoding::keywordField($this->type),
                'subtype'        => Encoding::keywordField($this->subtype),
                'action'         => Encoding::keywordField($this->action),
                'context'        => $this->getContext(),
                'duration'       => $this->duration,
                'name'           => Encoding::keywordField($this->getName()),
                'stacktrace'     => $this->stacktrace,
                'sync'           => $this->getSync(),
                'outcome'        => $this->getOutcome(),
                'timestamp'      => $this->getTimestamp(),
            ]
        ];
    }
}
