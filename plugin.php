<?php

add_plugin_hook('install', 'install');
add_plugin_hook('uninstall', 'uninstall');
//add_filter('admin_navigation_main', 'FocusAddToNav');
add_plugin_hook('config_form', 'focusq_config_form');
add_plugin_hook('config', 'focusq_config');
add_filter('admin_items_form_tabs', 'focusq_item_form_tabs');
add_plugin_hook('after_save_form_item', 'focusq_save');

function focusq_save($item)
{
	$db = get_db();
    $post = $_POST;    
    // If there are questions, then submit to the db
    $qPost = $post['question'];
	
	//$numberofqs	=	get_option('focusq_per_page');//max number of db order id's
	$x = 1;
    if (!empty($qPost))
	{
		foreach($qPost as $right)
			{
				//echo $right;
				$verifier	= find_item_and_q_combo($item->id, $x);
				
				if(!$question)
				 	{
	            	$question = new Question;
	            	$question->item_id = $item->id;
					$question->focus_q = $right;
       			 	}
					
				if($verifier)
					{
					if($question->focus_q != "")
						{
						$mysql = 'UPDATE '. $db->prefix .'questions SET
						focus_q = "'. $question->focus_q .'"
							WHERE 
								item_id = "'. $item->id .'" 
								AND qorder_id = "'. $x .'"';
						$db->query($mysql);	
						}
					else
						{
						$mysql = 'DELETE FROM '. $db->prefix .'questions
							WHERE 
								item_id = "'. $item->id .'" 
								AND qorder_id = "'. $x .'"';
						$db->query($mysql);	
							
						}
					}
				else 
					{
					//insert the quetion relationships
					$data = array(
					'item_id'	=> $question->item_id,
					'focus_q' => $question->focus_q,
					'qorder_id' => $x
					);
					$db->insert('questions', $data);
					}

				//$db->insert('questions', $data);
				unset($question);
				$x++;
				}
    } 
    else 
    {
        if ($question) 
        { $question->delete(); }
    }
}

function focusq_form($item, $post = null) { 	
	    $ht = '';
	    if ($post === null) { $post = $_POST;}
	 	ob_start();
		
		$numberofqs	=	get_option('focusq_per_page');
	
		
		?>
		<p>Enter one Question in each box. You can change the 
			number of boxes which appear here on
			the Questions' configuration page. </p>
			<p>To delete a question, delete the text from the box and 
				click save.</p>
		<div id="questionform">
		<?php
		for($x=1; $x<=$numberofqs; $x++)
			{
			$row	=	find_item_and_q_combo($item->id, $x);
				?>
			 <textarea rows="3" cols="75" id="question[<?php echo $x; ?>]"  name="question[<?php echo $x; ?>]" value="<?php echo $row[focus_q]; ?>" />
			 </textarea>
			<?php	
			}
		?>
		</div>
		<?php
		    $ht .= ob_get_contents();
		    ob_end_clean();
		    return $ht;
}
function find_item_and_q_combo($itemid, $questionum)
{
		$db = get_db();
		//insert the quetion relationships
		$mysql = 'SELECT focus_q FROM '. $db->prefix .'questions WHERE item_id = "'. $itemid .'" AND qorder_id = "'. $questionum .'"';
		$findrow	= $db->fetchRow($mysql);	
		//echo $findrow[focus_q];//debug
		//echo "$itemid is the item id and $questionum is the num";//debug
		//echo "$mysql";//debug
		return $findrow;
}

function focusq_item_form_tabs($tabs)
{
    // insert the map tab before the Miscellaneous tab
    $item = get_current_item();
    $ttabs = array();
    foreach($tabs as $key => $html) {
        if ($key == 'Miscellaneous') {
            $ht = '';
           $ht .= focusq_form($item);
            $ttabs['Questions'] = $ht;
        }
        $ttabs[$key] = $html;
    }
    $tabs = $ttabs;
    return $tabs;
}

    function install()
    {
        $db = get_db();
        $sql = "
        CREATE TABLE `{$db->prefix}questions` (
            `item_id` int(10) unsigned NOT NULL,
        	`focus_q_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        	`qorder_id` int(10) unsigned NOT NULL,
     		`focus_q` text collate utf8_unicode_ci,
        	PRIMARY KEY (`focus_q_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$db->query($sql);
	 	//$sql = "
       // CREATE TABLE `{$db->prefix}questions_items` (
       // 	`associative_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
       // 	`focus_q_id` int(10) unsigned NOT NULL,
       // 	`item_id` int(10) unsigned NOT NULL,
       // 	PRIMARY KEY (`associative_id`)
       // ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
       // $db->query($sql);
		
		 set_option('focusq_per_page', '5');
    }
    
    function uninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}questions`;";
		$db->query($sql);
	    //$sql = "DROP TABLE IF EXISTS `{$db->prefix}questions_items`;";
        //$db->query($sql);
		
		delete_option('focusq_per_page');
    }
	
	function FocusAddToNav($nav)
    {
       //$nav['Questions'] = uri('focus-q');
       return $nav;
    }
    

    
    function focusq_config_form()
	{

	include 'config_form.php';
	}

	function focusq_config()
	{   
    // Use the form to set a bunch of default options in the db
    $perPage = (int)$_POST['per_page'];
    set_option('focusq_per_page', $perPage);
	}
    
?>