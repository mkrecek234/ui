<?php

namespace atk4\ui;

use atk4\core\AppScopeTrait;
use atk4\core\DIContainerTrait;
use atk4\core\InitializerTrait;
use atk4\core\StaticAddToTrait;
use atk4\core\TrackableTrait;

/**
 * Add this object to your render tree and it will expose a unique URL which, when
 * executed directly will perform a PHP callback that you set().
 *
 * $button = Button::addTo($layout);
 * $button->set('Click to do something')->link(
 *      Callback::addTo($button)
 *          ->set(function(){
 *              do_something();
 *          })
 *          ->getURL()
 *  );
 *
 * @property View $owner
 */
class Callback
{
    use TrackableTrait;
    use AppScopeTrait;
    use DIContainerTrait;
    use InitializerTrait {
        init as _init;
    }
    use StaticAddToTrait;

    /**
     * Will look for trigger in the POST data. Will not care about URL, but
     * $_POST[$this->postTrigger] must be set.
     *
     * @var string|bool
     */
    public $postTrigger = false;

    /**
     * Contains either false if callback wasn't triggered or the value passed
     * as an argument to a call-back.
     *
     * e.g. following URL of getURL('test') will result in $triggered = 'test';
     *
     * @var string|false
     */
    public $triggered = false;

    /**
     * Specify a custom GET trigger here.
     *
     * @var string|null
     */
    public $urlTrigger;

    /** @var bool stick callback url argument to view or application. */
    public $appSticky = false;

    /**
     * Initialize object and set default properties.
     *
     * @param array|string $defaults
     */
    public function __construct($defaults = [])
    {
        $this->setDefaults($defaults);
    }

    /**
     * Initialization.
     */
    public function init(): void
    {
        $this->_init();

        if (!$this->app) {
            throw new Exception('Call-back must be part of a RenderTree');
        }

        if (!$this->urlTrigger) {
            $this->urlTrigger = $this->name;
        }

        if ($this->postTrigger === true) {
            $this->postTrigger = $this->name;
        }

        $this->appSticky ? $this->app->stickyGet($this->urlTrigger) : $this->owner->stickyGet($this->urlTrigger);
    }

    /**
     * Executes user-specified action when call-back is triggered.
     *
     * @param callable $callback
     * @param array    $args
     *
     * @return mixed|null
     */
    public function set($callback, $args = [])
    {
        if ($this->postTrigger) {
            if (isset($_POST[$this->postTrigger])) {
                $this->app->catch_runaway_callbacks = false;
                $this->triggered = $_POST[$this->postTrigger];

                $t = $this->app->run_called;
                $this->app->run_called = true;
                $ret = call_user_func_array($callback, $args);
                $this->app->run_called = $t;

                return $ret;
            }
        } else {
            if (isset($_GET[$this->urlTrigger])) {
                $this->app->catch_runaway_callbacks = false;
                $this->triggered = $_GET[$this->urlTrigger];

                $t = $this->app->run_called;
                $this->app->run_called = true;
                $this->owner->stickyGet($this->urlTrigger);
                $ret = call_user_func_array($callback, $args);
                //$this->app->stickyForget($this->name);
                $this->app->run_called = $t;

                return $ret;
            }
        }
    }

    /**
     * Terminate this callback
     * by rendering the owner view.
     */
    public function terminate()
    {
        if ($this->canTerminate()) {
            $this->app->terminateJSON($this->owner);
        }
    }

    /**
     * Prevent callback from terminating during a reload.
     */
    protected function canTerminate(): bool
    {
        $reload = $_GET['__atk_reload'] ?? null;

        return !$reload || $this->owner->name === $reload;
    }

    /**
     * Is callback triggered?
     *
     * @return bool
     */
    public function triggered()
    {
        return $_GET[$this->urlTrigger] ?? false;
    }

    /**
     * Return URL that will trigger action on this call-back. If you intend to request
     * the URL direcly in your browser (as iframe, new tab, or document location), you
     * should use getURL instead.
     *
     * @param string $mode
     *
     * @return string
     */
    public function getJSURL($mode = 'ajax')
    {
        return $this->owner->jsURL([$this->urlTrigger => $mode, '__atk_callback' => 1], (bool) $this->postTrigger);
    }

    /**
     * Return URL that will trigger action on this call-back. If you intend to request
     * the URL loading from inside JavaScript, it's always advised to use getJSURL instead.
     *
     * @param string $mode
     *
     * @return string
     */
    public function getURL($mode = 'callback')
    {
        return $this->owner->url([$this->urlTrigger => $mode, '__atk_callback' => 1], (bool) $this->postTrigger);
    }
}
