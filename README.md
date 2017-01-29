Hfp Fetch Random
=================

Fetches the random children of a node or a collection of nodes.

Version
=======

* The current version of Hfp Fetch Random is 0.2.0

* Last Major update: January 28, 2017

### Original Author

LIU Bin <bin.liu@lagardere-active.com>


Copyright
=========

* Hfp Fetch Random is copyright 1999 - 2017 Brookins Consulting and LIU Bin

* See: [COPYRIGHT.md](COPYRIGHT.md) for more information on the terms of the copyright and license


License
=======

Hfp Fetch Random is licensed under the GNU General Public License.

The complete license agreement is included in the [LICENSE](LICENSE) file.

Hfp Fetch Random is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License or at your
option a later version.

Hfp Fetch Random is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The GNU GPL gives you the right to use, modify and redistribute
Hfp Fetch Random under certain conditions. The GNU GPL license
is distributed with the software, see the file doc/LICENSE.

It is also available at [http://www.gnu.org/licenses/gpl.txt](http://www.gnu.org/licenses/gpl.txt)

You should have received a copy of the GNU General Public License
along with Hfp Fetch Random in doc/LICENSE.  If not, see [http://www.gnu.org/licenses/](http://www.gnu.org/licenses/).

Using Hfp Fetch Random under the terms of the GNU GPL is free (as in freedom).

For more information or questions please contact: license@brookinsconsulting.com


Requirements
============

The following requirements exists for using Hfp Fetch Random extension:


### eZ Publish version

* Make sure you use eZ Publish version 5.x (required) or higher.

* Designed and tested with eZ Publish Community Project GitHub Release tag (via composer) v2015.01.3


### PHP version

* Make sure you have PHP 5.x or higher.


Features
========

This solution provides the following:

* Custom Content Fetch Function Module 
    * Fetch Random Content Tree Node Objects


Installation
============

### Extension Installation via Composer

Run the following command from your project root to install the extension:

    bash$ composer require brookinsconsulting/hfpfetchrandom dev-master;


### Extension Activation

Activate this extension by adding the following to your `settings/override/site.ini.append.php`:

    [ExtensionSettings]
    # <snip existing active extensions list />
    ActiveExtensions[]=hfpfetchrandom


### Regenerate kernel class override autoloads

Regenerate autoloads (Required).

    php ./bin/php/ezpgenerateautoloads.php;


### Clear the caches

Clear eZ Publish Platform / eZ Publish Legacy caches (Required).

    php ./bin/php/ezcache.php --clear-all;


Configuration
=============

There are currently no configuration required.

Usage
=====

The solution is configured to work virtually by default once properly installed.

* Add the fetch function provided into your template.

### Usage Options

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

#### Returns

An array of ezcontentobjecttreenode objects or FALSE.


### Usage Example 1

The following example hfpfetchrandom, list fetch usage fetches three folder node objects randomly.

    {def $tests = fetch( 'hfpfetchrandom', 'list', hash( 'parent_node_id', 2,
                                                         'offset',0,
                                                         'limit',3,
                                                         'class_filter_type',  'include',
                                                         'class_filter_array', array('folder')
                                                         ) )}
    {foreach $tests as $test}
      {$test.name}
    {/foreach}


Troubleshooting
===============

### Read the FAQ

Some problems are more common than others. The most common ones are listed in the the [doc/FAQ.md](doc/FAQ.md)


### Support

If you have find any problems not handled by this document or the FAQ you can contact Brookins Consulting through the support system: [http://brookinsconsulting.com/contact](http://brookinsconsulting.com/contact)
