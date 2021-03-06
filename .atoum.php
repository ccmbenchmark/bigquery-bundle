<?php
    use mageekguy\atoum\reports;
    use mageekguy\atoum\reports\coverage;
    use mageekguy\atoum\writers\std;

    $runner->addTestsFromDirectory(__DIR__ . '/src/BigQueryBundle/Tests');

    $script->addDefaultReport();

    $coverage = new coverage\html();
    $coverage->addWriter(new std\out());
    $coverage->setOutPutDirectory(__DIR__ . '/coverage');
    $runner->addReport($coverage);
