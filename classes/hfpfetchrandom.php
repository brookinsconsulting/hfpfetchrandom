<?php

/**
// Created on: <2007-12-20 by LIU Bin bin.liu@lagardere-active.com>
 *
 */
class hfpFetchRandom extends eZContentObjectTreeNode
{
   static function subTreeRandomByNodeID( $params = false, $nodeID = 0 )
    {
        if ( !is_numeric( $nodeID ) and !is_array( $nodeID ) )
        {
            return null;
        }

        if ( $params === false )
        {
            $params = array( 'Depth'                    => false,
                             'Offset'                   => false,
                             //'OnlyTranslated'           => false,
                             'Language'                 => false,
                             'Limit'                    => false,
                             'SortBy'                   => false,
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

        if ( $offset < 0 )
        {
            $offset = abs( $offset );
        }
        
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

        $allowCustomSorting = false;
        if ( isset( $params['ExtendedAttributeFilter'] ) && is_array ( $params['ExtendedAttributeFilter'] ) )
        {
            $allowCustomSorting = true;
        }

        $sortingInfo             = hfpFetchRandom::createSortingSQLStrings( $params['SortBy'], 'ezcontentobject_tree', $allowCustomSorting );
        $classCondition          = hfpFetchRandom::createClassFilteringSQLString( $params['ClassFilterType'], $params['ClassFilterArray'] );
        if ( $classCondition === false )
        {
            eZDebug::writeNotice( "Class filter returned false" );
            return null;
        }

        $attributeFilter         = hfpFetchRandom::createAttributeFilterSQLStrings( $params['AttributeFilter'], $sortingInfo );
        if ( $attributeFilter === false )
        {
            return null;
        }
        $extendedAttributeFilter = hfpFetchRandom::createExtendedAttributeFilterSQLStrings( $params['ExtendedAttributeFilter'] );
        $mainNodeOnlyCond        = hfpFetchRandom::createMainNodeConditionSQLString( $mainNodeOnly );

        $pathStringCond     = '';
        $notEqParentString  = '';
        // If the node(s) doesn't exist we return null.
        if ( !hfpFetchRandom::createPathConditionAndNotEqParentSQLStrings( $pathStringCond, $notEqParentString, $nodeID, $depth, $depthOperator ) )
        {
            return null;
        }

        $groupBySelectText  = '';
        $groupByText        = '';
        hfpFetchRandom::createGroupBySQLStrings( $groupBySelectText, $groupByText, $groupBy );

        $useVersionName     = true;

        $versionNameTables  = hfpFetchRandom::createVersionNameTablesSQLString ( $useVersionName );
        $versionNameTargets = hfpFetchRandom::createVersionNameTargetsSQLString( $useVersionName );
        $versionNameJoins   = hfpFetchRandom::createVersionNameJoinsSQLString  ( $useVersionName, false );

        $languageFilter = ' AND ' . eZContentLanguage::languagesSQLFilter( 'ezcontentobject' );

        if ( $language )
        {
            eZContentLanguage::clearPrioritizedLanguages();
        }
        $objectNameFilterSQL = hfpFetchRandom::createObjectNameFilterConditionSQLString( $objectNameFilter );

        $limitation = ( isset( $params['Limitation']  ) && is_array( $params['Limitation']  ) ) ? $params['Limitation']: false;
        $limitationList = hfpFetchRandom::getLimitationList( $limitation );
        $sqlPermissionChecking = hfpFetchRandom::createPermissionCheckingSQL( $limitationList );

        // Determine whether we should show invisible nodes.
        $showInvisibleNodesCond = hfpFetchRandom::createShowInvisibleSQLString( !$ignoreVisibility );

        $query = "SELECT DISTINCT
                       ezcontentobject.*,
                       ezcontentobject_tree.*,
                       ezcontentclass.serialized_name_list as class_serialized_name_list,
                       ezcontentclass.identifier as class_identifier,
                       ezcontentclass.is_container as is_container
                       $groupBySelectText
                       $versionNameTargets
                       $sortingInfo[attributeTargetSQL]
                       $extendedAttributeFilter[columns]
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
                
		$ini = eZINI::instance();
        $databaseImplementation = $ini->variable( 'DatabaseSettings', 'DatabaseImplementation' );
                
        if ( $databaseImplementation == "ezmysql" )
            $query .= " ORDER BY RAND()";
        elseif( $databaseImplementation == "ezpostgresql" )
 			$query .= " ORDER BY RANDOM()";
        $db = eZDB::instance();

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
            $retNodeList = hfpFetchRandom::makeObjectsArray( $nodeListArray );
            if ( $loadDataMap )
                eZContentObject::fillNodeListAttributes( $retNodeList );
        }
        else
        {
            $retNodeList = $nodeListArray;
        }

        // cleanup temp tables
        $db->dropTempTableList( $sqlPermissionChecking['temp_tables'] );

        return $retNodeList;
    }
	
}

?>