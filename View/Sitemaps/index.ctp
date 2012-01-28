<?php 
$this->pageTitle = 'Sitemap'; 
?>

<h1> Sitemap </h1>
<table cellpadding="0" cellspacing="0">
  <?php 
if( isset($dynamics) && !empty($dynamics) ): 

    foreach ($dynamics as $dynamic):  
		if (!array_search($dynamic['options']['controllertitle'], $systems)) :
	?>
  <tr>
    <th> <?php 
		$systems[] = $dynamic['options']['controllertitle']; 
		echo $dynamic['options']['controllertitle']; /*$this->Html->link( 
                               $dynamic['options']['controllertitle'], 
                               array( 
									 	  'plugin' => $dynamic['options']['url']['plugin'],
                                          'controller' => $dynamic['options']['url']['controller'],  
                                          'action' => $dynamic['options']['url']['index'] 
                                          ));*/ ?>
    </th>
  </tr>
  <?php foreach ($dynamic['data'] as $section):?>
  <tr>
    <td><?php 
		// replace with alias if it exists
		$url = !empty($section['Alias']['name']) ? '/'.$section['Alias']['name'] : array('plugin' => $dynamic['options']['url']['plugin'], 'controller' => $dynamic['options']['url']['controller'], 'action' => $dynamic['options']['url']['action'], $section[$dynamic['model']][$dynamic['options']['fields']['id']]); 
		
		echo $this->Html->link($section[$dynamic['model']][$dynamic['options']['fields']['title']], $url); ?></td>
  </tr>
  <?php endforeach;?>
  <tr>
    <td class="clear">&nbsp;</td>
  </tr>
  <?php 
		endif; // end dedupe if statement (ie. array_search)
    endforeach; 
endif; 

if(isset($statics) && !empty($statics) ):?>
  <tr>
    <td class="title"> Misc </td>
  </tr>
  <?php foreach ($statics as $static): ?>
  <tr>
    <td><?php echo $this->Html->link( 
                               $static['title'], 
                               $static['url']); ?></td>
  </tr>
  <?php endforeach;?>
  <tr>
    <td class="clear">&nbsp;</td>
  </tr>
  <?php endif; ?>
</table>
<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Sitemaps',
		'items' => array(
			$this->Html->link(__('Send', true), array('plugin' => 'sitemaps', 'controller' => 'sitemaps', 'action' => 'send_sitemap')),
			)
		),
	))); ?>
