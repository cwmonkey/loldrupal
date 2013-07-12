<h1><? $v->printTitle() ?></h1>

<div class="body">
	<? $v->printBody() ?>
</div>

<? if ( $v->existsTags() ): ?>
	<ul>
		<? while ( list($key, $tag) = $v->eachTags() ): ?>
			<a href="<? $tag->printUrl() ?>"><? $tag->printName() ?></a>
		<? endwhile ?>
	</ul>
<? endif ?>

<? /*
<? var_dump($v->getBody()) ?>
<? var_dump($v->getNodeBody()) ?>

<? $v->printBody() ?>
<? $v->printNodeBody() ?>

<pre>
<? //var_dump(get_defined_vars()); ?>
<? //var_dump(array_keys(get_defined_vars())); ?>
</pre>

<? _l($node) ?>
*/ ?>