<?php

namespace atk4\ui\FormLayout;

use atk4\ui\Exception;

/**
 * Custom Layout for a form (user-defined HTML).
 */
class Custom extends _Abstract
{
    /** @var {@inheritdoc} */
    public $defaultTemplate;

    public function init(): void
    {
        parent::init();

        if (!$this->template) {
            throw new Exception('You must specify template for FormLayout/Custom. Try [\'Custom\', \'defaultTemplate\'=>\'./yourform.html\']');
        }
    }

    /**
     * Adds Button into {$Buttons}.
     *
     * @param Button|array|string $seed
     *
     * @return \atk4\ui\Button
     */
    public function addButton($seed)
    {
        return $this->add($this->mergeSeeds([\atk4\ui\Button::class], $seed), 'Buttons');
    }
}
