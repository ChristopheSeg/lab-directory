<?php
/*
IMPORTANT DON'T USE OLD TRANSLATIONS when switching to splitted pot files !! 
	If your plugin was using a single pot file and you decided to split your single pot files, older translation must also be splitted...
	You MUST apply this procedure before switching to splitted pot files
	In order to split old translation contained in one single file per language (like plugin_name-fr_FR.pot) 
	copy the original plugin_name-xx_YY.pot file to plugin_name-admin-xx_YY.pot plugin_name-frontend-xx_YY.pot 
	for all standalone pot files, copy the original plugin_name-xx_YY.pot to plugin_name-slug_extension-xx_YY.pot
	repeat this for all languages
	Update each copied .po file, from .pot source and save
	Compile each new .po file  

IMPORTANT when switching back to one single pot file !!
	If your plugin was using splitted pot files and you decided switch back to a single pot file, older translation must be merged in one single file per language...
	You MUST apply the following procedure before switching back
	The proposed procedure has not been tested. Please make test before applying it!!! 
	for each language (xx_YY) concatenate all existing .po files to plugin_name-xx_YY.po using msgcat: 
	 	msgcat --use-first lab-directory.pot lab-directory-admin.pot lab-directory-frontend.pot  zzz -o plugin_name-xx_YY.po
		where zzz is the list of all standalone -xx_YY.po files
	Compile each new .po file 
	
Splitting pot files goals
	reduce pot file size at loading by splitting it for frontend, admin and common files
	keep the original single pot file functionnality 
	automatically fill common pot file with common phrases
	provide the hability to deal with special pot file loaded alone by the plugin
		
Proposed Splitted pot file
	common phrases: keep the original pot file name, contains all phrases found in common php files and all phrases duplicate in frontend and admi
	frontend phrases: plugin_name-frontend.pot contains frontend phrases not found in common pot file
	admin phrases: plugin_name-admin.pot contains admin phrase not found in common pot file
	others phrases: plugin_name-????.pot which is a standalone pot file that can be loaded alone. Note that using standalone pot file can result in phrase duplication
	
Splitting mechanism using makeplot.php
	add a new function called 'wp-plugin-split' in the original makepot.php for creating multiple pot files
	'wp-plugin-split' function is fully compatible with original makeplot usage for plugin 
	'wp-plugin-split' function read $makepot_args provided by the plugin to tell if pot file splitting is used and how to do it
	
Parameter $makepot_args array structure 
	if $makepot_args array does not exists, pot file splitting is not used
	$makepot_args is an array with one item per pot file
	items with key 'common' 'frontend' and 'admin' are reserved for common frontend and admin pot file description 
    	'common' item is optionnal, this key is not needed if your plugin frontend and admin do not share common php files
    items with others keys correspond to standalone pot files which can be conditionnally loaded by the plugin
        Because these pot files can be loaded alone without common pot file loading, the common language phrases they contain can be move to common pot file but they can't be remove from the standalone pot file)
        For this reason, it is not possible to suppress all phrases duplication when using standalone pot files.  
    
    Item of $makepot_args array can up to 4 parameters 
    'slug_extension' is used to name pot file like $slug-{$args->slug_extension}.pot as for example 'plugin_name-admin.pot'  ($slug is the plugin-name slug in makeplot.php)
    'excludes' and 'includes' describe those file/folder to exclude or include in the gettext search 
    
    'use_for_common' optionnal boolean true by default, only used for standalone pot files
    	'use_for_common'=true : this pot file phrases can be inserted in common pot file (
    		Disadvantage: may result in more phrases duplication 
    		Advantage, when common.pot is loaded, standalone pot file do not need to be loaded
    	'use_for_common'=false : this pot file phrases will not be inserted in common pot file (no duplicate) 
    		Advantage : common pot file can be smaller 
    		Disadvantage: depending on this pot file content you would probably load it at the same time the common pot file is loaded.
       
*/
$makepot_args = array(
		// These files are common to admin and frontend
		'common' => array(
                'slug_extension' =>'', 
                'excludes' => array(),
				'includes' => array('common/.*', 'lab-directory\.php'),
		),
		// Frontend part
		'frontend' => array(
                'slug_extension' =>'frontend', 
                'excludes' => array(),
				'includes' => array('public/.*', 'templates/.*'),
		),
		// Admin part 
		'admin' => array(
                'slug_extension' =>'admin', 
                'excludes' => array('admin/classes/lab-directory-admin-menus\.php'),
				'includes' => array('admin/.*'),
		),
		/* This correspond to a standalone pot file which can result on some phrases duplication
		 * Admin menus are loaded alone when lab-directory is not called in admin
		 * This results in some messages duplicated 
		 */
		'admin_menus' => array(
                'slug_extension' =>'admin_menus', 
                'excludes' => array(),
				'includes' => array('admin/classes/lab-directory-admin-menus\.php'),
				// Set 'use_for_common' to true so that these language phrases are also found in common language file
				'use_for_common' => true, 
			
		),
); 
