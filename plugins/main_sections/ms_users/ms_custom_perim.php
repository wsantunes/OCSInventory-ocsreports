<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

/*
 * Add tags for users
 * 
 */
if(AJAX){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}

$form_name='taguser';
$table_name=$form_name;
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;

//BEGIN SHOW ACCOUNTINFO
require_once 'require/function_commun.php';
require_once('require/function_admininfo.php');
$info_tag=find_info_accountinfo('1','COMPUTERS');
if (is_array($info_tag)){
	foreach ($info_tag as $key=>$value){
		$info_value_tag= accountinfo_tab($value['id']);		
		if (is_array($info_value_tag)){
			$tab_options['REPLACE_VALUE'][$value['comment']]=$info_value_tag;
		}		
	}
}
//END SHOW ACCOUNTINFO

printEnTete($l->g(616)." ".$protectedGet["id"] );
if( $protectedPost['newtag'] != "" ) {
	if (isset($protectedPost['use_generic_0'])){
		if (is_array($info_value_tag)){
			$arg=str_replace(array("*","?"), "", $protectedPost["newtag"]); 
			$array_result=find_value_in_field(1,$arg);
		}else{
			$arg=str_replace(array("*","?"), array("%","_"), $protectedPost["newtag"]); 
			$sql="select distinct TAG from accountinfo where TAG like '%s'";
			$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
			while ($val_account_data = mysqli_fetch_array( $res ))
				$array_result[]=$val_account_data['TAG'];
		}
		
	}else
		$array_result[]=$protectedPost["newtag"];
	
	
	$tab_options['CACHE']='RESET';
	$sql="insert into tags (tag,login) values ('%s','%s')";
	$i=0;
	while (isset($array_result[$i])){
		$arg=array($array_result[$i],$protectedGet["id"]);
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
		$i++;
	}
	
	
	unset($protectedPost['newtag']);
}

//suppression d'une liste de tag
if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	$sql="DELETE FROM tags WHERE tag in ";
	$arg_sql=array();
	$sql=mysql2_prepare($sql,$arg_sql,$protectedPost['del_check']);
	$sql['SQL'].=" AND login='%s'";
	array_push($sql['ARG'],$protectedGet["id"]);
	mysql2_query_secure($sql['SQL'],$_SESSION['OCS']["writeServer"],$sql['ARG']);
	$tab_options['CACHE']='RESET';	
}

if(isset($protectedPost['SUP_PROF'])) {
	$sql="DELETE FROM tags WHERE tag='%s' AND login='%s'";
	$arg=array($protectedPost['SUP_PROF'],$protectedGet["id"]);
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
}
echo "<br>";

echo open_form($form_name);
$list_fields=array('TAG'=>'tag',
				);			

$tab_options['FILTRE']=array_flip($list_fields);
$tab_options['FILTRE']['NAME']=$l->g(49);
asort($tab_options['FILTRE']); 
$list_fields['SUP']='tag';
$list_fields['CHECK']='tag';

$list_col_cant_del=array('SUP'=>'SUP','CHECK'=>'CHECK');
$default_fields= array('TAG'=>'tag');
$sql=prepare_sql_tab($list_fields,$list_col_cant_del);
$sql['SQL'] = "SELECT tag FROM tags where login='%s'";
$sql['ARG'] = array($protectedGet["id"]);
$tab_options['ARG_SQL']=$sql['ARG'];
$queryDetails  = $sql['SQL'];
$tab_options['LBL']['SUP']=$l->g(122);

ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
$img['image/delete.png']=$l->g(162);
del_selection($form_name);

if (is_array($info_value_tag) and !isset($protectedPost['use_generic_0'])){
	$type=2;
}else{
	$type=0;
	$info_value_tag=$protectedPost['newtag'];
}
	
$select_choise=show_modif($info_value_tag,'newtag',$type);	
echo "<br>";
echo $l->g(617)." ".$_SESSION['OCS']['TAG_LBL']['TAG'].": ".$select_choise;
echo "<input type='submit' name='ADD_TAG' value='" . $l->g(13) . "'><br>";
//echo show_modif(array($l->g(358)),'use_generic',5,$form_name);
echo $l->g(358);
echo close_form();

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
}
?>

