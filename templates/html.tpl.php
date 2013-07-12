<!doctype html>
<html lang="<?=$language->language ?>" dir="<?=$language->dir ?>">
<head profile="<?=$grddl_profile ?>">
  <title><?=$head_title ?></title>
  <?=$head ?>
  <meta name="viewport" content="width=device-width">
  <?=$styles ?>
  <!--[if lt IE 9]>
      <? // TODO: move this to the new theme ?>
      <script src="/sites/all/themes/takepart_core/js/html5shiv.js"></script>
  <![endif]-->
</head>
<body class="<?=$classes ?>" <?=$attributes ?>>

  <? if (isset($page_top)): ?>
      <?=$page_top ?>
  <? endif ?>

  <? if (isset($page)): ?>
      <?=$page ?>
  <? endif ?>      

  <?=$scripts ?>

  <? if (isset($custom)): ?>
      <?=$custom ?>
  <? endif ?>        


  <? if (isset($page_bottom)): ?>
      <?=$page_bottom ?>
  <? endif ?>

  <? if (isset($tp_sysinfo_comment_tags)): ?>
      <?=$tp_sysinfo_comment_tags ?>
  <? endif ?>

  <? // Twitter widget ?>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</body>
</html>