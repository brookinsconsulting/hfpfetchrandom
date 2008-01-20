<?php
/**
 * @author      LIU Bin <bin.liu@lagardere-active.com>
 */
include_once('kernel/content/ezcontentfunctioncollection.php');
include_once('extension/hfpfetchrandom/classes/hfpfetchrandom.php');	 
include_once( 'kernel/error/errors.php' );

class hfpRamdomFunctionCollection extends eZContentFunctionCollection
		 
	
{
    /*!
     Constructor
    */
  
	function fetchObjectTreeRamdom( $parentNodeID, $onlyTranslated, $language, $offset, $limit, $depth, $depthOperator,
                              $classID, $attribute_filter, $extended_attribute_filter, $class_filter_type, $class_filter_array,
                              $groupBy, $mainNodeOnly, $ignoreVisibility, $limitation, $asObject, $objectNameFilter, $loadDataMap = true )
    {
        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
        $treeParameters = array( 'Offset' => $offset,
                                 'OnlyTranslated' => $onlyTranslated,
                                 'Language' => $language,
                                 'Limit' => $limit,
                                 'Limitation' => $limitation,
                                 'class_id' => $classID,
                                 'AttributeFilter' => $attribute_filter,
                                 'ExtendedAttributeFilter' => $extended_attribute_filter,
                                 'ClassFilterType' => $class_filter_type,
                                 'ClassFilterArray' => $class_filter_array,
                                 'IgnoreVisibility' => $ignoreVisibility,
                                 'ObjectNameFilter' => $objectNameFilter,
                                 'MainNodeOnly' => $mainNodeOnly );
        if ( is_array( $groupBy ) )
        {
            $groupByHash = array( 'field' => $groupBy[0],
                                  'type' => false );
            if ( isset( $groupBy[1] ) )
                $groupByHash['type'] = $groupBy[1];
            $treeParameters['GroupBy'] = $groupByHash;
        }
        if ( $asObject !== null )
            $treeParameters['AsObject'] = $asObject;
        if ( $loadDataMap )
            $treeParameters['LoadDataMap'] = true;
        if ( $depth !== false )
        {
            $treeParameters['Depth'] = $depth;
            $treeParameters['DepthOperator'] = $depthOperator;
        }

        $children = null;
        if ( is_numeric( $parentNodeID ) or is_array( $parentNodeID ) )
        {
            $children =& hfpFetchRandom::subTreeRandomByNodeID( $treeParameters,
                                                           $parentNodeID );
        }

        if ( $children === null )
        {
            $result = array( 'error' => array( 'error_type' => 'kernel',
                                               'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        }
        else
        {
            $result = array( 'result' => &$children );
        }
        return $result;
    }

}
?>