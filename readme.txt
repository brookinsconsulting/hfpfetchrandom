/**
 * @author      lagardere-active
 */
 

Summary
Fetches the random children of a node or a collection of nodes.
Usage

fetch( 'hfpfetchrandom', 'list',
 hash( 'parent_node_id',            parent_node_id,
     [ 'offset',                    offset,                    ]
     [ 'limit',                     limit,                     ]
     [ 'attribute_filter',          attribute_filter,          ]
     [ 'extended_attribute_filter', extended_attribute_filter, ]
     [ 'class_filter_type',         class_filter_type,         ]
     [ 'class_filter_array',        class_filter_array,        ]
     [ 'only_translated',           only_translated,           ]
     [ 'language',                  language,                  ]
     [ 'main_node_only',            main_node_only,            ]
     [ 'as_object',                 as_object,                 ]
     [ 'depth',                     depth,                     ]
     [ 'limitation',                limitation                 ]
     [ 'ignore_visibility',         ignore_visibility          ] ) )

Returns

An array of ezcontentobjecttreenode objects or FALSE. 


exemple : 

	     {def $tests = fetch( 'hfpfetchrandom', 'list', hash(	'parent_node_id', 2,
															'offset',0,
															'limit',3,
 										                	'class_filter_type',  'include',
									                		'class_filter_array', array('folder')
															 ) )}
		{foreach $tests as $test}
		{$test.name}
		{/foreach}