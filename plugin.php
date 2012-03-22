<?php

add_plugin_hook('install', 'zoominstall');
add_plugin_hook('uninstall', 'zoomuninstall');
//add_filter('admin_navigation_main', 'FocusAddToNav');
add_plugin_hook('config_form', 'zoomify_config_form');
add_plugin_hook('config', 'zoomify_config');
add_filter('admin_items_form_tabs', 'zoomify_item_form_tabs');
add_plugin_hook('after_save_form_item', 'zoomify_save');

function zoomify_save($item)
{
	$db = get_db();
    $post = $_POST;    
    // If there are questions, then submit to the db
    $zoomP = $post['zoomify'];
	
    if (!empty($zoomP))
	{
	//echo $right;
	// Verified: does this array already exist in the DB?
	$verifier	= currentZoomifyName($item->id);
				
			if(!$zoomify)
			 	{
            	$zoomify = new Zoomify;
            	$zoomify->item_id = $item->id;
				$zoomify->zoomify_name = $zoomP;
   			 	}
					
				if($verifier)
					{
					if($zoomP == "LinkNothing")
						{
						$mysql = 'DELETE FROM '. $db->prefix .'zoomifies
							WHERE 
								item_id = "'. $item->id .'"';
						$db->query($mysql);	
						
						}
					else
						{
						$mysql = 'UPDATE '. $db->prefix .'zoomifies SET
						zoomify_name = "'. $zoomify->zoomify_name .'"
							WHERE 
								item_id = "'. $item->id .'"';
						$db->query($mysql);	
							
						}
					}
				else 
					{
					//insert the quetion relationships
					$data = array(
					'item_id'	=> $zoomify->item_id,
					'zoomify_name' => $zoomify->zoomify_name
					);
					$db->insert('zoomify', $data);
					}

				//$db->insert('questions', $data);
    } 
    else 
    {
        if ($question) 
        { $question->delete(); }
    }
}

function zoomify_form($item, $post = null) { 	
	    $ht = '';
	    if ($post === null) { $post = $_POST;}
	 	ob_start();

		
		?>
		<p>Choose from the dropdown list which Zoomify file
			you would like to link to this image. Ideally,
			the image in the zoomify should match the
			image which you have uploaded on the Files Tab.</p>
			<p>Items with a (*) next to the name have
				already been linked to an item.</p>

		<div id="zoomifyform">
		<?php
	zoomify_directory_array_prepare($item->id);
		?>
		</div>
		<?php
		    $ht .= ob_get_contents();
		    ob_end_clean();
		    return $ht;
}

function zoomify_directory_array_prepare($itemid)
	{
		// open this directory 
		$myDirectory = opendir("../zoomedimages");
		
		// get each entry
		while($entryName = readdir($myDirectory)) 
			{
			//if the string contains HTM(L) we want
			//it to be available for selecting. BUT
			//NOT ANY OTHER FILE. 
			if(strstr($entryName, "htm"))
				{
				$dirArray[] = $entryName;	
				}
			}
		
		// close directory
		closedir($myDirectory);
		//	count elements in array
		$indexCount	= count($dirArray);
		
		
		// sort 'em
		sort($dirArray);
		$currentSelected = currentZoomifyName($itemid);
		$comparativeArray= isThisZoomifyTaken(); //what zoomifys are already paired up?
		echo $comparativeArray[0];
		//echo "$currentSelected $itemid is gdfjkhgjkhg";//debug
 		?>
 		<select name="zoomify" id="zoomify">
 			<optgroup label="Select">
 				<option value='LinkNothing'>Do not link any Zoomify</option>
 		<?php
 		//okay, purely functional at this point.
 		// not really proud of this to little display
 		// function. Needs to be cleaned up later. 
		for($index=0; $index < $indexCount; $index++)
		{
    		echo "<option ";
    		if($currentSelected == $dirArray[$index])
				{
					//highlight the current selection
					echo "SELECTED ";		
				}
    		echo "value='$dirArray[$index]'";
    		echo ">";
    		if(in_array($dirArray[$index], $comparativeArray))
				{
					echo "** ";
				}
    		echo "$dirArray[$index]";
    		if(in_array($dirArray[$index], $comparativeArray))
				{
					echo "** ";
				}
    		echo "</option>";
		}	
		?>
			</optgroup>
		</select>
		<?php
	}


function currentZoomifyName($itemid)
{
		$db = get_db();
		//insert the quetion relationships
		$mysql = 'SELECT zoomify_name FROM '. $db->prefix .'zoomifies WHERE item_id = "'. $itemid .'"';
		$findrow	= $db->fetchRow($mysql);
		//echo $findrow[focus_q];//debug
		//echo "$itemid is the item id and $questionum is the num";//debug
		//echo "$mysql";//debug
		return $findrow[zoomify_name];
}

function isThisZoomifyTaken()
{
	//instantiate the array we will return of named used zoomifies
	$GirlsWithaDancingPartners	=	array();
	
	$db = get_db();
	$mysql = 'SELECT * FROM '. $db->prefix .'zoomifies';
	$findrow	= $db->fetchAll($mysql);
	$echome	=	count($findrow);
	//echo "$echome is the echo,e";
	for($x=0;$x<$echome;$x++)
		{
			$GirlsWithaDancingPartners[$x] = $findrow[$x][zoomify_name]; 
			//echo $GirlsWithaDancingPartners[$x];
		}
		
	return $GirlsWithaDancingPartners;	
}

function zoomify_item_form_tabs($tabs)
{
    $item = get_current_item();
    $ttabs = array();
    foreach($tabs as $key => $html) {
        if ($key == 'Miscellaneous') {
           $ht = '';
           $ht .= zoomify_form($item);
           $ttabs['Link a Zoomify'] = $ht;
        }
    $ttabs[$key] = $html;
    }
    $tabs = $ttabs;
    return $tabs;
}

    function zoominstall()
    {
        $db = get_db();
        $sql = "
        CREATE TABLE `{$db->prefix}zoomifies` (
            `zoom_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
     		`zoomify_name` text collate utf8_unicode_ci,
        	PRIMARY KEY (`zoom_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$db->query($sql);

		set_option('zoomify_storage_directory', 'zoomedimages');
		
		//okay, this step can go wrong. If enabled, we'd like to make
		//the directory at the root level of this site's installation
		// for the zoomify images. But we are aware that this step will
		// not likely work on all operating systems. 
		
		//$path = ''. APPLICATION_PATH . '/zoomedimages';
		//echo $path;
		//exit();
		//if(mkdir($path))
		//	{
		//	flashSuccess('Definition Was Successfully Added to DB');
		//	}
    }
    
    function zoomuninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}zoomify`;";
		$db->query($sql);

		
		delete_option('zoomify_storage_directory');
		
		// NOTE. because of the conventions of application
		// design, we DO NOT automatically delete the directory
		// but we do pop up a message indicating that we
		// did not. I think this is the presumed expectation
		// but I shall not make assumptions. 
    }
	
	//function FocusAddToNav($nav)
   // {
       //$nav['Questions'] = uri('focus-q');
    //   return $nav;
   // }
    

    
    function zoomify_config_form()
	{
	include 'config_form.php';
	}

	function zoomify_config()
	{   
    // Use the form to set a bunch of default options in the db
	}
    
?>