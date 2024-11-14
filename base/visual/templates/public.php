<!DOCTYPE html>
<html lang="<?php echo Language::active();?>">
<head>

    <meta charset="utf-8">
    <meta name="description" content="<?php echo $metaDescription;?>"/>
    <meta name="keywords" content="<?php echo $metaKeywords;?>"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

    <meta property="fb:app_id" content="168728593755836" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@plasticwebs" />
    <meta name="twitter:creator" content="@plasticwebs" />
    <meta property="og:title" content="<?php echo $title;?>" />
    <meta property="og:description" content="<?php echo $metaDescription;?>" />
    <meta property="og:url" content="<?php echo $metaUrl;?>" />
    <meta property="og:type" content="article" />
    <?php echo $metaImage;?>

    <link rel="shortcut icon" href="<?php echo ASTERION_BASE_URL;?>visual/img/favicon.ico"/>
    <link rel="canonical" href="<?php echo $metaUrl;?>" />

    <title><?php echo $title;?></title>

    <style><?php echo str_replace('../fonts/', ASTERION_BASE_URL.'visual/css/fonts/', str_replace('../../img/', ASTERION_BASE_URL.'visual/img/', file_get_contents(ASTERION_BASE_FILE.'visual/css/stylesheets/public.css')));?></style>


    <?php echo Parameter::code('google_webmasters');?>
    <?php echo Parameter::code('google_analytics');?>

    <?php echo $head;?>


</head>
<body>
    <div id="body_content">
        <?php echo $content;?>
    </div>
    <script type="text/javascript">
        document.querySelector('.menu_trigger').addEventListener('click', function(evt) { document.querySelector('.menu_all').classList.toggle('menu_all_open'); });
        document.querySelector('.search_top_trigger').addEventListener('click', function(evt) {
            document.querySelector('.search_top').classList.toggle('search_top_open');
            document.querySelector('input[name="search"]').focus();
        });
    </script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7429223453905389"
     crossorigin="anonymous"></script>
</body>
</html>