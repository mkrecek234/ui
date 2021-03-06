<?php

namespace atk4\ui\demo;

/**
 * This view is designed to verify various things about it's positioning, e.g.
 * can its callbacks reach itself and potentially more.
 */
class ViewTester extends \atk4\ui\View
{
    public function init(): void
    {
        parent::init();

        $label = \atk4\ui\Label::addTo($this, ['CallBack', 'detail' => 'fail', 'red']);
        $reload = new \atk4\ui\jsReload($this, [$this->name => 'ok']);

        if (isset($_GET[$this->name])) {
            $label->class[] = 'green';
            $label->detail = 'success';
        } else {
            $this->js(true, $reload);
            $this->js(true, new \atk4\ui\jsExpression('var s = Date.now(); var i=setInterval(function() { var p = Date.now()-s; var el=$[]; el.find(".detail").text(p+"ms"); if(el.is(".green")) { clearInterval(i); }}, 100)', [$label]));
        }
    }
}
