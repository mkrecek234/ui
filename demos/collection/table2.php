<?php

namespace atk4\ui\demo;

require_once __DIR__ . '/../atk-init.php';

$data = [
    ['id' => 1, 'action' => 'Salary', 'amount' => 200],
    ['id' => 2, 'action' => 'Purchase goods', 'amount' => -120],
    ['id' => 3, 'action' => 'Tax', 'amount' => -40],
];

$m = new \atk4\data\Model(new \atk4\data\Persistence\Static_($data));
$m->getField('amount')->type = 'money';

\atk4\ui\Header::addTo($app, ['Table with various headers', 'subHeader' => 'Demonstrates how you can add subheaders, footnotes and other insertions into your data table', 'icon' => 'table']);

$table = \atk4\ui\Table::addTo($app);
$table->setModel($m, ['action']);
$table->addColumn('amount', [\atk4\ui\TableColumn\Money::class]);

// Table template can be tweaked directly
$table->template->appendHTML('SubHead', '<tr class="center aligned"><th colspan=2>This is sub-header, goes inside "thead" tag</th></tr>');
$table->template->appendHTML('Body', '<tr class="center aligned"><td colspan=2>This is part of body, goes before other rows</td></tr>');

// Hook can be used to display data before row. You can also inject and format extra rows.
$table->onHook(\atk4\ui\Lister::HOOK_BEFORE_ROW, function (\atk4\ui\Table $table) {
    if ($table->current_id === 2) {
        $table->template->appendHTML('Body', '<tr class="center aligned"><td colspan=2>This goes above row with ID=2 (' . $table->current_row['action'] . ')</th></tr>');
    } elseif ($table->current_row['action'] === 'Tax') {
        // renders current row
        $table->renderRow();

        // adjusts data for next render
        $table->model->set(['action' => 'manually injected row after Tax', 'amount' => -0.02]);
    }
});

$table->template->appendHTML('Foot', '<tr class="center aligned"><td colspan=2>This will appear above totals</th></tr>');
$table->addTotals(['action' => 'Totals:', 'amount' => ['sum']]);

\atk4\ui\Header::addTo($app, ['Columns with multiple formats', 'subHeader' => 'Single column can use logic to swap out formatters', 'icon' => 'table']);

$table = \atk4\ui\Table::addTo($app);
$table->setModel($m, ['action']);

// copy of amount through a PHP callback
$m->addExpression('amount_copy', [function (\atk4\data\Model $m) {
    return $m->get('amount');
}, 'type' => 'money']);

// column with 2 decorators that stack. Money will use red ink and alignment, format will change text.
$table->addColumn('amount', [\atk4\ui\TableColumn\Money::class]);
$table->addDecorator('amount', [\atk4\ui\TableColumn\Template::class, 'Refunded: {$amount}']);

// column which uses selective format depending on condition
$table->addColumn('amount_copy', [\atk4\ui\TableColumn\Multiformat::class, function ($a, $b) {
    if ($a->get('amount_copy') > 0) {
        // Two formatters together
        return [\atk4\ui\TableColumn\Link::class, \atk4\ui\TableColumn\Money::class];
    } elseif (abs($a->get('amount_copy')) < 50) {
        // One formatter, but inject template and some attributes
        return [[
            \atk4\ui\TableColumn\Template::class,
            'too <b>little</b> to <u>matter</u>',
            'attr' => ['all' => ['class' => ['right aligned single line']]],
        ]];
    }

    // Short way is to simply return seed
    return \atk4\ui\TableColumn\Money::class;
}, 'attr' => ['all' => ['class' => ['right aligned singel line']]]]);

\atk4\ui\Header::addTo($app, ['Table with resizable columns', 'subHeader' => 'Just drag column header to resize', 'icon' => 'table']);

$table = \atk4\ui\Table::addTo($app);
$table->setModel($m);
$table->addClass('celled')->resizableColumn();
