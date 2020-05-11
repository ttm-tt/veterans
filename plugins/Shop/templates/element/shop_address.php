<?php 
	if ($address === null) 
		return;
	
	// Some fields are needed because they are concatenated and must exist
	$tmp  = (is_object($address) ? $address->toArray() : $address) + [
		'first_name' => '',
		'last_name' => '&nbsp;',
		'zip_code' => '',
		'city' => '&nbsp;',
		'country_id' => null
	];
?>	
<dl>
	<dt><?php echo __d('user', 'Title');?></dt>
	<dd><?php echo $tmp['title'] ?? '&nbsp;';?></dd>
	<dt><?php echo __d('user', 'Name');?></dt>
	<dd><?php echo $tmp['first_name'] . ' ' . $tmp['last_name'];?></dd>
	<dt><?php echo __d('user', 'Street');?></dt>
	<dd><?php echo $tmp['street'] ?? '&nbsp;';?></dd>
	<dt><?php echo __d('user', 'City');?></dt>
	<dd><?php echo $tmp['zip_code'] . ' ' . $tmp['city'];?></dd>
	<dt><?php echo __d('user', 'Country');?></dt>
	<dd><?php echo $countries[$tmp['country_id']] ?? '&nbsp;';?></dd>
	<dt><?php echo __d('user', 'Email');?></dt>
	<dd><?php echo $tmp['email'] ?? '&nbsp;';?></dd>
	<dt><?php echo __d('user', 'Phone');?></dt>
	<dd><?php echo $tmp['phone'] ?? '&nbsp;';?></dd>
</dl>
