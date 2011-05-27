<?php

function __autoload($class) {
    is_file($file = './lib/' . strtr($class, '_', '/') . '.php') && require_once $file;
}

echo '<pre>';

$parser = new Parser();
$parser->yydebug = false;

// Output Demo
$stmts = $parser->yyparse(new Lexer(
    '<?php
        x::$y[z];
        $x->y[z];
        $x->y[z][k]->l()->m[t];
        $x->y[z]();
        $x->$y[z]();
        $x->$$y[z]();'
    ),
    function ($msg) {
        echo $msg;
    }
);
if (false !== $stmts) {
    foreach ($stmts as $stmt) {
        echo htmlspecialchars($stmt), "\n";
    }
}

echo "\n\n";

$prettyPrinter = new PrettyPrinter_Zend;
echo $prettyPrinter->pStmts($parser->yyparse(new Lexer(file_get_contents(__FILE__)), function ($msg) {
    echo $msg;
}));

echo "\n\n";

// Correctness Demo
$GST = microtime(true);
foreach (new RecursiveIteratorIterator(
             new RecursiveDirectoryIterator('.'),
             RecursiveIteratorIterator::LEAVES_ONLY)
         as $file) {
    if ('.php' !== substr($file, -4)) {
        continue;
    }

    set_time_limit(5);

    $startTime = microtime(true);
    $stmts = $parser->yyparse(
        new Lexer(file_get_contents($file)),
        function($msg) {
            echo $msg, "\n";
        }
    );
    $endTime = microtime(true);

    echo str_pad($file . ': ', 120, ' '), (false !== $stmts ? 'successful' : 'ERROR'), ' (', $endTime - $startTime, ')', "\n";

    flush();
}
echo microtime(true) - $GST;