<?php

return [
    'string' => ['value', 'stringKey', 's:5:"value";', 'x�+�2�R*K�)MU�\0008�'],
    'emptyString' => ['', 'emptyStringKey', 's:0:"";', 'x�+�2�RR�\000E�'],
    'emptyStrings' => [
        '      
            ',
        'emptyStringKey',
        's:0:"";',
        'x�+�2�RR�\000E�'
    ],
    'null' => [null, 'nullKey', 'N;', 'x��\000\000�\000�'],
    'false' => [false, 'falseKey', 'b:0;', 'x�K�2�\000'],
    'object' => [new stdClass(), 'objectKey', 'O:8:"stdClass":0:{}', 'x�󷲰R*.Iq�I,.V�2���\000:F'],
    'int' => [1, 'intKey', 'i:1;', 'x�˴2�\000�'],
    'double' => [0.11, 'doubleKey', 'd:0.11;', 'x�K�2�34�\000��'],
    'nestedArray' => [
        ['a' => 3, 'b' => 2, ['aa' => 22]],
        'nestedArrayKey',
        'a:3:{s:1:"a";i:3;s:1:"b";i:2;i:0;a:1:{s:2:"aa";i:22;}}',
        'x�K�2��.�2�RJT�δ2���@l# 6�N����*���kk�8�'
    ],
];