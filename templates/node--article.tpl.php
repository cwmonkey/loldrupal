<h1><?=$v->getTitle() ?></h1>

<div class="body">
	<?=$v->getBody() ?>
</div>

<? if ( $tags = $v->getTags() ): ?>
	<ul>
		<? while ( list($key, $tag) = $tags->each() ): ?>
			<a href="<?=$tag->getUrl() ?>"><?=$tag->getName() ?></a>
		<? endwhile ?>
	</ul>
<? endif ?>