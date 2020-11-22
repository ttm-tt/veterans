<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$paging = $this->Paginator->params();
	$start = $paging['start'];

	$limit = $paging['perPage'] ?: 20;
	$page = $paging['page'] ?: 1;
	$pageCount = $paging['pageCount'] ?: 1;

	unset($paging['page']);
	unset($paging['perPage']);

	$url = $this->Paginator->generateUrlParams();
	// We don't need page and limit, we use the current values from the web page
	unset($url['page']);
	unset($url['limit']);
	// debug($url);

	$commandLink = $this->Url->build($url, ['escape' => false]);

	// Make sure the URL contains a query part so it is easier to append
	if (strchr($commandLink, '?') === false)
			$commandLink .= '?_';

	// To be used as a onchange script
	$commandLink .= "&page=' + $('#currentpage').val() + '&limit=' + $('#numrecords').val()";
	// debug($commandLink);

	$records = array(
		'5' => '5',
		'10' => '10',
		'20' => '20',
		'50' => '50',
		'100' => '100'
	);

	$numRecords = $this->Form->select('numrecords', $records, array(
		'empty' => false,
		'value' => $limit,
		'id' => 'numrecords',
		'onchange' => "window.location = '" . $commandLink
	));

	// At least one page
	$pages = range(1, $pageCount);

	$currentPage = $this->Form->select(
		'currentpage', array_combine($pages, $pages), array(
			'empty' => false, 
			'value' => $page,
			'id' => 'currentpage',
			'onchange' => "window.location = '" . $commandLink 
	));

	$format = __('Show %current% records out of %count% total, starting on record %start%');
	$formatKeys = array(
		'%current%' => $numRecords,
		'%count%' => $paging['count'],
		'%start%' => $start
	);

	$options = [];

	$templates = [
		'nextActive' => '<span class="next"><a rel="next" href="{{url}}">{{text}}</a></span>',
		'nextDisabled' => '<span class="next disabled">{{text}}</span>',
		'prevActive' => '<span class="prev"><a rel="prev" href="{{url}}">{{text}}</a></span>',
		'prevDisabled' => '<span class="prev disabled">{{text}}</span>',
		'first' => '<span class="first"><a href="{{url}}">{{text}}</a></span>',
		'last' => '<span class="last"><a href="{{url}}">{{text}}</a></span>',
	];

?>

<?php 
	$this->Paginator->templater()->push();
	$this->Paginator->templater()->add($templates);
?>

<div class="paging">	
	<span class="page">
	<?php echo $this->Paginator->first('<<', $options);?>
	<?php echo $this->Paginator->prev('<', $options);?>
	<?php echo '<span>' . $currentPage . '</span>'; ?>
	<?php echo $this->Paginator->next('>', $options);?>
	<?php echo $this->Paginator->last('>>', $options);?>
	</span>
	<span class="limit">
	<?php echo str_replace(array_keys($formatKeys), array_values($formatKeys), $format); ?>
	</span>
	<?php
		$this->Paginator->templater()->pop();
	?>
</div>
