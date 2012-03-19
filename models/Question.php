<?php
/**
 * Location
 * @package: Omeka
 */
class Question extends Omeka_Record
{
	public $item_id;
    public $focus_q_id;
    public $focus_q;
	public $qorder;
    
    protected function _validate()
    {
        if (empty($this->item_id)) {
            $this->addError('item_id', 'q requires an item id.');
        }
    }
}