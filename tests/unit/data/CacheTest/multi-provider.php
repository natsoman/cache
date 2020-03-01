<?php

return [
    'string' => [
        [
            'stringKey0' => 'value0',
            'stringKey1' => 'value1',
            'stringKey2' => 'value2'
        ],
        [
            'stringKey0' => 'x�+�2�R*K�)M5P�\000/�',
            'stringKey1' => 'x�+�2�R*K�)M5T�\0002�',
            'stringKey2' => 'x�+�2�R*K�)M5R�\0005�'
        ],
    ],
    'empty' => [
        [
            'emptyStringKey0' => '',
            'emptyStringKey1' => '  ',
        ],
        [
            'emptyStringKey0' => 'x�+�2�RR�\000E�',
            'emptyStringKey1' => 'x�+�2�RRPP�\000
��',
        ],
    ],
    'bool' => [
        [
            'boolKey0' => false,
            'boolKey1' => true,
        ],
        [
            'boolKey0' => 'x�K�2�\000',
            'boolKey1' => 'x�K�2�\000�',
        ],
    ]
];