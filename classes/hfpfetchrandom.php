<?php
/**
 * @author      lagardere-active
 */
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
class hfpFetchRandom extends eZContentObjectTreeNode
	
{
 
    /*!
    */
    function &subTreeRandomByNodeID( $params = false ,$nodeID = 0 )
    {
        if ( !is_numeric( $nodeID ) and !is_array( $nodeID ) )
        {
            $retValue = null;
            return $retValue;
        }

        if ( $params === false )
        {
            $params = array( 'Depth'                    => false,
                             'Offset'                   => false,
                             //'OnlyTranslated'           => false,
                             'Language'                 => false,
                             'Limit'                    => false,
                             'AttributeFilter'          => false,
                             'ExtendedAttributeFilter'  => false,
                             'ClassFilterType'          => false,
                             'ClassFilterArray'         => false,
                             'GroupBy'                  => false );
        }

        $offset           = ( isset( $params['Offset'] ) && is_numeric( $params['Offset'] ) ) ? $params['Offset']             : false;
        //$onlyTranslated   = ( isset( $params['OnlyTranslated']      ) )                       ? $params['OnlyTranslated']     : false;
        $language         = ( isset( $params['Language']      ) )                             ? $params['Language']           : false;
        $limit            = ( isset( $params['Limit']  ) && is_numeric( $params['Limit']  ) ) ? $params['Limit']              : false;
        $depth            = ( isset( $params['Depth']  ) && is_numeric( $params['Depth']  ) ) ? $params['Depth']              : false;
        $depthOperator    = ( isset( $params['DepthOperator']     ) )                         ? $params['DepthOperator']      : false;
        $asObject         = ( isset( $params['AsObject']          ) )                         ? $params['AsObject']           : true;
        $loadDataMap      = ( isset( $params['LoadDataMap'] ) )                               ? $params['LoadDataMap']        : false;
        $groupBy          = ( isset( $params['GroupBy']           ) )                         ? $params['GroupBy']            : false;
        $mainNodeOnly     = ( isset( $params['MainNodeOnly']      ) )                         ? $params['MainNodeOnly']       : false;
        $ignoreVisibility = ( isset( $params['IgnoreVisibility']  ) )                         ? $params['IgnoreVisibility']   : false;
        $objectNameFilter = ( isset( $params['ObjectNameFilter']  ) )                         ? $params['ObjectNameFilter']   : false;

        $params['SortBy'] = false;
        if ( !isset( $params['ClassFilterType'] ) )
            $params['ClassFilterType'] = false;

        if ( $language )
        {
            if ( !is_array( $language ) )
            {
                $language = array( $language );
            }
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $sortingInfo             = eZContentObjectTreeNode::createSortingSQLStrings( $params['SortBy'] );
        $classCondition          = eZContentObjectTreeNode::createClassFilteringSQLString( $params['ClassFilterType'], $params['ClassFilterArray'] );
        if ( $classCondition === false )
        {
            eZDebug::writeNotice( "Class filter returned false" );
            $retValue = null;
            return $retValue;
        }

        $attributeFilter         = eZContentObjectTreeNode::createAttributeFilterSQLStrings( $params['AttributeFilter'], $sortingInfo );
        if ( $attributeFilter === false )
        {
            $retValue = null;
            return $retValue;
        }
        $extendedAttributeFilter = eZContentObjectTreeNode::createExtendedAttributeFilterSQLStrings( $params['ExtendedAttributeFilter'] );
        $mainNodeOnlyCond        = eZContentObjectTreeNode::createMainNodeConditionSQLString( $mainNodeOnly );

        $pathStringCond     = '';
        $notEqParentString  = '';
        // If the node(s) doesn't exist we return null.
        if ( !eZContentObjectTreeNode::createPathConditionAndNotEqParentSQLStrings( $pathStringCond, $notEqParentString, $this, $nodeID, $depth, $depthOperator ) )
        {
            $retValue = null;
            return $retValue;
        }

        $groupBySelectText  = '';
        $groupByText        = '';
        eZContentObjectTreeNode::createGroupBySQLStrings( $groupBySelectText, $groupByText, $groupBy );

        $useVersionName     = true;

        $versionNameTables  = eZContentObjectTreeNode::createVersionNameTablesSQLString ( $useVersionName );
        $versionNameTargets = eZContentObjectTreeNode::createVersionNameTargetsSQLString( $useVersionName );
        $versionNameJoins   = eZContentObjectTreeNode::createVersionNameJoinsSQLString  ( $useVersionName, false );

        $languageFilter = ' AND ' . eZContentLanguage::languagesSQLFilter( 'ezcontentobject' );

        if ( $language )
        {
            eZContentLanguage::clearPrioritizedLanguages();
        }
        $objectNameFilterSQL = eZContentObjectTreeNode::createObjectNameFilterConditionSQLString( $objectNameFilter );

        $limitation = ( isset( $params['Limitation']  ) && is_array( $params['Limitation']  ) ) ? $params['Limitation']: false;
        $limitationList = eZContentObjectTreeNode::getLimitationList( $limitation );
        $sqlPermissionChecking = eZContentObjectTreeNode::createPermissionCheckingSQL( $limitationList );

        // Determine whether we should show invisible nodes.
        $showInvisibleNodesCond = eZContentObjectTreeNode::createShowInvisibleSQLString( !$ignoreVisibility );

        $query = "SELECT ezcontentobject.*,
                       ezcontentobject_tree.*,
                       ezcontentclass.serialized_name_list as class_serialized_name_list,
                       ezcontentclass.identifier as class_identifier,
                       ezcontentclass.is_container as is_container
                       $groupBySelectText
                       $versionNameTargets
                   FROM
                      ezcontentobject_tree,
                      ezcontentobject,ezcontentclass
                      $versionNameTables
                      $sortingInfo[attributeFromSQL]
                      $attributeFilter[from]
                      $extendedAttributeFilter[tables]
                      $sqlPermissionChecking[from]
                   WHERE
                      $pathStringCond
                      $extendedAttributeFilter[joins]
                      $sortingInfo[attributeWhereSQL]
                      $attributeFilter[where]
                      ezcontentclass.version=0 AND
                      $notEqParentString
                      ezcontentobject_tree.contentobject_id = ezcontentobject.id  AND
                      ezcontentclass.id = ezcontentobject.contentclass_id AND
                      $mainNodeOnlyCond
                      $classCondition
                      $versionNameJoins
                      $showInvisibleNodesCond
                      $sqlPermissionChecking[where]
                      $objectNameFilterSQL
                      $languageFilter
                $groupByText";

        $ini = & eZINI::instance();
        $databaseImplementation = $ini->variable( 'DatabaseSettings', 'DatabaseImplementation' );
                
        if ( $databaseImplementation == "ezmysql" )
            $query .= " ORDER BY RAND()";
        elseif( $databaseImplementation == "ezpostgresql" )
 			$query .= " ORDER BY RANDOM()";
 			
        $db =& eZDB::instance();

        if ( !$offset && !$limit )
        {
            $nodeListArray = $db->arrayQuery( $query );
        }
        else
        {
            $nodeListArray = $db->arrayQuery( $query, array( 'offset' => $offset,
                                                             'limit'  => $limit ) );
        }

        if ( $asObject )
        {
            $retNodeList = eZContentObjectTreeNode::makeObjectsArray( $nodeListArray );
            if ( $loadDataMap )
                eZContentObject::fillNodeListAttributes( $retNodeList );
        }
        else
        {
            $retNodeList =& $nodeListArray;
        }

        // cleanup temp tables
        $db->dropTempTableList( $sqlPermissionChecking['temp_tables'] );

        return $retNodeList;
    }
	
}

?>