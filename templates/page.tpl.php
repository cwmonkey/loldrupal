<div id="skip-link" class="skip-link">
    <a href="#main"><?= t('Skip to main content') ?></a>
</div>

<?=$header ?>

<main role="main" id="main">
  <?php if (isset($primary_local_tasks)): ?><ul class='links clearfix'><?php print render($primary_local_tasks) ?></ul><?php endif; ?>
  <?php if (isset($secondary_local_tasks)): ?><ul class='links clearfix'><?php print render($secondary_local_tasks) ?></ul><?php endif; ?>
  <?php if ($action_links): ?><ul class='links clearfix'><?php print render($action_links); ?></ul><?php endif; ?>

  <? if ($tabs): ?>
    <div class="tabs-toolbar"><?=render($tabs) ?></div>
  <? endif ?>

  <?php if ($page['help'] || ($show_messages && $messages)): ?>
    <div id='console'><div class='limiter clearfix'>
    <?php print render($page['help']); ?>
    <?php if ($show_messages && $messages): print $messages; endif; ?>
    </div></div>
  <?php endif; ?> 

  <div id="site-top">
    <? foreach ( $page['content_top'] as $val ): ?>
      <? if ( ($block = _sblock($val)) ): ?>
        <? // TODO: get the section tag/classes onto blocks ?>
        <?=$block?>
      <? endif ?>
    <? endforeach ?>
  </div>

  <?=render($page['content']) ?>

  <aside id="site-secondary">
    <? foreach ( $page['sidebar_second'] as $val ): ?>
      <? if ( ($block = _sblock($val)) ): ?>
        <? // TODO: get the section tag/classes onto blocks ?>
        <?=$block?>
      <? endif ?>
    <? endforeach ?>
  </aside>
</main>

<?=$footer ?>

<? // TODO: Confirm this block can be here with the site JS below it ?>
<?=render($page['footer']) ?>
