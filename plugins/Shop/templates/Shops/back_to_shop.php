<?php
  if (count($_GET) > 0)
    $params= $_GET;
  elseif (count($_POST) > 0)
    $params= $_POST;
  else
    $params= array();

  if (count($params) > 0)
    $status= $params['ret_status'];
  else
    $status= "success";
?>

<div class="back_to_shop">
	<h2>Result from ipayment system (silent mode): <?php echo $status;?></h2>
    <table>
		<tr>
			<th>Parameter</th>
			<th>Value</th>
		</tr>
	    <?php
    	  while (list ($key, $val) = each ($params)) {
			if (!is_array($val)) {
	        	$val= htmlentities($val);
		        echo '<tr><td>' . $key . '</td><td>' . $val . '</td></tr>';
			} else if (Hash::numeric(array_keys($val))) {
				echo '<tr><td>' . $key . '</td><td></td></tr>';

				foreach ($val as $k => $v) {
					echo '<tr><td></td><td>' . htmlentities($v) . '</td></tr>';
				}
			} else {
				foreach ($val as $k => $v) {
					echo '<tr><td>' . $key . '.' . $k . '</td><td>' . htmlentities($v) . '</td></tr>';
				}
			}
    	  }
	    ?>
    </table>
</div>
