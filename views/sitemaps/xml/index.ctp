<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
         xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
         xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> 
      <url> 
        <loc><?php echo Router::url('/', true); ?></loc> 
        <lastmod><?php echo trim($this->Time->toAtom(time())); ?></lastmod> 
        <changefreq>weekly</changefreq> 
        <priority>1.0</priority> 
    </url> 
<?php 
if( isset($statics) && !empty($statics) ): 
    foreach ($statics as $static):?> 
    <url>  
        <loc><?php echo Router::url($static['url'], true); ?></loc>  
        <lastmod><?php echo trim($this->Time->toAtom(time())); ?></lastmod> 
        <priority><?php echo $static['options']['pr'] ?></priority> 
        <changefreq><?php echo $static['options']['changefreq'] ?></changefreq> 
    </url> 
<?php 
    endforeach; 
endif; 

if( isset($dynamics) && !empty($dynamics) ): 
  foreach ($dynamics as $dynamic):
    if (!array_search($dynamic['options']['controllertitle'], $systems)) :
	  $systems[] = $dynamic['options']['controllertitle']; 
	  foreach ($dynamic['data'] as $section):?>  
    <url>  
        <loc><?php $url = !empty($section['Alias']['name']) ? '/'.$section['Alias']['name'] : array('plugin' => $dynamic['options']['url']['plugin'], 'controller' => $dynamic['options']['url']['controller'], 'action' => $dynamic['options']['url']['action'], $section[$dynamic['model']][$dynamic['options']['fields']['id']]); echo Router::url($url, true); ?></loc>  
        <lastmod><?php echo trim($this->Time->toAtom($section[$dynamic['model']][$dynamic['options']['fields']['date']]))?></lastmod>  
        <priority><?php echo $dynamic['options']['pr'] ?></priority>  
        <changefreq><?php echo $dynamic['options']['changefreq'] ?></changefreq> 
    </url>  
    <?php endforeach; 
	   endif;
    endforeach; 
endif; ?>  
</urlset>