<?php
include(dirname(__FILE__).'/config/config.inc.php');

// nom du module à charger
$moduleName = 'hipay_tpp';
// si module déjà installé
if(!Module::isInstalled($moduleName)){
	// chargement de l'objet module
	$module = Module::getInstanceByName($moduleName);

	// installation du module
	if($module->install()){
		echo "Module installé";
	}else{
		echo "Module pas installé";
	}
}
