<h1><?=$v->getTitle() ?></h1>

<div class="body">
	<?=$v->getBody() ?>
</div>

<? if ( $tags = $v->getTags() ): ?>
	<ul>
		<? while ( list($key, $tag) = $tags->each() ): ?>
			<li><a href="<?=$tag->getUrl() ?>"><?=$tag->getName() ?></a></li>
		<? endwhile ?>
	</ul>
<? endif ?>

<?=$v->getImage()->getImgTag('article_main_image') ?>

<? if ( $photos = $v->getPhotos() ): ?>
	<ul>
		<? while ( list($key, $photo) = $photos->each() ): ?>
			<li><?=$photo->getImgTag('article_main_image') ?></li>
		<? endwhile ?>
	</ul>
<? endif ?>

<pre>
<? var_dump(array_keys((array)$node)) ?>

<? var_dump(array_keys(get_defined_vars())) ?>
</pre>