<?php
namespace PHPDaemon\Core;

use PHPDaemon\Structures\StackCallbacks;

/**
 * DeferredEvent class.
 */
class DeferredEvent {
	use \PHPDaemon\Traits\ClassWatchdog;
	use \PHPDaemon\Traits\StaticObjectWatchdog;

	/**
	 * @TODO DESCR
	 */
	const STATE_WAITING = 1;
	/**
	 * @TODO DESCR
	 */
	const STATE_RUNNING = 2;
	/**
	 * @TODO DESCR
	 */
	const STATE_DONE    = 3;

	/**
	 * @var \PHPDaemon\Structures\StackCallbacks
	 */
	protected $listeners;
	/**
	 * @var mixed
	 */
	protected $result;
	/**
	 * @var int
	 */
	protected $state;
	/**
	 * @var
	 */
	protected $args;
	/**
	 * @var
	 */
	protected $onRun;
	/**
	 * @var
	 */
	public $parent;

	/**
	 * @TODO DESCR
	 * @param $cb
	 */
	public function __construct($cb) {
		$this->state     = self::STATE_WAITING;
		$this->onRun     = $cb;
		$this->listeners = new StackCallbacks;
	}

	/**
	 * @param callable $cb
	 */
	public function setProducer($cb) {
		$this->onRun = $cb;
	}

	/**
	 * @param mixed $result
	 */
	public function setResult($result = null) {
		$this->result = $result;
		$this->state  = self::STATE_DONE;
		$this->listeners->executeAll($this->result);
	}

	/**
	 * @TODO DESCR
	 */
	public function cleanup() {
		$this->listeners = [];
		$this->onRun     = null;
		$this->args      = [];
	}

	/**
	 * @TODO DESCR
	 * @param callable $cb
	 */
	public function addListener($cb) {
		if ($this->state === self::STATE_DONE) {
			call_user_func($cb, $this);
			return;
		}
		$this->listeners->push($cb);
		if ($this->state === self::STATE_WAITING) {
			$i = 1;
			$n = func_num_args();
			while ($i < $n) {
				$this->args[] = func_get_arg($i);
				++$i;
			}
			$this->state = self::STATE_RUNNING;
			call_user_func($this->onRun, $this);
		}
	}

	/**
	 * @TODO DESCR
	 * @param callable $cb
	 * @param array $params
	 */
	public function __invoke($cb, $params = []) {
		$this->addListener($cb, $params);
	}
}
